<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nhanvien')) {
            Schema::create('nhanvien', function (Blueprint $table) {
                $table->string('maNV', 20)->primary();
                $table->string('tenNV', 100)->nullable();
                $table->date('ngaySinh')->nullable();
                $table->string('diaChi', 255)->nullable();
                $table->string('gioiTinh', 10)->nullable();
                $table->string('soDT', 12)->nullable();
                $table->string('email', 100)->nullable();
                $table->enum('vaitro', ['Quản lý thư viện', 'Thủ thư']);
                $table->enum('trangThaiTK', ['Đang hoạt động', 'Bị khóa', 'Chờ kích hoạt'])->default('Đang hoạt động');
                $table->string('tenDangNhap', 50)->unique();
                $table->string('matKhau', 100)->nullable();
                $table->rememberToken();
            });
        }

        if (!Schema::hasTable('docgia')) {
            Schema::create('docgia', function (Blueprint $table) {
                $table->string('maDG', 20)->primary();
                $table->string('tenDG', 100)->nullable();
                $table->date('ngaySinh')->nullable();
                $table->string('gioiTinh', 10)->nullable();
                $table->string('diaChi', 255)->nullable();
                $table->string('soDT', 12)->nullable();
                $table->string('email', 100)->nullable();
            });
        }

        if (!Schema::hasTable('tacgia')) {
            Schema::create('tacgia', function (Blueprint $table) {
                $table->string('maTG', 20)->primary();
                $table->string('tenTG', 100)->nullable();
                $table->date('namSinh')->nullable();
                $table->string('gioiTinh', 10)->nullable();
                $table->string('quocTich', 50)->nullable();
                $table->string('moTa', 255)->nullable();
            });
        }

        if (!Schema::hasTable('theloai')) {
            Schema::create('theloai', function (Blueprint $table) {
                $table->string('maTL', 20)->primary();
                $table->string('tenTL', 100)->nullable();
            });
        }

        if (!Schema::hasTable('nhaxuatban')) {
            Schema::create('nhaxuatban', function (Blueprint $table) {
                $table->string('maNXB', 20)->primary();
                $table->string('tenNXB', 100)->nullable();
                $table->string('diaChi', 255)->nullable();
                $table->string('soDT', 15)->nullable();
                $table->string('email', 100)->nullable();
            });
        }

        if (!Schema::hasTable('ngonngu')) {
            Schema::create('ngonngu', function (Blueprint $table) {
                $table->string('maNN', 20)->primary();
                $table->string('tenNN', 50)->nullable();
            });
        }

        if (!Schema::hasTable('kesach')) {
            Schema::create('kesach', function (Blueprint $table) {
                $table->string('maKS', 20)->primary();
                $table->string('tenKS', 50)->nullable();
            });
        }

        if (!Schema::hasTable('sach')) {
            Schema::create('sach', function (Blueprint $table) {
                $table->string('maSach', 20)->primary();
                $table->string('maNXB', 20)->nullable();
                $table->string('maTL', 20)->nullable();
                $table->string('maNN', 20)->nullable();
                $table->string('maTG', 20)->nullable();
                $table->string('maKS', 20)->nullable();
                $table->string('tenSach', 200)->unique();
                $table->integer('namXB')->nullable();
                $table->integer('soLuong')->nullable();
                $table->string('moTa', 255)->nullable();
                $table->string('trangThai', 50)->nullable();

                $table->foreign('maNXB')->references('maNXB')->on('nhaxuatban');
                $table->foreign('maTL')->references('maTL')->on('theloai');
                $table->foreign('maNN')->references('maNN')->on('ngonngu');
                $table->foreign('maTG')->references('maTG')->on('tacgia');
                $table->foreign('maKS')->references('maKS')->on('kesach');
            });
        }

        if (!Schema::hasTable('thethuvien')) {
            Schema::create('thethuvien', function (Blueprint $table) {
                $table->string('maTTV', 20)->primary();
                $table->string('maDG', 20)->nullable()->unique();
                $table->date('ngayCap')->nullable();
                $table->date('ngayHetHan')->nullable();
                $table->string('trangThai', 50)->nullable();

                $table->foreign('maDG')->references('maDG')->on('docgia');
            });
        }

        if (!Schema::hasTable('muontra')) {
            Schema::create('muontra', function (Blueprint $table) {
                $table->string('maMT', 20)->primary();
                $table->string('maDG', 20)->nullable();
                $table->string('maNV', 20)->nullable();
                $table->date('ngayMuon')->nullable();
                $table->date('hanTra')->nullable();
                $table->date('ngayTra')->nullable();
                $table->string('trangThai', 50)->nullable();

                $table->foreign('maDG')->references('maDG')->on('docgia');
                $table->foreign('maNV')->references('maNV')->on('nhanvien');
            });
        }

        if (!Schema::hasTable('chitietmuontra')) {
            Schema::create('chitietmuontra', function (Blueprint $table) {
                $table->string('maMT', 20);
                $table->string('maSach', 20);
                $table->integer('soLuong')->nullable();
                $table->string('ghiChu', 255)->nullable();

                $table->primary(['maMT', 'maSach']);
                $table->foreign('maMT')->references('maMT')->on('muontra');
                $table->foreign('maSach')->references('maSach')->on('sach');
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('chitietmuontra');
        Schema::dropIfExists('muontra');
        Schema::dropIfExists('thethuvien');
        Schema::dropIfExists('sach');
        Schema::dropIfExists('kesach');
        Schema::dropIfExists('ngonngu');
        Schema::dropIfExists('nhaxuatban');
        Schema::dropIfExists('theloai');
        Schema::dropIfExists('tacgia');
        Schema::dropIfExists('docgia');
        Schema::dropIfExists('nhanvien');

        Schema::enableForeignKeyConstraints();
    }
};
