<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_homepage_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_has_an_accessible_password_visibility_toggle(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="password-visibility-toggle"', false)
            ->assertSee('aria-label="Tampilkan kata sandi"', false)
            ->assertSee('data-icon="hidden"', false)
            ->assertSee('password.type = isVisible ? \'text\' : \'password\';', false);
    }
}
