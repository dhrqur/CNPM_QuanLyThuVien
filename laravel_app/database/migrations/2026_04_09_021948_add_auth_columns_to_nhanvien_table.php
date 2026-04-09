<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nhanvien')) {
            return;
        }

        Schema::table('nhanvien', function (Blueprint $table) {
            if (!Schema::hasColumn('nhanvien', 'trangThaiTK')) {
                $table->enum('trangThaiTK', ['Đang hoạt động', 'Bị khóa', 'Chờ kích hoạt'])
                    ->default('Đang hoạt động')
                    ->after('vaitro');
            }

            if (!Schema::hasColumn('nhanvien', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('matKhau');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('nhanvien')) {
            return;
        }

        Schema::table('nhanvien', function (Blueprint $table) {
            if (Schema::hasColumn('nhanvien', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('nhanvien', 'trangThaiTK')) {
                $table->dropColumn('trangThaiTK');
            }
        });
    }
};
