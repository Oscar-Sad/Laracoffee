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

class EditAndCancelOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setupCommonData()
    {
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

        Status::create(['order_status' => 'approved', 'style' => 'success']); // ID 1
        Status::create(['order_status' => 'pending', 'style' => 'warning']); // ID 2
        Status::create(['order_status' => 'rejected', 'style' => 'danger']); // ID 3
        Status::create(['order_status' => 'done', 'style' => 'success']); // ID 4
        Status::create(['order_status' => 'canceled', 'style' => 'danger']); // ID 5 - For Cancelled order

        Payment::create(['payment_method' => 'online banking']); // ID 1
        Bank::create(['bank_name' => 'Maybank', 'account_number' => '123', 'logo' => 'default.png']); // ID 1
        
        Note::create(['order_notes' => 'Note 1']); // ID 1
        Note::create(['order_notes' => 'Note 2']); // ID 2
        Note::create(['order_notes' => 'Note 3']); // ID 3
        Note::create(['order_notes' => 'Note 4']); // ID 4
        Note::create(['order_notes' => 'Note 5']); // ID 5
        Note::create(['order_notes' => 'Order canceled by user']); // ID 6 - Used by cancelOrder

        $order = Order::create([
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
            'transaction_doc' => env('IMAGE_PROOF', 'proof/default.png'), // fallback to string if env fails
            'is_done' => 0, 
            'refusal_reason' => null,
            'coupon_used' => 0,
        ]);

        return [$customer, $product, $order];
    }

    /**
     * Test customer can edit order.
     */
    public function test_customer_can_edit_order()
    {
        [$customer, $product, $order] = $this->setupCommonData();

        $this->browse(function (Browser $browser) use ($customer, $order) {
            $browser->loginAs($customer)
                    ->visit('/order/order_data')
                    ->assertSee('Order Data')
                    ->click('.order-detail-link') // Click the detail span
                    ->waitFor('#OrderDetailModal')
                    ->pause(1000)
                    ->click('#link_edit_order') // click the a tag, not the button inside
                    ->assertPathIs('/order/edit_order/' . $order->id)
                    ->type('address', 'Updated Address 123');

            // Bypass RajaOngkir API dependency for edit form
            $browser->script([
                "document.getElementById('province').innerHTML = '<option value=\"1\">Test Province</option>';",
                "document.getElementById('province').value = '1';",
                "document.getElementById('city').innerHTML = '<option value=\"1\">Test City</option>';",
                "document.getElementById('city').removeAttribute('disabled');",
                "document.getElementById('city').value = '1';",
                "document.getElementById('shipping_address').value = 'Test City, Test Province';",
                "document.getElementById('total_price').value = '100';",
            ]);

            $browser->screenshot('before-edit-submit')
                    ->press('Save Changes')
                    ->waitFor('.swal2-confirm') // Wait for SweetAlert
                    ->click('.swal2-confirm') // Confirm action
                    ->pause(1000) // Wait for redirect
                    ->assertPathIs('/order/order_data')
                    ->assertSee('Order has beed updated!') // Typo matches OrderController.php
                    ->screenshot('customer-edit-order-success');
        });
    }

    /**
     * Test customer can cancel order.
     */
    public function test_customer_can_cancel_order()
    {
        [$customer, $product, $order] = $this->setupCommonData();

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($customer)
                    ->visit('/order/order_data')
                    ->assertSee('Order Data')
                    ->click('.order-detail-link') // Click the detail span
                    ->waitFor('#OrderDetailModal')
                    ->pause(1000)
                    ->click('#button_cancel_order')
                    ->waitFor('.swal2-confirm') // Wait for SweetAlert
                    ->click('.swal2-confirm') // Confirm action
                    ->pause(1000) // Give it time to submit and redirect
                    ->assertPathIs('/order/order_data')
                    ->assertSee('Your order has been canceled!')
                    ->screenshot('customer-cancel-order-success');
        });
    }
}
