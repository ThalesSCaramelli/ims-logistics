<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Comum',             'type' => 'container', 'is_default' => true,  'has_boxes_skills' => true,  'description' => 'Default product — base price + optional additionals'],
            ['name' => 'FAK 20ft',          'type' => 'container', 'is_default' => false, 'has_boxes_skills' => true,  'description' => 'Fixed All Kinds — 20ft container'],
            ['name' => 'FAK 40ft',          'type' => 'container', 'is_default' => false, 'has_boxes_skills' => true,  'description' => 'Fixed All Kinds — 40ft container'],
            ['name' => 'FCL Padrão',        'type' => 'container', 'is_default' => false, 'has_boxes_skills' => true,  'description' => 'Full Container Load — standard'],
            ['name' => 'Carro - Fábrica',   'type' => 'container', 'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Vehicle — direct from factory'],
            ['name' => 'Carro - Particular','type' => 'container', 'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Vehicle — private origin'],
            ['name' => 'Painel Solar',      'type' => 'container', 'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Special cargo — solar panels'],
            ['name' => 'Labor Hire',        'type' => 'hour',      'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Hourly rate — billed per hour worked'],
            ['name' => 'Bateria',           'type' => 'mixed',     'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Battery — base price + hourly surcharge'],
            ['name' => 'Tesla',             'type' => 'mixed',     'is_default' => false, 'has_boxes_skills' => false, 'description' => 'Tesla — base price + hourly surcharge'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }

        $this->command->info('Products seeded: ' . count($products) . ' products.');
    }
}
