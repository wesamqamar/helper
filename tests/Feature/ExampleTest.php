<?php

namespace Tests\Feature;


use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{

    /**
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
