<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promotion;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Black Friday Promotion - Active
        Promotion::create([
            'name' => 'Black Friday Mega Sale',
            'code' => 'BLACKFRIDAY50',
            'description' => 'Nossa maior promoção do ano! Descontos especiais por tempo limitado.',
            'discount_percentage' => 50.00,
            'original_price' => 97.00,
            'discounted_price' => 48.50,
            'currency' => 'BRL',
            'is_active' => true,
            'show_urgency' => true,
            'show_floating_banner' => true,
            'valid_from' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->addDays(7),
            'max_uses' => 100,
            'current_uses' => 23,
        ]);

        // Holiday Special - Inactive (Future)
        Promotion::create([
            'name' => 'Holiday Special 2025',
            'code' => 'HOLIDAY25',
            'description' => 'Promoção especial de fim de ano com desconto exclusivo.',
            'discount_percentage' => 25.00,
            'original_price' => 97.00,
            'discounted_price' => 72.75,
            'currency' => 'BRL',
            'is_active' => false,
            'show_urgency' => false,
            'show_floating_banner' => false,
            'valid_from' => Carbon::now()->addDays(30),
            'valid_until' => Carbon::now()->addDays(37),
            'max_uses' => 200,
            'current_uses' => 0,
        ]);

        // Welcome Promotion - Active but ending soon
        Promotion::create([
            'name' => 'Oferta de Boas-Vindas',
            'code' => 'WELCOME30',
            'description' => 'Oferta especial para novos usuários. Aproveite enquanto é tempo!',
            'discount_percentage' => 30.00,
            'original_price' => 97.00,
            'discounted_price' => 67.90,
            'currency' => 'BRL',
            'is_active' => true,
            'show_urgency' => true,
            'show_floating_banner' => false,
            'valid_from' => Carbon::now()->subDays(10),
            'valid_until' => Carbon::now()->addHours(48),
            'max_uses' => 50,
            'current_uses' => 12,
        ]);

        // Expired Promotion
        Promotion::create([
            'name' => 'Promoção de Lançamento',
            'code' => 'LAUNCH20',
            'description' => 'Oferta especial para o lançamento da plataforma.',
            'discount_percentage' => 20.00,
            'original_price' => 97.00,
            'discounted_price' => 77.60,
            'currency' => 'BRL',
            'is_active' => false,
            'show_urgency' => false,
            'show_floating_banner' => false,
            'valid_from' => Carbon::now()->subDays(30),
            'valid_until' => Carbon::now()->subDays(7),
            'max_uses' => 150,
            'current_uses' => 89,
        ]);

        // Flash Sale - High urgency
        Promotion::create([
            'name' => 'Flash Sale 24h',
            'code' => 'FLASH40',
            'description' => 'Oferta relâmpago! Apenas 24 horas para aproveitar.',
            'discount_percentage' => 40.00,
            'original_price' => 97.00,
            'discounted_price' => 58.20,
            'currency' => 'BRL',
            'is_active' => true,
            'show_urgency' => true,
            'show_floating_banner' => true,
            'valid_from' => Carbon::now()->subHours(6),
            'valid_until' => Carbon::now()->addHours(18),
            'max_uses' => 25,
            'current_uses' => 8,
        ]);
    }
}
