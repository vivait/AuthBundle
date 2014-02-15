<?php

	namespace Viva\AuthBundle\Command;

	use Viva\AuthBundle\Entity\Group;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class GenerateGroupsCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:role:generate')
				->setDescription('Creates the standard set of roles into the database');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$em = $this->getContainer()->get('doctrine')->getManager();
			$array = array(
				array('ROLE_USER','User'),
				array('ROLE_ADMIN','Administrator'),
				array('ROLE_SUPER_ADMIN','Super Administrator'),
			);

			foreach($array as $row) {
				$obj = new Group();
				$obj->setRole($row[0]);
				$obj->setName($row[1]);
				$em->persist($obj);
				$output->writeln(sprintf('%s (%s), ', $obj->getRole(), $obj->getName()));
			}
			$em->flush();

			$output->writeln(sprintf('Created %s built-in groups/roles, ', count($array)));
		}
	}