<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //for checking manually created accounts and test each role
        // admin account
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // organizer accounts
        User::factory()->organizer()->create([
            'name' => 'Joyce Villudo',
            'email' => 'villudo.joyce@gmail.com',
        ]);
        User::factory()->organizer()->create([
            'name' => 'Mark Anthony Villudo',
            'email' => 'markanthony.villudo@gmail.com',
        ]);

        // customer accounts
        User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer+1@example.com',
        ]);
        User::factory()->customer()->create([
            'name' => 'Customer User',
            'email' => 'customer+2@example.com',
        ]);
    }
}
