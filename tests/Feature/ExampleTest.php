<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz redirige al listado de órdenes (que a su vez exige autenticación).
     */
    public function test_the_application_redirects_root_to_orders(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('ordenes.index'));
    }
}
