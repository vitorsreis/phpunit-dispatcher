<?php

use PUD\Http;

class HttpTest extends PUDTestCase
{
    public function testInvalidUrlReturnsStructuredFailure()
    {
        $response = Http::get('not-a-valid-url');
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertNotSame('', $response['error']);
    }
}
