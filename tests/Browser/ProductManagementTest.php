<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProductManagementTest extends DuskTestCase
{
    public function test_admin_can_manage_products()
    {
        $this->browse(function (Browser $browser) {

            // =========================
            // LOGIN ADMIN
            // =========================
            $browser->visit('/auth/login')
                ->type('email', 'admin@test.com')
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/admin/dashboard');

            // =========================
            // VIEW PRODUCT LIST
            // =========================
            $browser->visit('/product')
                ->assertSee('Product');

            // =========================
            // ADD PRODUCT
            // =========================
            $browser->visit('/product/add_product')
                ->type('product_name', 'Latte Coffee')
                ->type('orientation', 'Hot Drink')
                ->type('description', 'Fresh coffee')
                ->type('price', '12')
                ->type('stock', '50')
                ->type('discount', '0')
                ->attach('image', public_path('test.jpg'))
                ->press('Save')
                ->assertSee('success');

            // =========================
            // EDIT PRODUCT
            // =========================
            $browser->visit('/product')
                ->visit('/product/edit_product/1') // change ID if needed
                ->type('product_name', 'Latte Updated')
                ->type('price', '15')
                ->press('Update')
                ->assertSee('updated');
        });
    }
}
