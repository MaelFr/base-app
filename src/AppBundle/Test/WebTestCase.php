<?php

namespace AppBundle\Test;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as Base;

class WebTestCase extends Base
{
    private static $staticClient;
    /** @var Client $client */
    public $client;

    public static function setUpBeforeClass()
    {
        $baseUrl = getenv('TEST_BASE_URL');
        if (!$baseUrl) {
            static::fail('No TEST_BASE_URL environmental variable set in phpunit.xml.');
        }
        self::$staticClient = static::createClient([
            'base_uri' => $baseUrl,
            'http_errors' => false,
            'headers' => ['Cookie' => 'XDEBUG_SESSION=PHPSTORM'],
            'proxy' => '',
        ]);

        self::BootKernel();
    }

    protected function setUp()
    {
        $this->client = self::$staticClient;

        $this->purgeDatabase();
    }

    protected function tearDown()
    {

    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()->get($id);
    }

    protected function getParameter($id)
    {
        return self::$kernel->getContainer()->getParameter($id);
    }


    protected function createUser($username, $plainPassword = 'foo', $isAdmin = false)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@baseapp.com');
        $user->setPlainPassword($plainPassword);
        $user->setEnabled(true);
        if ($isAdmin) {
            $user->addRole('ROLE_ADMIN');
        }
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }
}