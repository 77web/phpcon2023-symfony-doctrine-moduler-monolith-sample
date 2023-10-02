<?php

declare(strict_types=1);

namespace TicketBundle\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use TicketBundle\Entity\Ticket;
use TicketBundle\Service\OrderFailedException;
use TicketBundle\Service\OrderUseCase;

class TicketController extends AbstractController
{
    #[Route('/ticket/{id}', name: 'app_ticket_order')]
    #[IsGranted('ROLE_USER')]
    public function order(Request $request, Ticket $ticket, OrderUseCase $usecase, UserInterface $user): Response
    {
        if ($request->isMethod(Request::METHOD_POST) && $this->isCsrfTokenValid('_order_token', $request->request->get('_order_token'))) {
            try {
                assert($user instanceof User);
                $usecase->placeOrder($ticket, $user);
                return $this->redirectToRoute('app_ticket_order_succeeded', ['id' => $ticket->getId()]);
            } catch (OrderFailedException) {
                return $this->render('@Ticket/ticket/failed.html.twig');
            }
        }

        return $this->render('@Ticket/ticket/order.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/ticket/{id}/order_succeeded', name: 'app_ticket_order_succeeded')]
    #[IsGranted('ROLE_USER')]
    public function orderSucceeded(Ticket $ticket): Response
    {
        return $this->render('@Ticket/ticket/succeeded.html.twig');
    }
}