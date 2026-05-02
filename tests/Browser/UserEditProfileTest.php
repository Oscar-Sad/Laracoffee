<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserEditProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test regular user can edit their profile data.
     */
    public function test_user_can_edit_profile_data()
    {
        // 1. Setup required data
        Role::create(['role_name' => 'Admin']); // ID 1
        Role::create(['role_name' => 'Customer']); // ID 2
        
        $customer = User::create([
            'fullname' => 'Customer User',
            'username' => 'customer1',
            'email' => 'customer@test.com',
            'password' => bcrypt('1234'),
            'phone' => '0123456789',
            'gender' => 'M',
            'address' => 'Old Address',
            'image' => 'default.png',
            'role_id' => 2, // Customer role
            'coupon' => 0,
            'point' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($customer) {
            $browser->loginAs($customer)
                    ->visit('/profile/edit_profile')
                    ->assertSee('Profile Details')
                    ->assertInputValue('username', 'customer1')
                    ->assertInputValue('fullname', 'Customer User')
                    ->assertInputValue('address', 'Old Address')
                    ->assertInputValue('phone', '0123456789')
                    // Edit the fields
                    ->type('username', 'newcustomer')
                    ->type('fullname', 'New Customer Name')
                    ->type('phone', '0987654321')
                    ->type('address', 'New Customer Address 123')
                    ->screenshot('before-user-edit-profile')
                    ->press('Save Changes')
                    ->pause(1000) // Wait for redirect and flash message
                    ->assertPathIs('/home')
                    ->assertSee('Your profile has been updated!')
                    ->screenshot('user-edit-profile-success');
        });
    }
}
