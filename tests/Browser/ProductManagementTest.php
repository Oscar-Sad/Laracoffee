<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductManagementTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Product::where('product_name', 'LIKE', 'Dusk%')->delete();
    }

    private function adminUser(): User
    {
        return User::where('email', 'admin@gmail.com')->firstOrFail();
    }

    // =========================================================
    // TEST 1: Admin can ADD a product successfully
    // =========================================================
    public function test_admin_can_add_product()
    {
        // Unique name (max 25 chars) → re-runnable without hitting the unique constraint
        $productName = 'DuskAdd' . now()->format('His'); // max 13 chars

        $this->browse(function (Browser $browser) use ($productName) {

            // ── Ensure clean state, then log in as admin ───────
            $browser->logout();
            $browser->loginAs($this->adminUser());

            // ── Navigate to Add Product form ───────────────────
            $browser->visit('/product/add_product')
                ->assertSee('Add Product');

            // ── Fill every required field & attach image ───────
            $browser->type('product_name', $productName)
                ->type('stock',       '50')
                ->type('price',       '12')
                ->type('discount',    '10')
                ->type('orientation', 'Hot Drink')
                ->type('description', 'A fresh dusk test coffee')
                ->attach('image', public_path('storage/home/coffee.jpg'))
                ->press('Add Product');

            $browser->waitForLocation('/product')
                ->assertSee('Product has been added!')
                ->assertPresent('.alert-success')
                ->screenshot('add-product-success');
        });
    }

    // =========================================================
    // TEST 2: Admin can EDIT a product successfully
    // =========================================================
    public function test_admin_can_edit_product()
    {
        $originalName = 'DuskEditA' . now()->format('His'); // max 15 chars
        $updatedName  = 'DuskEditB' . now()->format('His'); // max 15 chars

        // Seed a known product via Eloquent so we have a reliable ID
        $product = Product::create([
            'product_name' => $originalName,
            'stock'        => 20,
            'price'        => 10,
            'discount'     => 5,
            'orientation'  => 'Cold Drink',
            'description'  => 'Seeded for dusk edit test',
            'image'        => env('IMAGE_PRODUCT'),
        ]);

        $this->browse(function (Browser $browser) use ($product, $updatedName) {

            // ── Ensure clean state, then log in as admin ───────
            $browser->logout();
            $browser->loginAs($this->adminUser());

            // ── Navigate to Edit Product form ──────────────────
            $browser->visit("/product/edit_product/{$product->id}")
                ->assertSee('Edit Product');

            // ── Set all required fields using value() to reliably overwrite ──
            // value() sets via JS so it always overwrites pre-filled content
            $browser->value('#product_name', $updatedName)
                ->value('#price',       '15')
                ->value('#stock',       '30')
                ->value('#discount',    '8')
                ->value('#orientation', 'Hot Drink Updated')
                ->value('#description', 'Updated by dusk test')
                ->press('Save Changes');

            // ── A SweetAlert2 "Are you sure?" dialog appears — confirm it ──
            $browser->waitFor('.swal2-confirm')
                ->press('Confirm');

            // ── After success the controller redirects to /product ──
            $browser->waitForLocation('/product')
                ->assertSee('Product has been updated!')
                ->assertPresent('.alert-success')
                ->screenshot('edit-product-success');
        });

        // Clean up the seeded product after the test
        $product->fresh()?->delete();
    }
}
