<?php

namespace Database\Seeders;

use App\Models\Analytics;
use Illuminate\Database\Seeder;

class AnalyticsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing analytics data
        Analytics::truncate();

        // Generate home page visits for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i);
            $visitCount = rand(15, 45); // Random visits per day
            
            for ($j = 0; $j < $visitCount; $j++) {
                Analytics::create([
                    'event_type' => 'page_view',
                    'page' => 'home',
                    'url' => url('/'),
                    'referrer' => $this->getRandomReferrer(),
                    'user_agent' => $this->getRandomUserAgent(),
                    'ip_address' => $this->getRandomIP(),
                    'session_id' => 'test_session_' . uniqid(),
                    'created_at' => $date->subMinutes(rand(0, 1440)), // Random time within the day
                    'updated_at' => $date->subMinutes(rand(0, 1440)),
                ]);
            }
        }

        // Generate sales page visits for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i);
            $visitCount = rand(8, 25); // Fewer sales page visits
            
            for ($j = 0; $j < $visitCount; $j++) {
                Analytics::create([
                    'event_type' => 'page_view',
                    'page' => 'sales',
                    'url' => url('/sales'),
                    'referrer' => $this->getRandomReferrer(),
                    'user_agent' => $this->getRandomUserAgent(),
                    'ip_address' => $this->getRandomIP(),
                    'session_id' => 'test_session_' . uniqid(),
                    'created_at' => $date->subMinutes(rand(0, 1440)),
                    'updated_at' => $date->subMinutes(rand(0, 1440)),
                ]);
            }
        }

        // Generate some button clicks
        for ($i = 0; $i < 50; $i++) {
            Analytics::create([
                'event_type' => 'button_click',
                'page' => rand(0, 1) ? 'home' : 'sales',
                'element' => $this->getRandomButton(),
                'url' => rand(0, 1) ? url('/') : url('/sales'),
                'referrer' => $this->getRandomReferrer(),
                'user_agent' => $this->getRandomUserAgent(),
                'ip_address' => $this->getRandomIP(),
                'session_id' => 'test_session_' . uniqid(),
                'created_at' => now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440)),
                'updated_at' => now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440)),
            ]);
        }

        echo "Analytics test data seeded successfully.\n";
        echo "Home page visits: " . Analytics::where('page', 'home')->where('event_type', 'page_view')->count() . "\n";
        echo "Sales page visits: " . Analytics::where('page', 'sales')->where('event_type', 'page_view')->count() . "\n";
        echo "Button clicks: " . Analytics::where('event_type', 'button_click')->count() . "\n";
    }

    private function getRandomReferrer()
    {
        $referrers = [
            'https://google.com/search',
            'https://www.google.com.br/search',
            'https://www.facebook.com/',
            'https://twitter.com/',
            'https://linkedin.com/',
            null, // Direct traffic
            null,
            null,
        ];

        return $referrers[array_rand($referrers)];
    }

    private function getRandomUserAgent()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1',
        ];

        return $userAgents[array_rand($userAgents)];
    }

    private function getRandomIP()
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);
    }

    private function getRandomButton()
    {
        $buttons = [
            'hero-register',
            'hero-demo',
            'sales-cta',
            'nav-register',
            'plan-select',
            'testimonial-cta',
        ];

        return $buttons[array_rand($buttons)];
    }
}
