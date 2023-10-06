<?php

declare(strict_types=1);

namespace ProposalBundle\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ProposalBundle\Entity\Proposal;
use ProposalBundle\Form\ProposalFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProposalController extends AbstractController
{
    #[Route("/proposals", name: 'app_proposal_index')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('@Proposal/proposal/index.html.twig', [
            'proposals' => $em->getRepository(Proposal::class)->findAll(),
        ]);
    }

    #[Route("/proposal/{id}", name: 'app_proposal_show')]
    public function show(Proposal $proposal): Response
    {
        return $this->render('@Proposal/proposal/show.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    #[Route("/proposal", name: 'app_proposal_post')]
    #[IsGranted("ROLE_USER")]
    public function post(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        UserInterface $user,
    ): Response {
        $form = $formFactory->createNamed('', ProposalFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $proposal = $form->getData();
            assert($proposal instanceof Proposal);
            assert($user instanceof User);
            $proposal->setUser($user);
            $em->persist($proposal);
            $em->flush();

            return $this->redirectToRoute('app_proposal_show', ['id' => $proposal->getId()]);
        }

        return $this->render('@Proposal/proposal/form.html.twig', [
            'form' => $form,
        ]);
    }
}