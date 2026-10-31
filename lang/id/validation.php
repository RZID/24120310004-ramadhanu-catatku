<?php

declare(strict_types=1);

/*
 * Only the rules this application actually applies are translated here; any
 * other rule falls back to English through `APP_FALLBACK_LOCALE`.
 */
return [

    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
    'date_format' => 'Kolom :attribute harus sesuai format :format.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'lowercase' => 'Kolom :attribute harus berupa huruf kecil.',
    'max' => [
        'array' => 'Kolom :attribute tidak boleh lebih dari :max item.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'array' => 'Kolom :attribute harus memiliki minimal :min item.',
        'numeric' => 'Kolom :attribute minimal :min.',
        'string' => 'Kolom :attribute minimal :min karakter.',
    ],
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Kolom :attribute harus mengandung minimal satu huruf.',
        'mixed' => 'Kolom :attribute harus mengandung minimal satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Kolom :attribute harus mengandung minimal satu angka.',
        'symbols' => 'Kolom :attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':attribute pernah muncul dalam kebocoran data. Silakan pilih :attribute lain.',
    ],
    'required' => 'Kolom :attribute wajib diisi.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi',
        'type' => 'tipe',
        'amount' => 'jumlah',
        'transacted_on' => 'tanggal transaksi',
        'note' => 'catatan',
        'month' => 'bulan',
        'wallet_id' => 'dompet',
        'category_id' => 'kategori',
    ],

];
