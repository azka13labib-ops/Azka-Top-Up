<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminUser;
use App\Models\Game;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Enums\AdminRoleEnum;
use App\Enums\MarkupTypeEnum;
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
        // 1. Create a Test Customer
        User::create([
            'name' => 'Azka Customer',
            'email' => 'customer@azkatopup.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Create an Admin User
        AdminUser::create([
            'name' => 'Super Admin AZKA',
            'email' => 'admin@azkatopup.com',
            'password' => Hash::make('password'),
            'role' => AdminRoleEnum::SUPER_ADMIN,
            'is_active' => true,
        ]);

        // 3. Create Games
        $mlbb = Game::create([
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'thumbnail_url' => 'https://via.placeholder.com/150',
            'description' => 'Top Up Diamonds Mobile Legends: Bang Bang Instan 24 Jam.',
            'id_field_label' => 'User ID',
            'id_field_placeholder' => 'Contoh: 12345678',
            'zone_field_label' => 'Zone ID',
            'needs_zone' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $ff = Game::create([
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'thumbnail_url' => 'https://via.placeholder.com/150',
            'description' => 'Top Up Diamonds Free Fire Instan 24 Jam.',
            'id_field_label' => 'Player ID',
            'id_field_placeholder' => 'Contoh: 87654321',
            'needs_zone' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $pubg = Game::create([
            'name' => 'PUBG Mobile',
            'slug' => 'pubg-mobile',
            'thumbnail_url' => 'https://via.placeholder.com/150',
            'description' => 'Top Up UC PUBG Mobile Instan 24 Jam.',
            'id_field_label' => 'Character ID',
            'id_field_placeholder' => 'Contoh: 5123456789',
            'needs_zone' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // 4. Create Products for Mobile Legends
        $mlbbProducts = [
            ['sku' => 'MLBB_8', 'name' => '8 Diamonds', 'price' => 2000, 'markup' => 500],
            ['sku' => 'MLBB_36', 'name' => '36 Diamonds', 'price' => 10000, 'markup' => 1500],
            ['sku' => 'MLBB_74', 'name' => '74 Diamonds', 'price' => 20000, 'markup' => 2500],
            ['sku' => 'MLBB_222', 'name' => '222 Diamonds', 'price' => 60000, 'markup' => 5000],
            ['sku' => 'MLBB_366', 'name' => '366 Diamonds', 'price' => 100000, 'markup' => 8000],
        ];

        foreach ($mlbbProducts as $p) {
            Product::create([
                'game_id' => $mlbb->id,
                'digiflazz_sku' => $p['sku'],
                'name' => $p['name'],
                'base_price' => $p['price'],
                'selling_price' => $p['price'] + $p['markup'],
                'markup_type' => MarkupTypeEnum::FLAT,
                'markup_value' => $p['markup'],
                'is_active' => true,
            ]);
        }

        // 5. Create Products for Free Fire
        $ffProducts = [
            ['sku' => 'FF_5', 'name' => '5 Diamonds', 'price' => 1000, 'markup' => 200],
            ['sku' => 'FF_50', 'name' => '50 Diamonds', 'price' => 8000, 'markup' => 1000],
            ['sku' => 'FF_70', 'name' => '70 Diamonds', 'price' => 10000, 'markup' => 1500],
            ['sku' => 'FF_140', 'name' => '140 Diamonds', 'price' => 20000, 'markup' => 2500],
            ['sku' => 'FF_355', 'name' => '355 Diamonds', 'price' => 50000, 'markup' => 5000],
        ];

        foreach ($ffProducts as $p) {
            Product::create([
                'game_id' => $ff->id,
                'digiflazz_sku' => $p['sku'],
                'name' => $p['name'],
                'base_price' => $p['price'],
                'selling_price' => $p['price'] + $p['markup'],
                'markup_type' => MarkupTypeEnum::FLAT,
                'markup_value' => $p['markup'],
                'is_active' => true,
            ]);
        }

        // 6. Create Products for PUBG Mobile
        $pubgProducts = [
            ['sku' => 'PUBG_32', 'name' => '32 UC', 'price' => 7000, 'markup' => 1000],
            ['sku' => 'PUBG_60', 'name' => '60 UC', 'price' => 13000, 'markup' => 1500],
            ['sku' => 'PUBG_325', 'name' => '325 UC', 'price' => 65000, 'markup' => 5000],
            ['sku' => 'PUBG_660', 'name' => '660 UC', 'price' => 130000, 'markup' => 10000],
            ['sku' => 'PUBG_1800', 'name' => '1800 UC', 'price' => 325000, 'markup' => 25000],
        ];

        foreach ($pubgProducts as $p) {
            Product::create([
                'game_id' => $pubg->id,
                'digiflazz_sku' => $p['sku'],
                'name' => $p['name'],
                'base_price' => $p['price'],
                'selling_price' => $p['price'] + $p['markup'],
                'markup_type' => MarkupTypeEnum::FLAT,
                'markup_value' => $p['markup'],
                'is_active' => true,
            ]);
        }

        // 7. Create Site Settings
        $settings = [
            'maintenance_mode' => '0',
            'announcement_banner' => 'Selamat datang di AZKA TOP UP! Dapatkan promo harga termurah setiap harinya.',
            'digiflazz_balance_alert_threshold' => '500000',
            'payment_methods_enabled' => json_encode([
                'qris' => 1,
                'gopay' => 1,
                'dana' => 1,
                'shopeepay' => 1,
                'bca_va' => 1,
                'bni_va' => 1,
                'mandiri_bill' => 1
            ])
        ];

        foreach ($settings as $key => $val) {
            SiteSetting::create([
                'key' => $key,
                'value' => $val,
            ]);
        }
    }
}

