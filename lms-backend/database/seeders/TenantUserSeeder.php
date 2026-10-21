<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

use Hash;

class TenantUserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $users = [
      [
        'first_name' => 'Admin',
        'last_name' => 'Account',
        'role' => User::ROLE_ADMIN,
        'email' => 'admin@mail.com',
        'is_first_login' => 0
      ],
      [
        'first_name' => 'User',
        'last_name' => 'Account',
        'role' => User::ROLE_USER,
        'email' => 'user@mail.com',
        'is_first_login' => 0
      ],
      [
        'first_name' => 'Instructor',
        'last_name' => 'Account',
        'role' => User::ROLE_INSTRUCTOR,
        'email' => 'instructor@mail.com',
        'is_first_login' => 0
      ]
    ];

    foreach ($users as $key => $user) {
      $data = User::create([
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'role' => $user['role'],
        'is_first_login' => 0,
        'email' => $user['email'],
        'email_verified_at' => now(),
        'password' => Hash::make('password')
      ]);
    }
  }
}
