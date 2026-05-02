<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Role;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTransactionMonitorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_transaction_logs()
    {
        $role = Role::create([
            'role_name' => 'Admin',
        ]);
        // --------------------
        // ADMIN USER
        // --------------------
        $admin = User::create([
            'fullname' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Bangi',
            'image' => 'default.png',
            'role_id' => $role->id,
            'coupon' => 0,
            'point' => 0,
        ]);

        // --------------------
        // CATEGORY
        // --------------------
        $category = Category::create([
            'category_name' => 'Product Sale',
        ]);

        // --------------------
        // TRANSACTION (OUTCOME)
        // --------------------
        Transaction::create([
            'category_id' => $category->id,
            'description' => 'Buy ingredients',
            'income' => null,
            'outcome' => 50,
        ]);

        // --------------------
        // ACT AS ADMIN
        // --------------------
        $response = $this->actingAs($admin)
            ->get('/transaction');

        // --------------------
        // ASSERT
        // --------------------
        $response->assertStatus(200);
        $response->assertSee('Transaction List');
    }
}
