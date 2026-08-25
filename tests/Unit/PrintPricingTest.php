<?php

namespace Tests\Unit;

use App\Services\PrintPricing;
use PHPUnit\Framework\TestCase;

/**
 * Bộ ca kiểm thử chuẩn của bộ máy tính tiền in.
 *
 * Cố ý kế thừa PHPUnit\Framework\TestCase chứ không phải Tests\TestCase: quote()
 * nhận thẳng ảnh chụp bảng giá nên không đụng cơ sở dữ liệu, và .env của dự án
 * này trỏ vào Supabase thật — một bộ test lỡ chạm vào đó là hỏng dữ liệu bán hàng.
 *
 * Web bán hàng có một bản dịch lớp này bằng TypeScript để hiện giá xem trước.
 * MỌI ca ở đây phải được chép sang bên đó và ra cùng một con số đến từng đồng;
 * lệch một đồng là khách trả một số còn hoá đơn ghi số khác.
 */
class PrintPricingTest extends TestCase
{
    /** Kỹ thuật 1 = decal, 2 = in lụa. Bậc khổ 10 = A6, 11 = A5, 12 = A4. */
    private function pricing(): array
    {
        return [
            'techniques' => [
                ['id' => 1, 'name' => 'Decal', 'max_colors' => null, 'min_dpi' => 150, 'moq' => 1],
                ['id' => 2, 'name' => 'In lụa', 'max_colors' => 6, 'min_dpi' => 300, 'moq' => 20],
            ],
            'tiers' => [
                ['id' => 10, 'name' => 'A6', 'width_mm' => 105, 'height_mm' => 148],
                ['id' => 11, 'name' => 'A5', 'width_mm' => 148, 'height_mm' => 210],
                ['id' => 12, 'name' => 'A4', 'width_mm' => 210, 'height_mm' => 297],
            ],
            'cells' => [
                1 => [10 => 20000, 11 => 35000, 12 => 55000],
                2 => [10 => 35000, 11 => 50000],
            ],
            'rules' => [
                [
                    'id' => 'underbase', 'label' => 'Lót trắng áo tối', 'enabled' => true,
                    'when' => ['tone' => 'dark', 'technique_ids' => [1]],
                    'apply' => ['kind' => 'add', 'amount' => 15000, 'per' => 'zone'],
                ],
                [
                    'id' => 'back', 'label' => 'Mặt lưng khó căn', 'enabled' => true,
                    'when' => ['zone_keys' => ['back']],
                    'apply' => ['kind' => 'multiply', 'amount' => 1.2, 'per' => 'zone'],
                ],
                [
                    'id' => 'ink', 'label' => 'In lụa từ màu thứ 2', 'enabled' => true,
                    'when' => ['technique_ids' => [2], 'ink_colors_from' => 2],
                    'apply' => ['kind' => 'add', 'amount' => 20000, 'per' => 'inkColor'],
                ],
            ],
            'qty_tiers' => [['from' => 10, 'pct' => 10]],
            'rounding' => 1000,
            'min_charge' => 0,
        ];
    }

    /** @param array<int,array> $placements */
    private function design(array $placements, array $overrides = []): array
    {
        return array_merge([
            'blank' => ['id' => 1, 'name' => 'Áo thun', 'base_price' => 120000, 'moq' => 1, 'product_id' => null],
            'size' => 'L',
            'size_surcharge' => 0,
            'color_name' => 'Trắng',
            'tone' => 'light',
            'technique_id' => 1,
            'ink_colors' => 1,
            'qty' => 1,
            'zones' => [
                'front' => ['label' => 'Mặt trước', 'width_mm' => 280, 'height_mm' => 360],
                'back' => ['label' => 'Mặt lưng', 'width_mm' => 280, 'height_mm' => 400],
            ],
            'placements' => $placements,
        ], $overrides);
    }

    private function place(string $zone, float $x, float $y, float $w, float $h, float $rot = 0): array
    {
        return ['zone' => $zone, 'x_mm' => $x, 'y_mm' => $y, 'w_mm' => $w, 'h_mm' => $h, 'rotation' => $rot];
    }

    public function test_khong_co_hinh_thi_chi_tinh_tien_phoi(): void
    {
        $result = PrintPricing::quote($this->design([]), $this->pricing());

        $this->assertSame(120000, $result['unit_price']);
        $this->assertContains('Chưa có hình nào trên áo — mới tính tiền phôi.', $result['warnings']);
    }

