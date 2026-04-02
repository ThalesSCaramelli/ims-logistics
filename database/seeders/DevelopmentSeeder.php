<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Worker;
use App\Models\User;
use App\Models\ClientPrice;
use App\Models\Product;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('DevelopmentSeeder cannot run in production!');
            return;
        }

        // Test client
        $client = Client::firstOrCreate(['name' => 'Medlog Australia'], [
            'email'           => 'ops@medlog.com.au',
            'phone'           => '+61 3 9000 0000',
            'abn'             => '12 345 678 901',
            'payment_terms'   => 14,
            'requires_photos' => true,
            'is_active'       => true,
        ]);

        $site = $client->sites()->firstOrCreate(['name' => 'Altona DC'], [
            'address'         => '1 Industrial Ave, Altona North VIC 3025',
            'contact_person'  => 'David Chen',
            'phone'           => '+61 3 9000 0001',
            'requires_photos' => true,
            'skill_label'     => 'SKU',
            'is_active'       => true,
        ]);

        // Test workers
        $workersData = [
            ['name' => 'Thales Costa',    'phone' => '+61 4 1111 0001', 'abn' => '11 111 111 001', 'is_australian' => true,  'has_forklift' => true,  'min_weekly' => 800],
            ['name' => 'João Martins',    'phone' => '+61 4 1111 0002', 'abn' => '11 111 111 002', 'is_australian' => false, 'has_forklift' => false, 'min_weekly' => null],
            ['name' => 'André Silva',     'phone' => '+61 4 1111 0003', 'abn' => '11 111 111 003', 'is_australian' => true,  'has_forklift' => true,  'min_weekly' => null],
            ['name' => 'Lucas Ferreira',  'phone' => '+61 4 1111 0004', 'abn' => '11 111 111 004', 'is_australian' => false, 'has_forklift' => false, 'min_weekly' => 500],
            ['name' => 'Maria Nguyen',    'phone' => '+61 4 1111 0005', 'abn' => '11 111 111 005', 'is_australian' => true,  'has_forklift' => false, 'min_weekly' => 500],
        ];

        foreach ($workersData as $workerData) {
            $worker = Worker::firstOrCreate(
                ['abn' => $workerData['abn']],
                array_merge($workerData, ['status' => 'active'])
            );

            if (!$workerData['is_australian']) {
                $worker->visa()->firstOrCreate(['worker_id' => $worker->id], [
                    'visa_class'              => '482',
                    'valid_until'             => now()->addYear(),
                    'work_permitted'          => true,
                    'fortnightly_hours_limit' => 48,
                ]);
            }

            $email = strtolower(str_replace([' ', 'ã', 'é', 'ê', 'ô', 'ç'], ['.', 'a', 'e', 'e', 'o', 'c'], $worker->name)) . '@test.com';

            User::firstOrCreate(['email' => $email], [
                'password'  => bcrypt('password123'),
                'worker_id' => $worker->id,
                'is_active' => true,
            ])->assignRole('worker');
        }

        // Price config: Medlog / Altona DC / FAK 40ft
        $fak40 = Product::where('name', 'FAK 40ft')->first();

        if ($fak40) {
            $price = ClientPrice::firstOrCreate(
                ['client_id' => $client->id, 'site_id' => $site->id, 'product_id' => $fak40->id],
                [
                    'client_base_price'         => 210.00,
                    'client_boxes_limit'        => 500,
                    'client_boxes_block_size'   => 500,
                    'client_boxes_block_price'  => 35.00,
                    'client_skills_limit'       => 5,
                    'client_skills_block_size'  => 5,
                    'client_skills_block_price' => 25.00,
                    'labor_base_price'          => 150.00,
                    'labor_boxes_limit'         => 500,
                    'labor_boxes_block_size'    => 500,
                    'labor_boxes_block_price'   => 20.00,
                    'labor_skills_limit'        => 5,
                    'labor_skills_block_size'   => 5,
                    'labor_skills_block_price'  => 15.00,
                    'extra_work_client_rate'    => 38.00,
                    'extra_work_labor_rate'     => 30.00,
                    'waiting_time_client_rate'  => 32.00,
                    'waiting_time_labor_rate'   => 24.00,
                    'is_active'                 => true,
                ]
            );

            $price->extras()->firstOrCreate(['name' => 'Extra worker'], [
                'client_value' => 35.00,
                'labor_value'  => 25.00,
                'unit'         => 'per_worker',
                'rule'         => 'required',
                'condition'    => 'worker_count > 4',
            ]);
        }

        $this->command->info('Development data seeded.');
        $this->command->info('Workers login: [name]@test.com / password123');
        $this->command->info('Admin login: admin@ims.com.au / admin123');
    }
}
