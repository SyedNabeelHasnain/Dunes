<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_admin_can_authenticate_via_username_and_self_heal_corrupted_hash(): void
    {
        $admin = User::firstOrCreate(
            ['name' => 'admin'],
            ['email' => 'dunesdiscovery85@gmail.com', 'password' => 'initial']
        );
        $admin->password = 'corrupted_or_legacy_hash_xyz';
        $admin->save();

        $response = $this->post('/login', [
            'email' => 'admin',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));

        // Verify password hash in DB was healed
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('admin123', $admin->fresh()->password));
    }

    public function test_admin_can_self_heal_and_auto_create_when_table_empty(): void
    {
        User::query()->delete();
        $this->assertEquals(0, User::count());

        $response = $this->post('/login', [
            'email' => 'admin@dunesdiscoverytourism.com',
            'password' => 'admin123',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals(1, User::count());
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_seeder_seeds_valid_hash_and_resilient_accounts(): void
    {
        $this->seed(\Database\Seeders\UserSeeder::class);

        $admin = User::where('name', 'Admin')->orWhere('email', 'dunesdiscovery85@gmail.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('admin123', $admin->password));
    }

    public function test_token_mismatch_exception_redirects_to_login_with_status_message(): void
    {
        $request = \Illuminate\Http\Request::create('/admin', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $exception = new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.');

        $response = $this->app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $exception);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('login'), $response->headers->get('Location'));
    }
}
