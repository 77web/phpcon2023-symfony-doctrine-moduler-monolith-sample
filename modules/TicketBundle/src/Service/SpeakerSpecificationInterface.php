<?php

declare(strict_types=1);

namespace TicketBundle\Service;

use App\Entity\User;

interface SpeakerSpecificationInterface
{
    public function isSatisfiedBy(User $user): bool;
}