<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionCategorySeeder extends Seeder
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
                'id'   => 1,
                'description' => 'Deposit'
            ],
            [
                'id'   => 2,
                'description' => 'Expanses'
            ]
        ];
        foreach ($statuses as $status) {
            DB::table('transaction_categories')->insert($status);
        }
    }
}
