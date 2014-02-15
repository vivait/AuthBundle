<?php

	namespace Viva\AuthBundle\Command;

	use Viva\AuthBundle\Entity\Tenant;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class GenerateTenantCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:tenant:add')
				->setDescription('Create a single tenant into the database')
				->addArgument('code', InputArgument::REQUIRED, 'Enter the short code of the tenant?')
				->addArgument('name', InputArgument::REQUIRED, 'Enter the name of the tenant?')
				->addArgument('date', InputArgument::OPTIONAL, 'When does their license expire?', '+1 month');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$code = $input->getArgument('code');
			$name = $input->getArgument('name');
			$date = $input->getArgument('date');

			$db = $this->getContainer()->get('doctrine')
				->getRepository('VivaitAuthBundle:Tenant')
				->findOneBy(array('code'=>$code));

			if($db) {
				$output->writeln(sprintf("There is already a tenant (%s) with code: %s",$db->getTenant(),$db->getCode()));
				return null;
			}

			$tenant = new Tenant();
			$tenant->setCode($code);
			$tenant->setTenant($name);
			$tenant->setLicenseduntil(new \DateTime($date));

			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($tenant);
			$em->flush();

			$output->writeln(sprintf("A new tenant has been created (%s: %s)", $tenant->getCode(),$tenant->getTenant()));
		}
	}