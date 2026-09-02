<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@landman.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        $editeur = User::firstOrCreate(
            ['email' => 'editeur@landman.test'],
            [
                'name' => 'Editeur BTP',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $editeur->assignRole('Editeur BTP');

        $commercial = User::firstOrCreate(
            ['email' => 'commercial@landman.test'],
            [
                'name' => 'Commercial Lotissement',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $commercial->assignRole('Commercial Lotissement');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(SiteSettingSeeder::class);
        $this->call(DemoDataSeeder::class);
    }
}
