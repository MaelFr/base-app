<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Security\JwtTokenAuthenticator;
use AppBundle\Test\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{
    /**
     * @see TokenController::newTokenAction()
     */
    public function testPOSTCreateToken()
    {
        $this->createUser('myuser', 'iamapassword');

        $response = $this->client->post('/api/tokens', [
            'auth' => ['myuser', 'iamapassword'],
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'token');
    }

    /**
     * @see TokenController::newTokenAction()
     * @see JwtTokenAuthenticator::start()
     */
    public function testPOSTTokenInvalidCredentials()
    {
        $this->createUser('myuser', 'iamapassword');

        $response = $this->client->post('/api/tokens', [
            'auth' => ['myuser', 'iamanotherpassword'],
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Invalid credentials.');
    }
}