<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;
use AppBundle\Controller\Api\UserController;

class UserControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createUser('myuser', 'iamapassword');
        $this->createUser('myadmin', 'iamapassword', true);
    }

//    /**
//     * @see UserController::newAction()
//     */
//    public function testPOSTUserWorks()
//    {
//        $data = [
//            'username' => 'Peter',
//            'email' => 'peter@mail.com',
//            'enabled' => 'true',
//        ];
//
//        $response = $this->client->post('/api/users', [
//            'body' => json_encode($data),
//            'headers' => $this->getAuthorizedHeaders('myadmin'),
//        ]);
//
//        $this->assertEquals(201, $response->getStatusCode());
//        $this->assertTrue($response->hasHeader('Location'));
//        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'Peter');
//    }

    /**
     * @see UserController::showAction()
     */
    public function testGETUser()
    {
        $this->createUser('PeterPan');

        $response = $this->client->get('/api/users/PeterPan', [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'id',
            'username',
            'email',
            'password',
        ]);
    }

    /**
     * @see UserController::listAction()
     */
    public function testGETUsersCollection()
    {
        $this->createUser('PeterPan');
        $this->createUser('SpongeBob');

        $response = $this->client->get('/api/users', [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 4);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[3].username', 'SpongeBob');
    }

    /**
     * @see UserController::listAction()
     */
    public function testGETUsersCollectionPaginated()
    {
        $this->createUser('willnotmatch');
        for ($i=0; $i<25; $i++) {
            $this->createUser('Peter'.$i);
        }

        // page 1
        $response = $this->client->get('/api/users?filter=peter', [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].username', 'Peter5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, 'links.next');
        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, 'links.next');
        $response = $this->client->get($nextLink, [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].username', 'Peter15');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        // last page
        $lastLink = $this->asserter()->readResponseProperty($response, 'links.last');
        $response = $this->client->get($lastLink, [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].username', 'Peter24');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].username');
    }

    /**
     * @see UserController::updateAction()
     */
    public function testPUTUser()
    {
        $this->createUser('Peter');

        $data = [
            'username' => 'Peter',
            'email' => 'Jean@baseapp.com',
            'enabled' => true
        ];
        $response = $this->client->put('/api/users/Peter', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'Peter');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'Jean@baseapp.com');
        $this->asserter()->assertResponsePropertyEquals($response, 'enabled', true);
        $this->asserter()->assertResponsePropertyEquals($response, 'superAdmin', false);
    }

    /**
     * @see UserController::updateAction()
     */
    public function testPATCHUser()
    {
        $this->createUser('Peter');

        $data = ['email' => 'Jean@baseapp.com'];
        $response = $this->client->patch('/api/users/Peter', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'Peter');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'Jean@baseapp.com');
    }

    /**
     * @see UserController::updateAction()
     */
    public function testDisableUser()
    {
        $this->createUser('Peter');

        $data = ['enabled' => false];
        $response = $this->client->patch('/api/users/Peter', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'Peter');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'Peter@baseapp.com');
        $this->asserter()->assertResponsePropertyEquals($response, 'enabled', false);
    }

    /**
     * @see UserController::deleteAction()
     */
    public function testDELETEUser()
    {
        $this->createUser('Peter');

        $response = $this->client->delete('/api/users/Peter', [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());
    }

//    /**
//     * @see UserController::newAction()
//     */
//    public function testValidationErrors()
//    {
//        $data = [
//            'address' => '42 Somewhere Street',
//            'birthDate' => '2004-06-28',
//        ];
//
//        $response = $this->client->post('/api/users', [
//            'body' => json_encode($data),
//            'headers' => $this->getAuthorizedHeaders('myuser'),
//        ]);
//
//        $this->assertEquals(400, $response->getStatusCode());
//        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
//        $this->asserter()->assertResponsePropertiesExist($response, array(
//            'type',
//            'title',
//            'errors',
//        ));
//        $this->asserter()->assertResponsePropertyExists($response, 'errors.name');
//        $this->asserter()->assertResponsePropertyEquals($response, 'errors.name[0]', 'Please enter a clever name');
//        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.address');
//    }


    /**
     * @see UserController::newAction()
     */
    public function testInvalidJson()
    {
        $this->createUser('Peter');

        $invalidBody = <<<EOF
{
    "name": "Peter",
    "email" : "Jean@baseapp.com
}
EOF;

        $response = $this->client->put('/api/users/Peter', [
            'body' => $invalidBody,
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains($response, 'type', 'invalid_body_format');
    }

    /**
     * @see UserController::showAction()
     */
    public function test404Exception()
    {
        $response = $this->client->get('/api/users/unknown', [
            'headers' => $this->getAuthorizedHeaders('myadmin'),
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No user found with name "unknown"');
    }

    /**
     * @see UserController::updateAction()
     */
    public function testRequiresAuthentication()
    {
        $this->createUser('Peter');

        $response = $this->client->patch('/api/users/Peter', [
            'body' => '[]',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @see UserController::updateAction()
     */
    public function testForbiddenAccess()
    {
        $this->createUser('Peter');

        $response = $this->client->patch('/api/users/Peter', [
            'body' => '[]',
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Forbidden');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'You don\'t have permission to access this resource.');
    }

    public function testBadToken()
    {
        $this->createUser('Peter');

        $response = $this->client->patch('/api/users/Peter', [
            'body' => '[]',
            'headers' => [
                'Authorization' => 'Bearer WRONG',
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }
}