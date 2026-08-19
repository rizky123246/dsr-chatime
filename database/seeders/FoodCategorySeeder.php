<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\DB;

class FoodCategorySeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 biar tidak double data
        DB::table('food_category_items')->delete();
        DB::table('food_categories')->delete();

        $categories = [

                'jinja_fire' => [
                'label' => 'Jinja Fire',
                'items' => [
                    '10709786',
                    '10709792',
                    '10709790',
                    '10709791',
                    '10709785',
                    '10709789',
                ]
            ],

            'snowy' => [
                'label' => 'Snowy',
                'items' => [
                    '10675443',
                    '10675442',
                    '10675449',
                    '10675445',
                ]
            ],

            'k_pop' => [
                'label' => 'K-Pop',
                'items' => [
                    '10591670','10603558','10495830','10495831','10495832','10495833','10495834',
                    '10573373','10573374','10573375','10573376','10573377','10591672'
                ]
            ],

            'k_fries' => [
                'label' => 'K-Fries',
                'items' => [
                    '10591671','10591680','10591681','10591682','10591683','10591684',
                    '10591675','10591676','10591677','10591678','10591679','10591673', 
                    '10603559'
                ]
            ],

            'rappoki_tokpokki' => [
                'label' => 'Rappoki / Tokpokki',
                'items' => [
                    '10450836','10579700','10579701','10450837',
                    '10565324','10554596','10565323','10554595'
                ]
            ],

            'hotdakk' => [
                'label' => 'Hotdakk',
                'items' => [
                    '10661551','10661552','10661553','10675439',
                    '10454053','10453547','10454050','10454055',
                    '10453549','10454052', '10454054'
                ]
            ],

            'loaded_fries' => [
                'label' => 'Loaded Fries',
                'items' => [
                    '10603557','10591674'
                ]
            ],

            'sokkochi' => [
                'label' => 'Sokkochi',
                'items' => [
                    '10495746','10472301','10495748','10495747'
                ]
            ],

            'extra' => [
                'label' => 'Extra',
                'items' => [
                    '10455137','10454067','10454065','10454059',
                    '10454063','10454061'
                ]
            ],

                'free' => [
                'label' => 'Free Sauce',
                'items' => [
                    '10454066','10454064','10454068','10454060',
                    '10454062'
                ]
            ],
        ];

        foreach ($categories as $name => $data) {

            $category = FoodCategory::create([
                'name' => $name,
                'label' => $data['label']
            ]);

            $items = [];

            foreach ($data['items'] as $code) {
                $items[] = [
                    'category_id' => $category->id,
                    'article_code' => $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('food_category_items')->insert($items);
        }
    }
}