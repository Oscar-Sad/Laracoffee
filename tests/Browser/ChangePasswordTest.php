<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ChangePasswordTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin can change their password.
     */
    public function test_admin_can_change_password()
    {
        // 1. Setup required data
        Role::create(['role_name' => 'Admin']); // ID 1
        
        $admin = User::create([
            'fullname' => 'Admin User',
            'username' => 'admin1',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Test Address',
            'image' => 'default.png',
            'role_id' => 1,
            'coupon' => 0,
            'point' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/profile/change_password')
                    ->assertSee('Current Password')
                    ->assertSee('New Password')
                    ->type('current_password', '1234')
                    ->type('password', '5678')
                    ->type('password_confirmation', '5678')
                    ->screenshot('before-change-password')
                    ->press('Change Password')
                    ->pause(1000) // Wait for redirect and flash message
                    ->assertPathIs('/home')
                    ->assertSee('Password has been updated')
                    ->screenshot('change-password-success');

            // Verify the new password actually works by attempting to log in
            $browser->logout()
                    ->visit('/auth/login')
                    ->type('email', 'admin@gmail.com')
                    ->type('password', '5678')
                    ->press('Login')
                    ->assertPathIs('/admin/dashboard');
        });
    }
}
