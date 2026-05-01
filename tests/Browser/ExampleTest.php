<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    public function testBasicExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/login')
            ->type('email', 'admin@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/home');
        });
    }
}
