<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test guest entry pages load cleanly.
     */
    public function test_login_and_public_tracking_pages_return_successful_response(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $trackingResponse = $this->get('/track');
        $trackingResponse->assertStatus(200);
    }
}
