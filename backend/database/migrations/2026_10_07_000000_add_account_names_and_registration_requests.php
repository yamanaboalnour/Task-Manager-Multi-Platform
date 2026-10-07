<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    $parts = preg_split('/\s+/u', trim((string) $user->name), 2) ?: [];

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'first_name' => $parts[0] ?? '',
                            'last_name' => $parts[1] ?? '',
                        ]);
                }
            });

        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('password_hash')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('user_id')->nullable()->unique()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        if (in_array(DB::connection()->getDriverName(), ['sqlite', 'sqlsrv'], true)) {
            DB::statement(
                'CREATE UNIQUE INDEX registration_requests_pending_email_unique '.
                "ON registration_requests (email) WHERE status = 'pending'"
            );
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['sqlite', 'sqlsrv'], true)) {
            if (DB::connection()->getDriverName() === 'sqlsrv') {
                DB::statement('DROP INDEX registration_requests_pending_email_unique ON registration_requests');
            } else {
                DB::statement('DROP INDEX registration_requests_pending_email_unique');
            }
        }

        Schema::dropIfExists('registration_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
