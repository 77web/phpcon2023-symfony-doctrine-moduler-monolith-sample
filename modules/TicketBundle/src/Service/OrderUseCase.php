<?php

declare(strict_types=1);

namespace TicketBundle\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use TicketBundle\Entity\Ticket;

class OrderUseCase
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly SpeakerSpecification $speakerSpecification,
        private readonly string $adminEmail,
    ) {
    }

    public function placeOrder(Ticket $ticket, User $user): void
    {
        $isSpeaker = $this->speakerSpecification->isSatisfiedBy($user);

        $messageToOrganizer = new TemplatedEmail();
        $messageToOrganizer
            ->textTemplate('@Ticket/email/order_notification.txt.twig')
            ->to($this->adminEmail)
            ->from($this->adminEmail)
            ->subject('Order placed')
            ->context(['user' => $user, 'ticket' => $ticket, 'price' => $isSpeaker ? 0 : $ticket->getPrice()])
        ;

        $messageToUser = new TemplatedEmail();
        $messageToUser
            ->textTemplate('@Ticket/email/order_thank_you.txt.twig')
            ->to($user->getEmail())
            ->from($this->adminEmail)
            ->subject('Thank you for your order')
            ->context(['user' => $user, 'ticket' => $ticket, 'price' => $isSpeaker ? 0 : $ticket->getPrice()])
        ;

        try {
            $this->mailer->send($messageToUser);
            $this->mailer->send($messageToOrganizer);
        } catch (TransportExceptionInterface $e) {
            throw new OrderFailedException(previous: $e);
        }
    }
}