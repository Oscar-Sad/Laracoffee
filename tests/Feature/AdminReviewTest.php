<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_product_review_page()
    {
        // =========================
        // 1. Create Admin User
        // =========================
        $role = \App\Models\Role::create([
            'role_name' => 'Admin'
        ]);

        $admin = User::create([
            'fullname' => 'Admin User',
            'username' => 'admin1',
            'email' => 'admin@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Bangi',
            'image' => 'default.png',
            'role_id' => 1,
            'coupon' => 0,
            'point' => 0,
        ]);

        // =========================
        // 2. Create Product
        // =========================
        $product = Product::create([
            'product_name' => 'Test Product',
            'orientation' => 'Test orientation',
            'description' => 'Test description',
            'price' => 100,
            'stock' => 10,
            'discount' => 0,
            'image' => 'default.png',
        ]);

        // =========================
        // 3. Create Review
        // =========================
        Review::create([
            'user_id' => $admin->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Great product!',
            'is_edit' => 0,
        ]);

        // =========================
        // 4. Act as Admin & Request Page
        // =========================
        $response = $this->actingAs($admin)
            ->get("/review/product/{$product->id}");

        // =========================
        // 5. Assertions
        // =========================
        $response->assertStatus(200);
        $response->assertViewIs('.review.product_review');

        // check page content
        $response->assertSee('Product Review');
        $response->assertSee('Great product!');
    }
}
