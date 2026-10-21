<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Central\SuperAdmin;

use Artisan;
use Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SuperAdmin::create([
          'name' => 'Super Admin',
          'email' => config('services.super_admin.email'),
          'password' => Hash::make(config('services.super_admin.password') ?? throw new \RuntimeException('Set SUPER_ADMIN_PASSWORD before seeding.'))
        ]);

        Artisan::call('passport:install');
    }
}
