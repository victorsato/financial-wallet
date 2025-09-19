<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User;
        $user->name = 'Gerente teste';
        $user->email = 'gerente@gmail.com';
        $user->password = Hash::make('123456');
        $user->save();
        $user->assignRole('Gerente');
    }
}
