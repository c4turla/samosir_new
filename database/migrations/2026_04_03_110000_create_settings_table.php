<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index();        // Grouping: app, port, contact, document, etc.
            $table->string('key', 100)->unique();         // Unique setting key
            $table->text('value')->nullable();             // Setting value
            $table->string('type', 20)->default('text');   // Type hint: text, textarea, number, boolean, email, select
            $table->string('label', 255);                  // Human-readable label
            $table->text('description')->nullable();       // Help text
            $table->text('options')->nullable();            // JSON options for select type
            $table->integer('sort_order')->default(0);     // Sort within group
            $table->timestamps();
        });

        // Seed default settings
        $now = now();
        DB::table('settings')->insert([
            // ===== Informasi Aplikasi =====
            [
                'group' => 'app',
                'key' => 'app_name',
                'value' => 'SAMOSIR',
                'type' => 'text',
                'label' => 'Nama Aplikasi',
                'description' => 'Nama sistem yang ditampilkan di header dan halaman login',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'app',
                'key' => 'app_full_name',
                'value' => 'Sistem Administrasi dan Monitoring Operasional Sumber Daya Ikan Regional',
                'type' => 'text',
                'label' => 'Nama Lengkap Aplikasi',
                'description' => 'Kepanjangan dari nama aplikasi',
                'options' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'app',
                'key' => 'app_version',
                'value' => '3.0',
                'type' => 'text',
                'label' => 'Versi Aplikasi',
                'description' => 'Nomor versi aplikasi yang ditampilkan',
                'options' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'app',
                'key' => 'app_description',
                'value' => 'Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga',
                'type' => 'textarea',
                'label' => 'Deskripsi Aplikasi',
                'description' => 'Deskripsi singkat tentang aplikasi',
                'options' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===== Informasi Pelabuhan =====
            [
                'group' => 'port',
                'key' => 'port_name',
                'value' => 'Pelabuhan Perikanan Nusantara Sibolga',
                'type' => 'text',
                'label' => 'Nama Pelabuhan',
                'description' => 'Nama lengkap pelabuhan perikanan',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'port',
                'key' => 'port_code',
                'value' => 'PPN Sibolga',
                'type' => 'text',
                'label' => 'Kode Pelabuhan',
                'description' => 'Kode singkat pelabuhan',
                'options' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'port',
                'key' => 'port_address',
                'value' => 'Jl. Pelabuhan No. 1, Sibolga, Sumatera Utara',
                'type' => 'textarea',
                'label' => 'Alamat Pelabuhan',
                'description' => 'Alamat lengkap pelabuhan',
                'options' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===== Pejabat / Kepala Pelabuhan =====
            [
                'group' => 'officials',
                'key' => 'port_head_name',
                'value' => '',
                'type' => 'text',
                'label' => 'Nama Kepala Pelabuhan',
                'description' => 'Nama lengkap Kepala Pelabuhan Perikanan',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'officials',
                'key' => 'port_head_nip',
                'value' => '',
                'type' => 'text',
                'label' => 'NIP Kepala Pelabuhan',
                'description' => 'Nomor Induk Pegawai Kepala Pelabuhan',
                'options' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'officials',
                'key' => 'port_head_title',
                'value' => 'Kepala Pelabuhan Perikanan Nusantara Sibolga',
                'type' => 'text',
                'label' => 'Jabatan Kepala Pelabuhan',
                'description' => 'Jabatan resmi yang tertera di dokumen',
                'options' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'officials',
                'key' => 'syahbandar_name',
                'value' => '',
                'type' => 'text',
                'label' => 'Nama Syahbandar',
                'description' => 'Nama lengkap Syahbandar pelabuhan',
                'options' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'officials',
                'key' => 'syahbandar_nip',
                'value' => '',
                'type' => 'text',
                'label' => 'NIP Syahbandar',
                'description' => 'Nomor Induk Pegawai Syahbandar',
                'options' => null,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===== Kontak =====
            [
                'group' => 'contact',
                'key' => 'contact_email',
                'value' => '',
                'type' => 'email',
                'label' => 'Email Kontak',
                'description' => 'Alamat email resmi pelabuhan',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'text',
                'label' => 'No. Telepon',
                'description' => 'Nomor telepon kantor pelabuhan',
                'options' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_fax',
                'value' => '',
                'type' => 'text',
                'label' => 'No. Fax',
                'description' => 'Nomor fax kantor pelabuhan',
                'options' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'contact_website',
                'value' => '',
                'type' => 'text',
                'label' => 'Website',
                'description' => 'Alamat website resmi pelabuhan',
                'options' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===== Dokumen =====
            [
                'group' => 'document',
                'key' => 'document_header',
                'value' => 'KEMENTERIAN KELAUTAN DAN PERIKANAN',
                'type' => 'text',
                'label' => 'Header Dokumen',
                'description' => 'Kop surat baris pertama untuk dokumen resmi',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'document',
                'key' => 'document_subheader',
                'value' => 'DIREKTORAT JENDERAL PERIKANAN TANGKAP',
                'type' => 'text',
                'label' => 'Sub-Header Dokumen',
                'description' => 'Kop surat baris kedua untuk dokumen resmi',
                'options' => null,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'document',
                'key' => 'document_footer',
                'value' => 'Pelabuhan Perikanan Nusantara Sibolga',
                'type' => 'text',
                'label' => 'Footer Dokumen',
                'description' => 'Footer yang ditampilkan di dokumen resmi',
                'options' => null,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ===== Operasional =====
            [
                'group' => 'operational',
                'key' => 'operating_hours',
                'value' => '08:00 - 17:00 WIB',
                'type' => 'text',
                'label' => 'Jam Operasional',
                'description' => 'Jam operasional kantor pelabuhan',
                'options' => null,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'operational',
                'key' => 'timezone',
                'value' => 'Asia/Jakarta',
                'type' => 'select',
                'label' => 'Zona Waktu',
                'description' => 'Zona waktu yang digunakan oleh aplikasi',
                'options' => json_encode(['Asia/Jakarta' => 'WIB', 'Asia/Makassar' => 'WITA', 'Asia/Jayapura' => 'WIT']),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'operational',
                'key' => 'default_fish_quality',
                'value' => 'SEGAR',
                'type' => 'select',
                'label' => 'Kualitas Ikan Default',
                'description' => 'Kualitas ikan default saat input kedatangan',
                'options' => json_encode(['SEGAR' => 'Segar', 'BEKU' => 'Beku', 'OLAHAN' => 'Olahan']),
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'operational',
                'key' => 'auto_approve_arrival',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Auto-Approve Kedatangan',
                'description' => 'Otomatis menyetujui data kedatangan kapal tanpa perlu approval manual',
                'options' => null,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'operational',
                'key' => 'auto_approve_departure',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Auto-Approve Keberangkatan',
                'description' => 'Otomatis menyetujui data keberangkatan kapal tanpa perlu approval manual',
                'options' => null,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
