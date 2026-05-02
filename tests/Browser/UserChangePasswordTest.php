<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserChangePasswordTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test regular user can change their password.
     */
    public function test_user_can_change_password()
    {
        // 1. Setup required data
        Role::create(['role_name' => 'Admin']); // ID 1
        Role::create(['role_name' => 'Customer']); // ID 2
        
        $customer = User::create([
            'fullname' => 'Customer User',
            'username' => 'customer1',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Test Address',
            'image' => 'default.png',
            'role_id' => 2, // Customer role
            'coupon' => 0,
            'point' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($customer)
                    ->visit('/profile/change_password')
                    ->assertSee('Current Password')
                    ->assertSee('New Password')
                    ->type('current_password', '1234')
                    ->type('password', '5678')
                    ->type('password_confirmation', '5678')
                    ->screenshot('before-user-change-password')
                    ->press('Change Password')
                    ->pause(1000) // Wait for redirect and flash message
                    ->assertPathIs('/home')
                    ->assertSee('Password has been updated')
                    ->screenshot('user-change-password-success');

            // Verify the new password actually works by attempting to log in
            $browser->logout()
                    ->visit('/auth/login')
                    ->type('email', 'customer@gmail.com')
                    ->type('password', '5678')
                    ->press('Login')
                    ->assertPathIs('/home')
                    ->assertSee('Login success');
        });
    }
}
