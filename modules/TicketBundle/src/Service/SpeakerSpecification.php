<?php

declare(strict_types=1);

namespace TicketBundle\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ProposalBundle\Entity\Proposal;

class SpeakerSpecification
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function isSatisfiedBy(User $user): bool
    {
       $selectedProposals = $this->em->getRepository(Proposal::class)->findBy(['user' => $user, 'selected' => true]);

       return count($selectedProposals) > 0;
    }
}