<?php

namespace Database\Seeders;

use App\Enums\Permission as EnumPermission;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (EnumPermission::values() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
