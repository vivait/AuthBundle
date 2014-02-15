<?php

	namespace Viva\AuthBundle\Command;

	use Viva\AuthBundle\Entity\Group;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class GenerateGroupCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:role:add')
				->setDescription('Create a single role into the database')
				->addArgument('code', InputArgument::REQUIRED, 'Enter the code of the role?')
				->addArgument('name', InputArgument::REQUIRED, 'Enter the name of the role?');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$code = $input->getArgument('code');
			$name = $input->getArgument('name');

			$db = $this->getContainer()->get('doctrine')
			           ->getRepository('VivaAuthBundle:Group')
			           ->findOneBy(array('role'=>$code));

			if($db) {
				$output->writeln(sprintf("There is already a role (%s) with code: %s",$db->getName(),$db->getRole()));
				return null;
			}

			$group = new Group();
			$group->setRole($code);
			$group->setName($name);

			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($group);
			$em->flush();

			$output->writeln(sprintf("Success: A new role has been created (%s: %s)", $group->getRole(),$group->getName()));
		}
	}