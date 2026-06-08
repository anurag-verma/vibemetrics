<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PASSWORD = 'Password1';

    public function test_registration_rejects_special_characters_in_name(): void
    {
        Notification::fake();

        $response = $this->from('/register')->post('/register', [
            'name' => 'John <script> Doe',
            'email' => 'bad-name@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    public function test_registration_accepts_single_word_name(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Madonna',
            'email' => 'single@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_registration_accepts_unicode_name(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'José García',
            'email' => 'unicode@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ]);

        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_registration_rejects_weak_password(): void
    {
        Notification::fake();

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'weak@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }
}
