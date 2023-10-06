<?php

declare(strict_types=1);

namespace lib\ProposalBundle\tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ProposalBundle\Entity\Proposal;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProposalControllerTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $client;
    private User $user;
    private Proposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('test');
        $this->user->setRoles(['ROLE_USER']);

        $this->proposal = new Proposal();
        $this->proposal->setTitle('test proposal');
        $this->proposal->setBody('this is test proposal from user1');

        $this->client = static::createClient();
        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $em->persist($this->user);
        $em->persist($this->proposal);
        $em->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/proposals');
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->assertTrue($crawler->filter('.proposal')->count() === 1);
    }

    public function testShow(): void
    {
        $crawler = $this->client->request('GET', '/proposal/'.$this->proposal->getId());
        $this->assertTrue($this->client->getResponse()->isOk());

        $this->assertTrue($crawler->filter('body:contains("test proposal")')->count() === 1);
    }

    public function testShowNotFound(): void
    {
        $this->client->request('GET', '/proposal/'.($this->proposal->getId() * 100));
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function testPost(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/proposal');
        $this->assertTrue($this->client->getResponse()->isOk());

        $form = $crawler->selectButton('Post')->form();
        $form->setValues(['title' => 'proposal2', 'body' => 'proposal2 body']);
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/proposal/'.($this->proposal->getId() + 1)));

        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);
        $this->assertCount(2, $em->getRepository(Proposal::class)->findAll());
    }

    public function testPostInvalid(): void
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/proposal');
        $this->assertTrue($this->client->getResponse()->isOk());

        $form = $crawler->selectButton('Post')->form();
        $form->setValues(['title' => '', 'body' => 'proposal2 body']); // title is required
        $crawler = $this->client->submit($form);
        $this->assertFalse($this->client->getResponse()->isRedirection());
        $this->assertTrue($crawler->filter('body:contains("This value should not be blank")')->count() === 1);

        $em = $this->client->getContainer()->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);
        $this->assertCount(1, $em->getRepository(Proposal::class)->findAll());
    }

    public function testPostNeedsLogin(): void
    {
        $this->client->request('GET', '/proposal');
        $this->assertTrue($this->client->getResponse()->isRedirect('http://localhost/login'));
    }
}