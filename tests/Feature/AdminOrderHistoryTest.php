<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Bank;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_order_history()
    {
        // --------------------
        // ADMIN USER
        // --------------------
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

        // --------------------
        // PRODUCT
        // --------------------
        $product = Product::create([
            'product_name' => 'Test Product',
            'orientation' => 'Test',
            'description' => 'Test desc',
            'price' => 100,
            'stock' => 10,
            'discount' => 0,
            'image' => 'default.png',
        ]);

        // --------------------
        // STATUS
        // --------------------
        $status = Status::create([
            'order_status' => 'completed',
            'style' => 'success',
        ]);

        // --------------------
        // PAYMENT (IMPORTANT FIX)
        // --------------------
        $payment = Payment::create([
            'payment_method' => 'online banking',
        ]);

        // --------------------
        // BANK (IMPORTANT FIX)
        // --------------------
        $bank = Bank::create([
            'bank_name' => 'Maybank',
            'account_number' => '1234567890',
            'logo' => 'default.png',
        ]);

        // --------------------
        // NOTE (IMPORTANT FIX)
        // --------------------
        $note = Note::create([
            'note' => 'Test note',
        ]);

        // --------------------
        // ORDER
        // --------------------
        $order = Order::create([
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'quantity' => 1,
            'address' => 'Bangi',
            'shipping_address' => 'Bangi',
            'total_price' => 100,
            'payment_id' => $payment->id,
            'bank_id' => $bank->id,
            'note_id' => $note->id,
            'status_id' => $status->id,
            'transaction_doc' => null,
            'is_done' => 1,
            'refusal_reason' => null,
            'coupon_used' => 0,
        ]);

        // --------------------
        // ACT AS ADMIN
        // --------------------
        $response = $this->actingAs($admin)
            ->get('/order/order_history');

        // --------------------
        // ASSERTIONS
        // --------------------
        $response->assertStatus(200);
        $response->assertSee('History Data');
    }
}
