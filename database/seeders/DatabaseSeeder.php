<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Product;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = (string) env('ADMIN_PASSWORD', 'charm1900');
        if (app()->environment('production') && mb_strlen($adminPassword) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must contain at least 12 characters in production.');
        }

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@charm.dz')],
            ['name' => 'مدير المتجر', 'password' => Hash::make($adminPassword)]
        );

        $product = Product::updateOrCreate(
            ['slug' => 'charm-tote'],
            ['name' => 'حقيبة Charm القابلة للطي', 'price' => 1900, 'compare_at_price' => 2400, 'active' => true, 'free_shipping' => false, 'track_inventory' => true]
        );

        foreach ([
            ['key' => 'black', 'name' => 'أسود', 'stock' => 25, 'image' => '/assets/charm-black.png'],
            ['key' => 'brown', 'name' => 'بني', 'stock' => 18, 'image' => '/assets/charm-brown.png'],
            ['key' => 'green', 'name' => 'أخضر', 'stock' => 14, 'image' => '/assets/charm-green.png'],
            ['key' => 'beige', 'name' => 'بيج', 'stock' => 20, 'image' => '/assets/charm-beige.png'],
        ] as $variant) {
            $product->variants()->updateOrCreate(['key' => $variant['key']], $variant + ['active' => true]);
        }

        $names = [
            1 => ['Adrar', 'أدرار'], 2 => ['Chlef', 'الشلف'], 3 => ['Laghouat', 'الأغواط'], 4 => ['Oum El Bouaghi', 'أم البواقي'], 5 => ['Batna', 'باتنة'], 6 => ['Béjaïa', 'بجاية'], 7 => ['Biskra', 'بسكرة'], 8 => ['Béchar', 'بشار'], 9 => ['Blida', 'البليدة'], 10 => ['Bouira', 'البويرة'], 11 => ['Tamanrasset', 'تمنراست'], 12 => ['Tébessa', 'تبسة'], 13 => ['Tlemcen', 'تلمسان'], 14 => ['Tiaret', 'تيارت'], 15 => ['Tizi Ouzou', 'تيزي وزو'], 16 => ['Alger', 'الجزائر'], 17 => ['Djelfa', 'الجلفة'], 18 => ['Jijel', 'جيجل'], 19 => ['Sétif', 'سطيف'], 20 => ['Saïda', 'سعيدة'], 21 => ['Skikda', 'سكيكدة'], 22 => ['Sidi Bel Abbès', 'سيدي بلعباس'], 23 => ['Annaba', 'عنابة'], 24 => ['Guelma', 'قالمة'], 25 => ['Constantine', 'قسنطينة'], 26 => ['Médéa', 'المدية'], 27 => ['Mostaganem', 'مستغانم'], 28 => ["M'Sila", 'المسيلة'], 29 => ['Mascara', 'معسكر'], 30 => ['Ouargla', 'ورقلة'], 31 => ['Oran', 'وهران'], 32 => ['El Bayadh', 'البيض'], 33 => ['Illizi', 'إليزي'], 34 => ['Bordj Bou Arréridj', 'برج بوعريريج'], 35 => ['Boumerdès', 'بومرداس'], 36 => ['El Tarf', 'الطارف'], 37 => ['Tindouf', 'تندوف'], 38 => ['Tissemsilt', 'تيسمسيلت'], 39 => ['El Oued', 'الوادي'], 40 => ['Khenchela', 'خنشلة'], 41 => ['Souk Ahras', 'سوق أهراس'], 42 => ['Tipaza', 'تيبازة'], 43 => ['Mila', 'ميلة'], 44 => ['Aïn Defla', 'عين الدفلى'], 45 => ['Naâma', 'النعامة'], 46 => ['Aïn Témouchent', 'عين تموشنت'], 47 => ['Ghardaïa', 'غرداية'], 48 => ['Relizane', 'غليزان'], 49 => ['Timimoun', 'تيميمون'], 50 => ['Bordj Badji Mokhtar', 'برج باجي مختار'], 51 => ['Ouled Djellal', 'أولاد جلال'], 52 => ['Béni Abbès', 'بني عباس'], 53 => ['In Salah', 'عين صالح'], 54 => ['In Guezzam', 'عين قزام'], 55 => ['Touggourt', 'تقرت'], 56 => ['Djanet', 'جانت'], 57 => ["El M'Ghair", 'المغير'], 58 => ['El Menia', 'المنيعة'],
        ];
        foreach ($names as $id => [$name, $nameAr]) {
            Wilaya::updateOrCreate(['id' => $id], ['name' => $name, 'name_ar' => $nameAr]);
        }

        $communes = json_decode(file_get_contents(base_path('data/communes.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach (array_chunk($communes, 300) as $chunk) {
            Commune::upsert(array_map(fn ($row) => [
                'wilaya_id' => (int) $row['wilaya_code'],
                'name' => $row['commune_name_ascii'],
                'name_ar' => $row['commune_name'],
                'source_id' => (int) $row['num'],
                'created_at' => now(),
                'updated_at' => now(),
            ], $chunk), ['source_id'], ['wilaya_id', 'name', 'name_ar', 'updated_at']);
        }
    }
}
