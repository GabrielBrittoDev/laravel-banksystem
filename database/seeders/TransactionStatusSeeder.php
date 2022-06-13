<?php

namespace Database\Seeders;

use App\Models\Transaction\TransactionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'id'          => 1,
                'description' => 'Pending'
            ],
            [
                'id'          => 2,
                'description' => 'Accepted'
            ],
            [
                'id'          => 3,
                'description' => 'Rejected'
            ]
        ];
        foreach ($statuses as $status) {
            DB::table('transaction_status')->insert($status);
        }
    }
}
