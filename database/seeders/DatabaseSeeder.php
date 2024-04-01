<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Language;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Timo',
            'email' => 'timohoff@live.nl',
            'password' => bcrypt('abc')
        ]);

        Language::factory()->create([
            'code' => 'en',
            'name' => 'English'
        ]);

        Language::factory()->create([
            'code' => 'nl',
            'name' => 'Dutch'
        ]);
    }
}
