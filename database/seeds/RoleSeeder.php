<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Quản trị viên',
        ]);

        Role::create([
            'name' => 'member',
            'description' => 'Thành viên',
        ]);
    }
}
