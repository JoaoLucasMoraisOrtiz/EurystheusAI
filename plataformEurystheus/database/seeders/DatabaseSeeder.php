<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chamar seeders necessários
        $this->call([
            AdminUserSeeder::class,
            PromotionSeeder::class,
        ]);
        
        // Comentar ou remover a criação de usuários de teste
        // User::factory(10)->create();
        
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
