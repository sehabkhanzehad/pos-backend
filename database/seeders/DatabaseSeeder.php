<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);

        [$owner, $staff, $tenant] = $this->createUser();

        Product::factory(20)->create(['tenant_id' => $tenant->id]);

        Customer::factory(10)->create(['tenant_id' => $tenant->id]);

        $customers = Customer::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->get();

        $this->createOrder($staff, $tenant, $customers, $products);
    }

    private function createUser(): array
    {
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => UserRole::Owner,
        ]);

        $tenant = $owner->createTenant(default: true);

        $staff = $tenant->staffs()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => 'password',
            'role' => UserRole::Staff,
        ]);

        $role = $tenant->ownedRoles()->create([
            'name' => 'Manager'
        ]);

        $role->givePermissions(Permission::values());
        $staff->assignRoles([$role->id]);

        return [
            $owner,
            $staff,
            $tenant
        ];
    }

    private function createOrder($staff, $tenant, $customers, $products): void
    {
        foreach ($customers as $customer) {
            $order = Order::factory()->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'created_by' => $staff->id,
            ]);

            $selectedProducts = $products->random(rand(1, 5));
            foreach ($selectedProducts as $product) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'qty' => rand(1, 5),
                    'unit_price' => $product->price,
                    'sub_total' => rand(1, 5) * $product->price,
                ]);
            }

            $order->update(['total_amount' => $order->items->sum('sub_total')]);
        }
    }
}
