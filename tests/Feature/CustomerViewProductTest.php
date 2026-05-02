<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerViewProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_view_product_details()
    {
        // 1. Create customer
        $customer = User::create([
            'fullname' => 'Customer User',
            'username' => 'customer1',
            'email' => 'customer@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Bangi',
            'image' => 'default.png',
            'role_id' => 2, // customer
            'coupon' => 0,
            'point' => 0,
        ]);

        // 2. Create product
        $product = Product::create([
            'product_name' => 'Test Product',
            'orientation' => 'Test orientation',
            'description' => 'Test description',
            'price' => 100,
            'stock' => 10,
            'discount' => 0,
            'image' => 'default.png',
        ]);

        // 3. Act as customer
        $response = $this->actingAs($customer)
        ->get("/product/data/{$product->id}");

        // 4. Assertions
        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('Test description');
    }
}
