<?php

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Test\ApiTestCase;

class MemberControllerTest extends ApiTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->createUser('myuser', 'iamapassword');
    }
    /**
     * @see MemberController::newAction()
     */
    public function testPOSTMemberWorks()
    {
        $data = [
            'name' => 'Peter',
            'address' => '42 Somewhere Street',
            'birthDate' => '2004-06-28',
        ];

        $response = $this->client->post('/api/members', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $finishedData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('name', $finishedData);
        $this->assertEquals('Peter', $finishedData['name']);
    }

    /**
     * @see MemberController::showAction()
     */
    public function testGETMember()
    {
        $this->createMember(['name' => 'Peter', 'address' => '42 Somewhere Street']);

        $response = $this->client->get('/api/members/Peter', [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'name',
            'address',
            'birthDate',
        ]);
    }

    /**
     * @see MemberController::listAction()
     */
    public function testGETMembersCollection()
    {
        $this->createMember(['name' => 'Peter', 'address' => '42 Somewhere Street']);
        $this->createMember(['name' => 'SpongeBob', 'address' => 'Bikini Bottom']);

        $response = $this->client->get('/api/members', [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[1].name', 'SpongeBob');
    }

    /**
     * @see MemberController::listAction()
     */
    public function testGETMembersCollectionPaginated()
    {
        $this->createMember(['name' => 'willnotmatch', 'address' => 'Bikini Bottom']);
        for ($i=0; $i<25; $i++) {
            $this->createMember(['name' => 'Peter'.$i, 'address' => $i.' Somewhere Street']);
        }

        // page 1
        $response = $this->client->get('/api/members?filter=peter', [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].name', 'Peter5');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 25);
        $this->asserter()->assertResponsePropertyExists($response, 'links.next');
        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->client->get($nextLink, [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[5].name', 'Peter15');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        // last page
        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink, [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'items[4].name', 'Peter24');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 5);
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].name');
    }

    /**
     * @see MemberController::updateAction()
     */
    public function testPUTMember()
    {
        $member = $this->createMember(['name' => 'Peter', 'address' => '42 Somewhere Street']);

        $data = [
            'name' => 'Peter',
            'address' => '69 Paradise Avenue',
            'birthDate' => $member->getBirthDate()->format('Y-m-d'),
        ];
        $response = $this->client->put('/api/members/Peter', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'address', '69 Paradise Avenue');
        $this->asserter()->assertResponsePropertyEquals($response, 'birthDate', $member->getBirthDate()->format(DATE_ATOM));
    }

    /**
     * @see MemberController::updateAction()
     */
    public function testPATCHMember()
    {
        $this->createMember(['name' => 'Peter', 'address' => '42 Somewhere Street']);

        $data = ['address' => '69 Paradise Avenue'];
        $response = $this->client->patch('/api/members/Peter', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'name', 'Peter');
        $this->asserter()->assertResponsePropertyEquals($response, 'address', '69 Paradise Avenue');
    }

    /**
     * @see MemberController::deleteAction()
     */
    public function testDELETEMember()
    {
        $this->createMember(['name' => 'Peter', 'address' => '42 Somewhere Street']);

        $response = $this->client->delete('/api/members/Peter', [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @see MemberController::newAction()
     */
    public function testValidationErrors()
    {
        $data = [
            'address' => '42 Somewhere Street',
            'birthDate' => '2004-06-28',
        ];

        $response = $this->client->post('/api/members', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertiesExist($response, array(
            'type',
            'title',
            'errors',
        ));
        $this->asserter()->assertResponsePropertyExists($response, 'errors.name');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.name[0]', 'Please enter a clever name');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.address');
    }


    /**
     * @see MemberController::newAction()
     */
    public function testInvalidJson()
    {
        $invalidBody = <<<EOF
{
    "name": "Peter",
    "address" : "Bikini Bottom
    "birthDate": "2001-02-03"
}
EOF;

        $response = $this->client->post('/api/members', [
            'body' => $invalidBody,
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains($response, 'type', 'invalid_body_format');
    }

    /**
     * @see MemberController::showAction()
     */
    public function test404Exception()
    {
        $response = $this->client->get('/api/members/unknown', [
            'headers' => $this->getAuthorizedHeaders('myuser'),
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No member found with name "unknown"');
    }

    /**
     * @see MemberController::newAction()
     */
    public function testRequiresAuthentication()
    {
        $response = $this->client->post('/api/members', [
            'body' => '[]',
        ]);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testBadToken()
    {
        $response = $this->client->post('/api/members', [
            'body' => '[]',
            'headers' => [
                'Authorization' => 'Bearer WRONG',
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }
}