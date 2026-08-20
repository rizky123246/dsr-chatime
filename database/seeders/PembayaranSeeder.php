<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pembayaran;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pembayarans = [
            [
                'amount' => 50000.00,
                'order_no' => 'ORD001',
                'receipt_no' => 'RCP001',
                'invoice_reference' => 'INV001',
                'mop_code' => 'CASH',
                'mop_name' => 'Cash Payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 75000.00,
                'order_no' => 'ORD002',
                'receipt_no' => 'RCP002',
                'invoice_reference' => 'INV002',
                'mop_code' => 'OVO',
                'mop_name' => 'OVO E-Wallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 35000.00,
                'order_no' => 'ORD003',
                'receipt_no' => 'RCP003',
                'invoice_reference' => null,
                'mop_code' => 'GCASH',
                'mop_name' => 'GoPay',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 120000.00,
                'order_no' => 'ORD004',
                'receipt_no' => 'RCP004',
                'invoice_reference' => 'INV004',
                'mop_code' => 'CC',
                'mop_name' => 'Credit Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 28000.00,
                'order_no' => 'ORD005',
                'receipt_no' => 'RCP005',
                'invoice_reference' => 'INV005',
                'mop_code' => 'DANA',
                'mop_name' => 'DANA E-Wallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 95000.00,
                'order_no' => 'ORD006',
                'receipt_no' => 'RCP006',
                'invoice_reference' => null,
                'mop_code' => 'CASH',
                'mop_name' => 'Cash Payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 45000.00,
                'order_no' => 'ORD007',
                'receipt_no' => 'RCP007',
                'invoice_reference' => 'INV007',
                'mop_code' => 'SHOPEEPAY',
                'mop_name' => 'ShopeePay',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 67000.00,
                'order_no' => 'ORD008',
                'receipt_no' => 'RCP008',
                'invoice_reference' => 'INV008',
                'mop_code' => 'DC',
                'mop_name' => 'Debit Card',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 89000.00,
                'order_no' => 'ORD009',
                'receipt_no' => 'RCP009',
                'invoice_reference' => null,
                'mop_code' => 'OVO',
                'mop_name' => 'OVO E-Wallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'amount' => 156000.00,
                'order_no' => 'ORD010',
                'receipt_no' => 'RCP010',
                'invoice_reference' => 'INV010',
                'mop_code' => 'CASH',
                'mop_name' => 'Cash Payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Pembayaran::insert($pembayarans);
    }
}