    public function test_mot_hinh_a5_tren_ao_sang(): void
    {
        // 120.000 phôi + 35.000 in A5 = 155.000, đã là bội số 1.000
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)]),
            $this->pricing(),
        );

        $this->assertSame(155000, $result['unit_price']);
        $this->assertEmpty($result['errors']);
    }

    public function test_ao_toi_bi_cong_phi_lot_trang_theo_tung_vung(): void
    {
        // 120.000 + 35.000 + 15.000 lót = 170.000
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['tone' => 'dark']),
            $this->pricing(),
        );

        $this->assertSame(170000, $result['unit_price']);
    }

    /**
     * Ca quan trọng nhất của cả cách tính giá: ba sticker nhỏ nằm gọn trong một
     * khổ A5 phải tính tiền MỘT lần khổ A5, không phải ba lần tiền khổ A6.
     */
    public function test_nhieu_hinh_cung_vung_gop_thanh_mot_khung_bao(): void
    {
        $result = PrintPricing::quote($this->design([
            $this->place('front', 0, 0, 40, 40),
            $this->place('front', 50, 0, 40, 40),
            $this->place('front', 0, 60, 90, 60),
        ]), $this->pricing());

        // khung bao 90 x 120 mm -> vẫn lọt A6 (105x148): 120.000 + 20.000
        $this->assertSame(140000, $result['unit_price']);

        $printLine = collect($result['lines'])->firstWhere('label', 'Decal · Mặt trước · khổ A6');
        $this->assertNotNull($printLine, 'Bảng kê phải nói rõ đã tính theo khổ nào.');
        $this->assertStringContainsString('3 hình', $printLine['meta']);
    }

    public function test_hinh_xoay_lam_khung_bao_lon_hon_va_nhay_bac_kho(): void
    {
        // 100x100 để thẳng thì lọt A6; xoay 45° thành ~141x141 nên phải lên A5.
        $thang = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 100, 100)]),
            $this->pricing(),
        );
        $xoay = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 100, 100, 45)]),
            $this->pricing(),
        );

        $this->assertSame(140000, $thang['unit_price']);   // 120k + A6 20k
        $this->assertSame(155000, $xoay['unit_price']);    // 120k + A5 35k
    }

    public function test_khung_bao_nam_ngang_van_lot_bac_kho_dung_dung(): void
    {
        // 200 x 140: không lọt A5 dựng đứng (148x210) nhưng lọt khi quay ngang.
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 200, 140)]),
            $this->pricing(),
        );

        $this->assertSame(155000, $result['unit_price']);
        $this->assertEmpty($result['errors']);
    }

    public function test_vuot_bac_kho_lon_nhat_thi_bao_loi(): void
    {
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 270, 350)]),
            $this->pricing(),
        );

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('vượt bậc khổ lớn nhất', $result['errors'][0]);
    }

    public function test_ky_thuat_khong_nhan_kho_do_thi_bao_loi(): void
    {
        // In lụa (id 2) không có giá cho A4 trong ma trận.
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 200, 280)], ['technique_id' => 2, 'qty' => 20]),
            $this->pricing(),
        );

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('không nhận khổ A4', $result['errors'][0]);
    }

    public function test_he_so_nhan_chi_an_vao_gia_in_cua_vung_do(): void
    {
        // Mặt lưng x1.2 phải nhân trên 35.000 tiền in, KHÔNG nhân cả tiền phôi.
        $result = PrintPricing::quote(
            $this->design([$this->place('back', 0, 0, 140, 200)]),
            $this->pricing(),
        );

        // 120.000 + 35.000 + 7.000 = 162.000
        $this->assertSame(162000, $result['unit_price']);
    }

    public function test_in_lua_tinh_tien_tu_mau_muc_thu_hai(): void
    {
        // 4 màu, ngưỡng từ màu 2 -> tính phí 3 màu x 20.000
        $result = PrintPricing::quote(
            $this->design(
                [$this->place('front', 0, 0, 140, 200)],
                ['technique_id' => 2, 'ink_colors' => 4, 'qty' => 20],
            ),
            $this->pricing(),
        );

        // (120.000 + 50.000 + 60.000) = 230.000, giảm 10% còn 207.000
        $this->assertSame(207000, $result['unit_price']);
    }

    public function test_chiet_khau_so_luong_ap_bac_cao_nhat_dat_duoc(): void
    {
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['qty' => 30]),
            $this->pricing(),
        );

        // 155.000 giảm 10% = 139.500 -> làm tròn lên 140.000
        $this->assertSame(140000, $result['unit_price']);
        $this->assertSame(140000 * 30, $result['total']);
    }

    public function test_lam_tron_luon_lam_tron_len(): void
    {
        $pricing = $this->pricing();
        $pricing['rounding'] = 5000;

        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)]),
            $pricing,
        );

        // 155.000 vốn đã là bội số của 5.000
        $this->assertSame(155000, $result['unit_price']);

        $pricing['rounding'] = 50000;
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)]),
            $pricing,
        );
        $this->assertSame(200000, $result['unit_price']);
    }

    public function test_san_gia_nang_don_qua_re_len_muc_toi_thieu(): void
    {
        $pricing = $this->pricing();
        $pricing['min_charge'] = 200000;

        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)]),
            $pricing,
        );

        $this->assertSame(200000, $result['unit_price']);
    }

    public function test_vuot_so_mau_toi_da_cua_ky_thuat_thi_bao_loi(): void
    {
        $result = PrintPricing::quote(
            $this->design(
                [$this->place('front', 0, 0, 140, 200)],
                ['technique_id' => 2, 'ink_colors' => 9, 'qty' => 20],
            ),
            $this->pricing(),
        );

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('chỉ in tối đa 6 màu', $result['errors'][0]);
    }

    public function test_duoi_moq_cua_ky_thuat_thi_canh_bao_chu_khong_chan(): void
    {
        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['technique_id' => 2, 'qty' => 5]),
            $this->pricing(),
        );

        $this->assertEmpty($result['errors']);
        $this->assertNotEmpty($result['warnings']);
        $this->assertStringContainsString('tối thiểu 20 áo', $result['warnings'][0]);
    }

    public function test_quy_tac_tat_thi_khong_an_vao_gia(): void
    {
        $pricing = $this->pricing();
        $pricing['rules'][0]['enabled'] = false;

        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['tone' => 'dark']),
            $pricing,
        );

        $this->assertSame(155000, $result['unit_price']);
    }

    /**
     * Hồi quy: phụ phí `mỗi đơn` KHÔNG được nhân theo số áo.
     *
     * Bộ máy tính giá một áo và tổng là đơn giá nhân số lượng, nên một khoản
     * "mỗi đơn" cộng thẳng vào đơn giá sẽ bị thu gấp `qty` lần. Trước khi sửa,
     * đơn 4 áo với phụ phí 30.000/đơn bị tính 120.000.
     */
    public function test_phu_phi_moi_don_khong_bi_nhan_theo_so_luong(): void
    {
        $pricing = $this->pricing();
        $pricing['qty_tiers'] = [];
        $pricing['rounding'] = 0;
        $pricing['rules'] = [[
            'id' => 'small', 'label' => 'Phụ phí đơn lẻ', 'enabled' => true,
            'when' => ['qty_to' => 4],
            'apply' => ['kind' => 'add', 'amount' => 30000, 'per' => 'order'],
        ]];

        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['qty' => 4]),
            $pricing,
        );

        // 120.000 phôi + 35.000 in = 155.000 mỗi áo, cộng 30.000 CHO CẢ ĐƠN.
        $this->assertSame(155000 * 4 + 30000, $result['total']);
        $this->assertSame(155000 + 7500, $result['unit_price']);
    }

    /** Đơn một áo thì "mỗi đơn" và "mỗi áo" ra cùng một số. */
    public function test_phu_phi_moi_don_voi_don_mot_ao(): void
    {
        $pricing = $this->pricing();
        $pricing['qty_tiers'] = [];
        $pricing['rounding'] = 0;
        $pricing['rules'] = [[
            'id' => 'small', 'label' => 'Phụ phí đơn lẻ', 'enabled' => true,
            'when' => ['qty_to' => 4],
            'apply' => ['kind' => 'add', 'amount' => 30000, 'per' => 'order'],
        ]];

        $result = PrintPricing::quote(
            $this->design([$this->place('front', 0, 0, 140, 200)], ['qty' => 1]),
            $pricing,
        );

        $this->assertSame(185000, $result['unit_price']);
        $this->assertSame(185000, $result['total']);
    }
}
