<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'id' => 1,
            'email' => 'nacho.suarez125553@hotmail.com',
            'password' => bcrypt('123456'),
        ]);

        Http::fake([
            env('API_AUTH_URL') . '/api/validate' => Http::response([
                'id' => $this->user->id,
                'email' => $this->user->email,
            ], 200),
        ]);
    }

    /** @test */
    public function puede_listar_los_posts()
    {
        Post::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/posts', [
            'Authorization' => 'Bearer fake-token-valido',
        ]);

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    /** @test */
    public function puede_crear_un_post()
    {
        $data = [
            'titulo' => 'Post de prueba',
            'contenido' => 'Contenido de prueba',
        ];

        $response = $this->postJson('/api/posts', $data, [
            'Authorization' => 'Bearer fake-token-valido',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['titulo' => 'Post de prueba']);
    }
}
