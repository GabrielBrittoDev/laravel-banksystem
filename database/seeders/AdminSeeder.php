<?php

namespace Database\Seeders;

use App\Domain\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            'name'     => 'Mr Admin',
            'role_id'  => RoleEnum::ADMINISTRATOR,
            'username' => 'admin',
            'email'    => 'admin@admin.com',
            'password' => bcrypt('12345678')
        ];

        DB::table('users')->insert($user);
    }
}
