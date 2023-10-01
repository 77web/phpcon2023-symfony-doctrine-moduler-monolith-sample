<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ProposalBundle\Entity\Proposal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand("app:demo")]
class DemoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword($this->hasherFactory->getPasswordHasher($user1)->hash('password1'));
        $this->em->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword($this->hasherFactory->getPasswordHasher($user2)->hash('password2'));
        $this->em->persist($user2);

        $proposalByUser1 = new Proposal();
        $proposalByUser1->setUser($user1);
        $proposalByUser1->setTitle('test proposal');
        $proposalByUser1->setBody(implode(PHP_EOL, ['This is the first line of proposal.', 'To be continued to this second line.']));
        $this->em->persist($proposalByUser1);

        $this->em->flush();

        return self::SUCCESS;
    }
}