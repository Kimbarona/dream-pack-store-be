<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Dream Pack',
            'site_description' => 'Premium e-commerce store for quality products',
            'site_email' => 'contact@dreampack.com',
            'currency' => 'ILS',
            'tax_rate' => 0.08,
            'shipping_cost' => 9.99,
            'free_shipping_threshold' => 100.00,
            'allow_guest_checkout' => true,
            'enable_crypto_payments' => true,
            'supported_cryptos' => ['BTC', 'ETH', 'USDT'],
            'crypto_payment_timeout' => 30, // minutes
            'meta_title_template' => '{product_name} - Dream Pack',
            'meta_description_template' => 'Buy {product_name} for only {price}. {product_description}',
            'enable_reviews' => true,
            'auto_approve_reviews' => false,
            'low_stock_threshold' => 10,
            'out_of_stock_action' => 'hide', // hide, show, notify
        ];

        foreach ($settings as $key => $value) {
            Setting::create([
                'key' => $key,
                'value' => is_array($value) ? $value : null,
                'text_value' => is_array($value) ? null : (string) $value,
            ]);
        }
    }
}