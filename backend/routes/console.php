<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:make-manager {email}', function (string $email): int {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('لم يتم العثور على مستخدم بهذا البريد الإلكتروني.');

        return self::FAILURE;
    }

    $user->forceFill(['role' => User::ROLE_MANAGER])->save();
    $this->info("تم منح {$user->email} صلاحية مدير.");

    return self::SUCCESS;
})->purpose('ترقية حساب موجود إلى مدير عند تهيئة النظام');
