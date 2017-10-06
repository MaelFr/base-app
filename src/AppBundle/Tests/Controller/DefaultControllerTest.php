<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Controller\DefaultController;
use AppBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /** @see DefaultController::indexAction() */
    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains($this->getParameter('app_name'), $crawler->filter('main.container h1')->text());
    }

    public function testLoginPage()
    {
        $this->createUser('Peter');

        $crawler = $this->client->request('GET', '/login');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form input#username'));
        $this->assertCount(1, $crawler->filter('form input#password'));
        $this->assertCount(1, $crawler->filter('form button[type="submit"]'));

        $buttonCrawlerNode = $crawler->selectButton('_submit');
        $form = $buttonCrawlerNode->form([
            '_username' => 'Peter',
            '_password' => 'foo',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }
}
