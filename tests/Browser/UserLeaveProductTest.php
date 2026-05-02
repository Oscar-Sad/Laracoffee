<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\Order;
use App\Models\Status;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserLeaveProductTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test customer can leave product review when order is done.
     */
    public function test_customer_can_leave_review()
    {
        // 1. Setup required data
        Role::create(['role_name' => 'Admin']); // ID 1
        Role::create(['role_name' => 'Customer']); // ID 2
        
        $customer = User::create([
            'fullname' => 'Customer User',
            'username' => 'customer1',
            'email' => 'customer@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Test Address',
            'image' => 'default.png',
            'role_id' => 2,
            'coupon' => 0,
            'point' => 0,
        ]);

        $product = Product::create([
            'product_name' => 'Test Product',
            'orientation' => 'Test',
            'description' => 'Test description',
            'price' => 100,
            'stock' => 10,
            'discount' => 0,
            'image' => 'default.png',
        ]);

        Status::create(['order_status' => 'done', 'style' => 'success']);

        // Create a completed order
        Order::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'quantity' => 1,
            'address' => 'Test Address',
            'shipping_address' => 'Test Shipping',
            'total_price' => 100,
            'payment_id' => 1,
            'bank_id' => 1,
            'note_id' => 1,
            'status_id' => 1,
            'transaction_doc' => 'proof.png',
            'is_done' => 1, // Crucial for isPurchased check
            'refusal_reason' => null,
            'coupon_used' => 0,
        ]);

        // 2. Dusk test
        $this->browse(function (Browser $browser) use ($customer, $product) {
            $browser->loginAs($customer)
                    ->visit('/review/product/' . $product->id)
                    ->assertSee('Create your review');

            $browser->script("document.getElementById('5-star-rating').checked = true;");

            $browser->type('review', 'Awesome product! Totally worth it.')
                    ->screenshot('before-submit-review')
                    ->press('Submit Review')
                    ->assertSee('Your review has been created!')
                    ->assertSee('Awesome product! Totally worth it.')
                    ->screenshot('after-submit-review');
        });
    }
}
