<?php

declare(strict_types=1);

namespace TicketBundle\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TicketBundle\Entity\Ticket;
use Zenstruck\Foundry\Test\ResetDatabase;

class TicketControllerTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $client;
    private User $user;
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('test');
        $this->user->setRoles(['ROLE_USER']);

        $this->ticket = new Ticket();
        $this->ticket->setName('test ticket');
        $this->ticket->setPrice(3000);

        $this->client = static::createClient();
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($this->user);
        $em->persist($this->ticket);
        $em->flush();
    }

    public function testOrderForm(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/ticket/'.$this->ticket->getId());

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertTrue($crawler->filter('h1:contains("Order ticket: test ticket")')->count() === 1);
    }

    public function testOrderSucceeded(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/ticket/'.$this->ticket->getId());
        $this->assertTrue($this->client->getResponse()->isOk());

        $form = $crawler->selectButton('Order')->form();
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect(sprintf('/ticket/%d/order_succeeded', $this->ticket->getId())));

        $crawler2 = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertTrue($crawler2->filter('body:contains("Your order was sent to organizer")')->count() === 1);
    }

    public function testNoTicketFound(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/ticket/'.($this->ticket->getId() * 100));
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testNotAuthenticated(): void
    {
        $this->client->request('GET', '/ticket/'.$this->ticket->getId());
        $this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
    }
}