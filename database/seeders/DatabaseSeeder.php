<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or update Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Super Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create Default Cashier User
        User::updateOrCreate(
            ['email' => 'kasir@pos.com'],
            [
                'name' => 'Kasir 01',
                'role' => 'kasir',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Default Store Settings
        \App\Models\Setting::set('store_name', 'POSPRO CAFE & RESTO');
        \App\Models\Setting::set('store_address', 'Jl. Sudirman No. 123, Jakarta Selatan');
        \App\Models\Setting::set('store_phone', '0812-3456-7890');
        \App\Models\Setting::set('receipt_footer', 'Terima Kasih Atas Kunjungan Anda!\nBarang yang sudah dibeli tidak dapat ditukar');

        // 3. Create Categories
        $categories = [
            [
                'name' => 'Coffee & Espresso',
                'slug' => 'coffee',
                'icon' => 'fa-mug-hot',
                'color' => 'amber',
            ],
            [
                'name' => 'Non-Coffee & Tea',
                'slug' => 'non-coffee',
                'icon' => 'fa-glass-water',
                'color' => 'blue',
            ],
            [
                'name' => 'Makanan Utama',
                'slug' => 'food',
                'icon' => 'fa-utensils',
                'color' => 'orange',
            ],
            [
                'name' => 'Snack & Bites',
                'slug' => 'snacks',
                'icon' => 'fa-cookie-bite',
                'color' => 'emerald',
            ],
            [
                'name' => 'Pastry & Dessert',
                'slug' => 'dessert',
                'icon' => 'fa-cake-candles',
                'color' => 'purple',
            ],
        ];

        $catModels = [];
        foreach ($categories as $cat) {
            $catModels[$cat['slug']] = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 4. Create Sample Products (Both Unlimited Cafe Mode & Retail Stock Mode)
        $products = [
            // Coffee (Cafe Mode: manage_stock = false)
            [
                'category_id' => $catModels['coffee']->id,
                'name' => 'Es Kopi Susu Gula Aren',
                'sku' => 'COF-001',
                'barcode' => '8991001001',
                'price' => 18000,
                'cost_price' => 8000,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['coffee']->id,
                'name' => 'Caffe Americano Ice',
                'sku' => 'COF-002',
                'barcode' => '8991001002',
                'price' => 15000,
                'cost_price' => 5000,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['coffee']->id,
                'name' => 'Caramel Macchiato',
                'sku' => 'COF-003',
                'barcode' => '8991001003',
                'price' => 24000,
                'cost_price' => 11000,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?w=500&auto=format&fit=crop&q=60',
            ],

            // Non-Coffee (Cafe Mode: manage_stock = false)
            [
                'category_id' => $catModels['non-coffee']->id,
                'name' => 'Matcha Latte Ice',
                'sku' => 'TEA-001',
                'barcode' => '8991002001',
                'price' => 22000,
                'cost_price' => 9500,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['non-coffee']->id,
                'name' => 'Lemon Black Tea',
                'sku' => 'TEA-002',
                'barcode' => '8991002002',
                'price' => 14000,
                'cost_price' => 4500,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=500&auto=format&fit=crop&q=60',
            ],

            // Food (Cafe Mode: manage_stock = false)
            [
                'category_id' => $catModels['food']->id,
                'name' => 'Nasi Goreng Spesial',
                'sku' => 'FOD-001',
                'barcode' => '8991003001',
                'price' => 28000,
                'cost_price' => 13000,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['food']->id,
                'name' => 'Mie Goreng Ayam Geprek',
                'sku' => 'FOD-002',
                'barcode' => '8991003002',
                'price' => 25000,
                'cost_price' => 12000,
                'manage_stock' => false,
                'stock' => 0,
                'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=500&auto=format&fit=crop&q=60',
            ],

            // Snacks (Retail/Warung Mode: manage_stock = true)
            [
                'category_id' => $catModels['snacks']->id,
                'name' => 'French Fries Crispy',
                'sku' => 'SNK-001',
                'barcode' => '8991004001',
                'price' => 16000,
                'cost_price' => 6000,
                'manage_stock' => true,
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['snacks']->id,
                'name' => 'Cireng Bumbu Rujak',
                'sku' => 'SNK-002',
                'barcode' => '8991004002',
                'price' => 15000,
                'cost_price' => 5000,
                'manage_stock' => true,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?w=500&auto=format&fit=crop&q=60',
            ],

            // Dessert (Retail/Warung Mode: manage_stock = true)
            [
                'category_id' => $catModels['dessert']->id,
                'name' => 'Croissant Butter Original',
                'sku' => 'DST-001',
                'barcode' => '8991005001',
                'price' => 20000,
                'cost_price' => 9000,
                'manage_stock' => true,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=500&auto=format&fit=crop&q=60',
            ],
            [
                'category_id' => $catModels['dessert']->id,
                'name' => 'Fudgy Brownie Melt',
                'sku' => 'DST-002',
                'barcode' => '8991005002',
                'price' => 18000,
                'cost_price' => 8000,
                'manage_stock' => true,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=500&auto=format&fit=crop&q=60',
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(
                ['sku' => $prod['sku']],
                $prod
            );
        }

        // 5. Create Sample Coupons / Vouchers
        $coupons = [
            [
                'code' => 'COFFEE2026',
                'title' => 'Spesial Pecinta Kopi 15%',
                'category_id' => $catModels['coffee']->id,
                'discount_type' => 'PERCENT',
                'discount_value' => 15,
                'min_order_amount' => 0,
                'max_discount_amount' => 25000,
                'usage_limit' => 200,
                'is_active' => true,
            ],
            [
                'code' => 'FOOD10',
                'title' => 'Diskon Makanan Utama 10%',
                'category_id' => $catModels['food']->id,
                'discount_type' => 'PERCENT',
                'discount_value' => 10,
                'min_order_amount' => 20000,
                'max_discount_amount' => 15000,
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'HEMAT20K',
                'title' => 'Potongan Langsung Rp 20.000 (Semua Menu)',
                'category_id' => null,
                'discount_type' => 'FIXED',
                'discount_value' => 20000,
                'min_order_amount' => 50000,
                'usage_limit' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            \App\Models\Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }
}
