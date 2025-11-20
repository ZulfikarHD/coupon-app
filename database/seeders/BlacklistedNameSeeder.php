<?php

namespace Database\Seeders;

use App\Models\BlacklistedName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlacklistedNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample blacklisted names - add more as needed
        $blacklistedNames = [
            ['name' => 'admin', 'reason' => 'Reserved system name'],
            ['name' => 'test', 'reason' => 'Test account'],
            ['name' => 'root', 'reason' => 'System administrator'],
            ['name' => 'spam', 'reason' => 'Spam account'],
            ['name' => 'abuse', 'reason' => 'Abusive content'],
        ];

        foreach ($blacklistedNames as $blacklistedName) {
            BlacklistedName::updateOrCreate(
                ['name' => $blacklistedName['name']],
                ['reason' => $blacklistedName['reason']]
            );
        }

        $this->command->info('Blacklisted names seeded successfully!');
    }
}
