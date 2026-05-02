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

class ChangeOrderStatusTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin can change order status.
     */
    public function test_admin_can_approve_order()
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

        Status::create(['order_status' => 'approved', 'style' => 'success']); // ID 1
        Status::create(['order_status' => 'pending', 'style' => 'warning']); // ID 2

        Payment::create(['payment_method' => 'online banking']); // ID 1
        Bank::create(['bank_name' => 'Maybank', 'account_number' => '123', 'logo' => 'default.png']); // ID 1
        Note::create(['order_notes' => 'Note 1']); // ID 1
        Note::create(['order_notes' => 'Note 2']); // ID 2
        Note::create(['order_notes' => 'Note 3']); // ID 3
        Note::create(['order_notes' => 'Note 4']); // ID 4 - Used when approving Bank payments

        // Create a pending order with proof
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
            'status_id' => 2, // pending
            'transaction_doc' => 'real_proof.png', // not default image
            'is_done' => 0, 
            'refusal_reason' => null,
            'coupon_used' => 0,
        ]);

        // 2. Dusk test
        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/order/order_data')
                    ->assertSee('Order Data')
                    ->click('.order-detail-link') // Click the detail span
                    ->waitFor('#OrderDetailModal')
                    ->pause(1000)
                    ->screenshot('admin-order-modal')
                    ->press('approve')
                    ->waitFor('.swal2-confirm') // Wait for SweetAlert
                    ->click('.swal2-confirm') // Confirm action
                    ->pause(1000) // Give it time to submit and redirect
                    ->assertPathIs('/order/order_data')
                    ->assertSee('Order approved successfully!')
                    ->screenshot('admin-approve-success');
        });
    }
}
