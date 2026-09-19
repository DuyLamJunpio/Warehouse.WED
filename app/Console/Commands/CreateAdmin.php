<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email? : Email đăng nhập} {--name= : Tên hiển thị}';

    protected $description = 'Tạo tài khoản quản trị đầu tiên bằng lời nhắc mật khẩu ẩn';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email đăng nhập'))));
        $name = trim((string) ($this->option('name') ?: $this->ask('Tên hiển thị', 'Quản trị RUNGU')));
        $password = (string) $this->secret('Mật khẩu (tối thiểu 8 ký tự)');
        $confirmation = (string) $this->secret('Nhập lại mật khẩu');

        $validator = Validator::make(compact('email', 'name', 'password', 'confirmation'), [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(8), 'same:confirmation'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 1,
            'status' => 1,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->info('Đã tạo tài khoản quản trị.');

        return self::SUCCESS;
    }
}
