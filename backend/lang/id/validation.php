<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Baris Bahasa untuk Validasi
|--------------------------------------------------------------------------
|
| APP_LOCALE sudah diset ke "id", sehingga berkas ini otomatis dipakai.
|
| "attributes" mengganti nama kolom mentah pada pesan galat: tanpa ini pesan
| akan berbunyi "company name wajib diisi." alih-alih "Nama Perusahaan wajib
| diisi.". Form Request masing-masing tetap menentukan kalimat pesannya.
|
*/

return [
    'attributes' => [
        // Akun
        'name' => 'Nama',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Konfirmasi Password',
        'phone' => 'Nomor Telepon',
        'company_name' => 'Nama Perusahaan',
        'company_address' => 'Alamat Perusahaan',

        // Permohonan
        'title' => 'Judul Permohonan',
        'document_category_id' => 'Kategori Dokumen',
        'description' => 'Deskripsi',
        'priority' => 'Prioritas',
        'notes' => 'Catatan',

        // Dokumen
        'document' => 'Dokumen',
    ],

    // Cadangan bila sebuah aturan tidak punya pesan khusus di Form Request.
    'required' => ':attribute wajib diisi.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'unique' => ':attribute sudah terdaftar.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'min' => [
        'string' => ':attribute minimal :min karakter.',
        'numeric' => ':attribute minimal :min.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
    ],
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
        'numeric' => ':attribute maksimal :max.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
    ],
    'in' => 'Pilihan :attribute tidak valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'integer' => ':attribute harus berupa angka.',
    'string' => ':attribute harus berupa teks.',
    'file' => ':attribute harus berupa berkas.',
    'mimes' => ':attribute harus berformat: :values.',
];
