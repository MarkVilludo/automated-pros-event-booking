<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private function defaults(): array
    {
        return array_merge(
            User::factory()->definition(),
            ['password' => bcrypt('password')]
        );
    }

    public function run(): void
    {
        $defaults = $this->defaults();

        $admins = [
            ['name' => 'Admin One', 'email' => 'admin1@example.com'],
            ['name' => 'Admin Two', 'email' => 'admin2@example.com'],
        ];
        foreach ($admins as $attrs) {
            User::firstOrCreate(
                ['email' => $attrs['email']],
                array_merge($defaults, $attrs, ['role' => UserRole::Admin->value])
            );
        }

        $organizers = [
            ['name' => 'Organizer One', 'email' => 'organizer1@example.com'],
            ['name' => 'Organizer Two', 'email' => 'organizer2@example.com'],
            ['name' => 'Organizer Three', 'email' => 'organizer3@example.com'],
        ];
        foreach ($organizers as $attrs) {
            User::firstOrCreate(
                ['email' => $attrs['email']],
                array_merge($defaults, $attrs, ['role' => UserRole::Organizer->value])
            );
        }

        for ($i = 1; $i <= 10; $i++) {
            $email = "customer{$i}@example.com";
            User::firstOrCreate(
                ['email' => $email],
                array_merge($defaults, [
                    'name' => "Customer {$i}",
                    'email' => $email,
                    'role' => UserRole::Customer->value,
                ])
            );
        }
    }
}
