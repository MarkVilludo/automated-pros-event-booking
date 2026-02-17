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

        //for customer account, they need to manually create
        // their accounts using api/register endpoint.
    }
}
