<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'System administrator with full access'
            ],
            [
                'name' => 'Category Coordinator',
                'slug' => 'category_coordinator',
                'description' => 'Manages categories and subcategories'
            ],
            [
                'name' => 'Sub-category Coordinator',
                'slug' => 'subcategory_coordinator',
                'description' => 'Manages subcategories only'
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}

