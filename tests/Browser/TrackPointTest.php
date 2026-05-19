<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\Order;
use App\Models\Status;
use App\Models\Bank;
use App\Models\Note;
use App\Models\Payment;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TrackPointTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test users can track loyalty points earned through transactions.
     */
    public function test_track_points_through_transaction()
    {
        // 1. Setup required data
        Role::create(['role_name' => 'Admin']); // ID 1
        Role::create(['role_name' => 'Customer']); // ID 2
        
        $admin = User::create([
            'fullname' => 'Admin User',
            'username' => 'admin1',
            'email' => 'admin@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Test Address',
            'image' => 'default.png',
            'role_id' => 1,
            'coupon' => 0,
            'point' => 0,
        ]);

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
            'point' => 10, // Initial point
        ]);

        $product = Product::create([ // ID 1
            'product_name' => 'Test Product',
            'orientation' => 'Test',
            'description' => 'Test description',
            'price' => 100,
            'stock' => 10,
            'discount' => 0,
            'image' => 'default.png',
        ]);

        Status::create(['order_status' => 'approved', 'style' => 'success']); // ID 1
        Status::create(['order_status' => 'pending', 'style' => 'warning']); // ID 2
        Status::create(['order_status' => 'rejected', 'style' => 'danger']); // ID 3
        Status::create(['order_status' => 'done', 'style' => 'success']); // ID 4

        Payment::create(['payment_method' => 'online banking']); // ID 1
        Bank::create(['bank_name' => 'Maybank', 'account_number' => '123', 'logo' => 'default.png']); // ID 1
        Note::create(['order_notes' => 'Note 1']); // ID 1
        Note::create(['order_notes' => 'Note 2']); // ID 2
        Note::create(['order_notes' => 'Note 3']); // ID 3
        Note::create(['order_notes' => 'Note 4']); // ID 4
        Note::create(['order_notes' => 'Note 5']); // ID 5 - Used by endOrder

        $order = Order::create([
            'product_id' => $product->id, // 1
            'user_id' => $customer->id,
            'quantity' => 2, // 2 units
            'address' => 'Test Address',
            'shipping_address' => 'Test Shipping',
            'total_price' => 200,
            'payment_id' => 1,
            'bank_id' => 1,
            'note_id' => 4, // Approved note id
            'status_id' => 1, // Currently approved
            'transaction_doc' => 'proof.png',
            'is_done' => 0, 
            'refusal_reason' => null,
            'coupon_used' => 0,
        ]);

        // Points logic:
        // Product ID 1 gives 3 points per quantity.
        // Quantity is 2. Points earned = 6.
        // Initial points = 10.
        // Total points should be 16.

        $this->browse(function (Browser $browser) use ($admin, $customer) {
            // Admin logs in and ends the order
            $browser->loginAs($admin)
                    ->visit('/order/order_data')
                    ->assertSee('Order Data')
                    ->click('.order-detail-link') // Open modal
                    ->waitFor('#OrderDetailModal')
                    ->pause(1000)
                    ->press('done') // Click the 'done' button in modal
                    ->waitFor('.swal2-confirm') // Wait for SweetAlert
                    ->click('.swal2-confirm') // Confirm ending order
                    ->pause(1000) // wait for redirect
                    ->assertPathIs('/order/order_history')
                    ->assertSee('Order has been ended by admin');

            // Customer logs in and checks points
            $browser->logout()
                    ->loginAs($customer)
                    ->visit('/point/user_point')
                    ->assertSee('Your Point')
                    ->assertSee('16') // 10 initial + (3 * 2 quantity)
                    ->screenshot('customer-earned-points-tracking');
        });
    }
}
