<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Bank;
use App\Models\Note;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserPurchaseProductTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test customer can purchase products.
     */
    public function test_customer_can_purchase_product(): void
    {
        // 1. Setup required data in the testing database
        Role::create(['role_name' => 'Admin']); // ID 1
        Role::create(['role_name' => 'Customer']); // ID 2

        $user = User::create([
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

        // OrderController hardcodes status_id 2 for new orders
        Status::create(['order_status' => 'approved', 'style' => 'success']);
        Status::create(['order_status' => 'pending', 'style' => 'warning']); // ID 2

        // OrderController uses payment_method = 1 for Bank, 2 for COD
        Payment::create(['payment_method' => 'online banking']); // ID 1
        Payment::create(['payment_method' => 'cod']); // ID 2

        // OrderController uses note_id 1 or 2 based on payment
        Note::create(['order_notes' => 'Note 1']); // ID 1
        Note::create(['order_notes' => 'Note 2']); // ID 2

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/order/make_order/' . $product->id)
                ->assertSee('Make Order')
                ->type('quantity', '1')
                ->pause(1000); // give time for any UI updates

            // Bypass the RajaOngkir API dependency by manually filling the hidden and select fields
            $browser->script([
                "document.getElementById('province').innerHTML = '<option value=\"1\" selected>Test Province</option>';",
                "document.getElementById('city').innerHTML = '<option value=\"1\" selected>Test City</option>';",
                "document.getElementById('city').removeAttribute('disabled');",
                "document.getElementById('shipping_address').value = 'Test City, Test Province';",
                "document.getElementById('total_price').value = '100';",
            ]);

            $browser->type('address', '123 Testing Avenue')
                ->radio('payment_method', '2') // 2 is COD
                ->screenshot('before-submit-order')
                ->press('Submit')
                ->assertPathIs('/order/order_data')
                ->assertSee('Orders has been created!')
                ->screenshot('order-success-page');
        });
    }
}
