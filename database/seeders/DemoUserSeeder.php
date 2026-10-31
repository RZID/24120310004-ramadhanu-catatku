<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data is written by hand instead of generated, so every fresh seed
 * produces exactly the same dashboard numbers.
 */
class DemoUserSeeder extends Seeder
{
    /**
     * @var list<array{
     *     name: string,
     *     email: string,
     *     wallets: list<string>,
     *     income_categories: list<string>,
     *     expense_categories: list<string>,
     *     transactions: list<array{string, string, string, int, string}>
     * }>
     */
    private const USERS = [
        [
            'name' => 'Budi Santoso',
            'email' => 'budi@catatku.test',
            'wallets' => ['Dompet Tunai', 'BCA', 'GoPay'],
            'income_categories' => ['Gaji', 'Bonus', 'Freelance'],
            'expense_categories' => ['Makan', 'Transportasi', 'Belanja', 'Tagihan', 'Hiburan', 'Kesehatan'],
            'transactions' => [
                ['2026-08-01', 'Makan', 'Dompet Tunai', 45000, 'Sarapan bubur ayam'],
                ['2026-08-03', 'Transportasi', 'GoPay', 25000, 'Ojek ke kantor'],
                ['2026-08-05', 'Belanja', 'BCA', 320000, 'Belanja bulanan Indomaret'],
                ['2026-08-08', 'Makan', 'GoPay', 38000, 'Makan siang warteg'],
                ['2026-08-10', 'Tagihan', 'BCA', 450000, 'Listrik dan air'],
                ['2026-08-12', 'Hiburan', 'GoPay', 60000, 'Nonton bioskop'],
                ['2026-08-15', 'Transportasi', 'GoPay', 30000, 'Bensin motor'],
                ['2026-08-20', 'Makan', 'Dompet Tunai', 52000, 'Makan malam keluarga'],
                ['2026-08-25', 'Gaji', 'BCA', 8500000, 'Gaji bulan Agustus'],
                ['2026-08-28', 'Kesehatan', 'Dompet Tunai', 150000, 'Beli vitamin'],
                ['2026-09-02', 'Makan', 'Dompet Tunai', 40000, 'Sarapan nasi uduk'],
                ['2026-09-04', 'Belanja', 'BCA', 275000, 'Belanja sayur dan buah'],
                ['2026-09-07', 'Transportasi', 'GoPay', 22000, 'Ojek ke stasiun'],
                ['2026-09-09', 'Tagihan', 'BCA', 465000, 'Listrik dan air'],
                ['2026-09-11', 'Makan', 'GoPay', 42000, 'Makan siang padang'],
                ['2026-09-14', 'Freelance', 'BCA', 1750000, 'Proyek desain logo'],
                ['2026-09-18', 'Hiburan', 'GoPay', 75000, 'Konser musik kampus'],
                ['2026-09-22', 'Makan', 'Dompet Tunai', 48000, 'Makan malam'],
                ['2026-09-25', 'Gaji', 'BCA', 8500000, 'Gaji bulan September'],
                ['2026-09-29', 'Transportasi', 'Dompet Tunai', 20000, 'Angkot'],
                ['2026-10-01', 'Makan', 'Dompet Tunai', 35000, 'Sarapan bubur'],
                ['2026-10-03', 'Belanja', 'BCA', 310000, 'Belanja bulanan'],
                ['2026-10-06', 'Transportasi', 'GoPay', 27000, 'Ojek ke kantor'],
                ['2026-10-08', 'Tagihan', 'BCA', 470000, 'Listrik dan air'],
                ['2026-10-10', 'Bonus', 'BCA', 1000000, 'Bonus proyek'],
                ['2026-10-13', 'Makan', 'GoPay', 39000, 'Makan siang'],
                ['2026-10-17', 'Kesehatan', 'Dompet Tunai', 210000, 'Periksa gigi'],
                ['2026-10-21', 'Hiburan', 'GoPay', 55000, 'Langganan streaming'],
                ['2026-10-25', 'Gaji', 'BCA', 8500000, 'Gaji bulan Oktober'],
                ['2026-10-29', 'Makan', 'Dompet Tunai', 44000, 'Makan malam'],
            ],
        ],
        [
            'name' => 'Siti Rahayu',
            'email' => 'siti@catatku.test',
            'wallets' => ['Dompet Tunai', 'Mandiri'],
            'income_categories' => ['Gaji', 'Usaha'],
            'expense_categories' => ['Makan', 'Transportasi', 'Belanja', 'Tagihan', 'Pendidikan'],
            'transactions' => [
                ['2026-08-02', 'Makan', 'Dompet Tunai', 30000, 'Sarapan nasi kuning'],
                ['2026-08-05', 'Transportasi', 'Dompet Tunai', 18000, 'Angkot ke pasar'],
                ['2026-08-07', 'Belanja', 'Mandiri', 195000, 'Belanja bahan masakan'],
                ['2026-08-11', 'Tagihan', 'Mandiri', 320000, 'Listrik rumah'],
                ['2026-08-14', 'Pendidikan', 'Mandiri', 350000, 'Buku pelajaran anak'],
                ['2026-08-18', 'Makan', 'Dompet Tunai', 36000, 'Makan siang'],
                ['2026-08-22', 'Usaha', 'Mandiri', 950000, 'Penjualan kue kering'],
                ['2026-08-26', 'Gaji', 'Mandiri', 6200000, 'Gaji bulan Agustus'],
                ['2026-08-29', 'Transportasi', 'Dompet Tunai', 25000, 'Ojek ke sekolah'],
                ['2026-08-31', 'Makan', 'Dompet Tunai', 41000, 'Makan malam keluarga'],
                ['2026-09-03', 'Makan', 'Dompet Tunai', 28000, 'Sarapan'],
                ['2026-09-06', 'Belanja', 'Mandiri', 210000, 'Belanja mingguan'],
                ['2026-09-09', 'Tagihan', 'Mandiri', 335000, 'Listrik rumah'],
                ['2026-09-12', 'Transportasi', 'Dompet Tunai', 20000, 'Angkot'],
                ['2026-09-15', 'Pendidikan', 'Mandiri', 425000, 'Les matematika'],
                ['2026-09-19', 'Makan', 'Dompet Tunai', 33000, 'Makan siang'],
                ['2026-09-23', 'Usaha', 'Mandiri', 1120000, 'Pesanan katering'],
                ['2026-09-26', 'Gaji', 'Mandiri', 6200000, 'Gaji bulan September'],
                ['2026-09-28', 'Belanja', 'Mandiri', 165000, 'Belanja sayur'],
                ['2026-09-30', 'Makan', 'Dompet Tunai', 39000, 'Makan malam'],
                ['2026-10-02', 'Makan', 'Dompet Tunai', 32000, 'Sarapan'],
                ['2026-10-05', 'Belanja', 'Mandiri', 240000, 'Belanja bulanan'],
                ['2026-10-07', 'Transportasi', 'Dompet Tunai', 22000, 'Ojek ke pasar'],
                ['2026-10-11', 'Tagihan', 'Mandiri', 340000, 'Listrik rumah'],
                ['2026-10-14', 'Pendidikan', 'Mandiri', 300000, 'Buku dan alat tulis'],
                ['2026-10-18', 'Usaha', 'Mandiri', 780000, 'Penjualan kue'],
                ['2026-10-20', 'Makan', 'Dompet Tunai', 37000, 'Makan siang'],
                ['2026-10-24', 'Gaji', 'Mandiri', 6200000, 'Gaji bulan Oktober'],
                ['2026-10-27', 'Belanja', 'Mandiri', 185000, 'Belanja bahan masakan'],
                ['2026-10-30', 'Makan', 'Dompet Tunai', 40000, 'Makan malam'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::USERS as $data) {
            $this->seedUser($data);
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     wallets: list<string>,
     *     income_categories: list<string>,
     *     expense_categories: list<string>,
     *     transactions: list<array{string, string, string, int, string}>
     * }  $data
     */
    private function seedUser(array $data): void
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => 'password',
        ]);

        $wallets = [];

        foreach ($data['wallets'] as $name) {
            $wallets[$name] = $user->wallets()->create(['name' => $name]);
        }

        $categories = [];

        foreach ($data['income_categories'] as $name) {
            $categories[$name] = $user->categories()->create([
                'name' => $name,
                'type' => TransactionType::Income,
            ]);
        }

        foreach ($data['expense_categories'] as $name) {
            $categories[$name] = $user->categories()->create([
                'name' => $name,
                'type' => TransactionType::Expense,
            ]);
        }

        foreach ($data['transactions'] as [$date, $categoryName, $walletName, $amount, $note]) {
            $category = $categories[$categoryName];

            $user->transactions()->create([
                'wallet_id' => $wallets[$walletName]->id,
                'category_id' => $category->id,
                // Copied from the category so the denormalized column stays true.
                'type' => $category->type,
                'amount' => $amount,
                'transacted_on' => $date,
                'note' => $note,
            ]);
        }
    }
}
