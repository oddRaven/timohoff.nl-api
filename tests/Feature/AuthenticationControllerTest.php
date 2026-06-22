<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_name(): void
    {
        User::factory()->create([
            'name' => 'Timo',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/authentication', [
            'name' => 'Timo',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Authentication succeeded.',
            ]);
    }

    public function test_login_without_name_is_rejected(): void
    {
        $response = $this->postJson('/api/authentication', [
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'name' => 'Timo',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/authentication', [
            'name' => 'Timo',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Authentication failed.',
            ]);
    }
}
