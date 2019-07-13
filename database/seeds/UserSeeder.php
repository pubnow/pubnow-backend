<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleAdmin = Role::where(['name' => 'admin'])->first();
        User::create([
            'name' => 'Ạt min',
            'username' => 'admin',
            'password' => '@dm1nn',
            'email' => 'admin@pubnow.co',
            'role_id' => $roleAdmin->id,
        ]);
    }
}
