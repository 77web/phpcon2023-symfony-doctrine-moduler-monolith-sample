<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfileControllerTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $client;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('test');
        $this->user->setRoles(['ROLE_USER']);

        $this->client = static::createClient();
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($this->user);
        $em->flush();
    }

    public function test(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/profile');
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertTrue($crawler->filter('body:contains("test@example.com")')->count() === 1);
    }

    public function testNotAuthenticated(): void
    {
        $this->client->request('GET', '/profile');
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}