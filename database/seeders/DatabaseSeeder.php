<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\InventoryItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Owner
        User::create([
            'name' => 'Shop Owner',
            'email' => 'owner@delivergate.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // 2. Create the Manager
        User::create([
            'name' => 'Shop Manager',
            'email' => 'manager@delivergate.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
        ]);

        // 3. Create the Cashier
        User::create([
            'name' => 'Shop Cashier',
            'email' => 'cashier@delivergate.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        // 4. Seed some dummy Inventory Items
        $items = [
            ['name' => 'Vanilla Bean', 'category' => 'Vanilla', 'price' => 450.00, 'quantity' => 50],
            ['name' => 'Dark Chocolate', 'category' => 'Chocolate', 'price' => 550.00, 'quantity' => 30],
            ['name' => 'Strawberry Swirl', 'category' => 'Strawberry', 'price' => 480.00, 'quantity' => 40],
        ];

        foreach ($items as $item) {
            InventoryItem::create($item);
        }

        // 3. Seed dummy Customers
        $customers = [
            ['name' => 'Nimal Perera', 'email' => 'nimal@example.com', 'phone' => '0771112223'],
            ['name' => 'Sunil Silva', 'email' => 'sunil@example.com', 'phone' => '0714445556'],
            ['name' => 'Kamal Fernando', 'email' => 'kamal@example.com', 'phone' => '0707778889'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

    }
}
