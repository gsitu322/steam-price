<?php
namespace GS\SteamWeaponsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SteamWeaponsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('steamweapon:findprice')
            ->setDescription('Run though all weapons and find prices')
            ->addArgument(
                'collection',
                InputArgument::OPTIONAL,
                'Collection Name'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('collection');
        if ($name) {
            $text = 'Hello '.$name;
        } else {
            $text = 'Hello';
        }

        $output->writeln($text);
    }
}