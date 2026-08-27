<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Chuẩn hóa dữ liệu khách cũ và gộp các hồ sơ có cùng số điện thoại/email.
     * Hóa đơn được chuyển sang hồ sơ giữ lại trước khi hồ sơ trùng bị soft-delete.
     */
    public function up(): void
    {
        $normalizePhone = static function ($phone): ?string {
            $digits = preg_replace('/\D+/', '', trim((string) $phone)) ?? '';
            if ($digits === '') {
                return null;
            }

            if (str_starts_with($digits, '0084')) {
                $digits = substr($digits, 2);
            }

            if (str_starts_with($digits, '84')) {
                $local = substr($digits, 2);
                if (str_starts_with($local, '0')) {
                    $local = substr($local, 1);
                }
                if (strlen($local) === 9) {
                    $digits = '0' . $local;
                }
            }

            return $digits;
        };
        $normalizeEmail = static function ($email): ?string {
            $email = mb_strtolower(trim((string) $email));

            return $email === '' ? null : $email;
        };

        // Chỉ gộp hồ sơ đang hoạt động. Hồ sơ đã xóa trước đây vẫn giữ nguyên
        // để không làm thay đổi ý nghĩa của thao tác xóa trong quá khứ.
        $rows = DB::table('customers')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $parent = [];
        $phoneOwner = [];
        $emailOwner = [];

        $find = function (int $id) use (&$parent, &$find): int {
            if (($parent[$id] ?? $id) !== $id) {
                $parent[$id] = $find($parent[$id]);
            }

            return $parent[$id] ?? $id;
        };
        $union = function (int $left, int $right) use (&$parent, $find): void {
            $leftRoot = $find($left);
            $rightRoot = $find($right);
            if ($leftRoot !== $rightRoot) {
                $parent[$rightRoot] = $leftRoot;
            }
        };

        foreach ($rows as $row) {
            $id = (int) $row->id;
            $parent[$id] = $id;
            $phone = $normalizePhone($row->customer_phone);
            $email = $normalizeEmail($row->customer_email);

            if ($phone !== null && isset($phoneOwner[$phone])) {
                $union($id, $phoneOwner[$phone]);
            } elseif ($phone !== null) {
                $phoneOwner[$phone] = $id;
            }
            if ($email !== null && isset($emailOwner[$email])) {
                $union($id, $emailOwner[$email]);
            } elseif ($email !== null) {
                $emailOwner[$email] = $id;
            }
        }

        $groups = [];
        foreach ($rows as $row) {
            $groups[$find((int) $row->id)][] = $row;
        }

        foreach ($groups as $group) {
            usort($group, fn($a, $b) => $a->id <=> $b->id);
            $master = $group[0];
            $phone = $normalizePhone($master->customer_phone);
            $email = $normalizeEmail($master->customer_email);
            if ($phone === null) {
                foreach ($group as $row) {
                    $candidate = $normalizePhone($row->customer_phone);
                    if ($candidate !== null) {
                        $phone = $candidate;
                        break;
                    }
                }
            }
            if ($email === null) {
                foreach ($group as $row) {
                    $candidate = $normalizeEmail($row->customer_email);
                    if ($candidate !== null) {
                        $email = $candidate;
                        break;
                    }
                }
            }
            $updates = [
                'customer_phone' => $phone ?: $master->customer_phone,
                'customer_email' => $email,
            ];

            foreach (['customer_name', 'address', 'province', 'ward', 'note', 'avatar'] as $field) {
                if (blank($updates[$field] ?? null)) {
                    foreach ($group as $row) {
                        if (filled($row->{$field} ?? null)) {
                            $updates[$field] = $row->{$field};
                            break;
                        }
                    }
                }
            }

            DB::table('customers')->where('id', $master->id)->update($updates);

            foreach (array_slice($group, 1) as $duplicate) {
                DB::table('invoices')
                    ->where('customer_id', $duplicate->id)
                    ->update(['customer_id' => $master->id]);
                DB::table('customers')
                    ->where('id', $duplicate->id)
                    ->update(['deleted_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Không thể khôi phục chính xác các hồ sơ đã gộp và lịch sử đã chuyển.
    }
};
