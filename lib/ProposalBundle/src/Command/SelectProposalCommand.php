<?php

declare(strict_types=1);

namespace ProposalBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use ProposalBundle\Entity\Proposal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand("proposal:select")]
class SelectProposalCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'an id of the selected proposal');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $proposal = $this->em->find(Proposal::class, $input->getArgument('id'));
        if ($proposal === null) {
            $output->writeln('No proposal found for id '.$input->getArgument('id'));

            return self::FAILURE;
        }

        $proposal->setSelected(true);
        $this->em->persist($proposal);
        $this->em->flush();

        return self::SUCCESS;
    }
}