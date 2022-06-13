<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
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
                'name' => 'Administrator',
                'description' => 'Administrator user'
            ],
            [
                'id'   => 2,
                'name' => 'Customer',
                'description' => 'Normal user'
            ]
        ];
        foreach ($statuses as $status) {
            DB::table('roles')->insert($status);
        }
    }
}
