<?php

	namespace Vivait\AuthBundle\Command;


	use Vivait\AuthBundle\Entity\User;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class GenerateUserCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:user:add')
				->setDescription('Create a single user into the database')
				->addArgument('fullname', InputArgument::REQUIRED, 'Enter the name of the user?')
				->addArgument('password', InputArgument::REQUIRED, 'Enter the username of the user?')
				->addArgument('email', InputArgument::REQUIRED, 'Enter the email address of the user?')
				->addArgument('role', InputArgument::REQUIRED, 'Enter the role of the user?')
				->addArgument('tenant', InputArgument::REQUIRED, 'Enter the code of the tenant')
                ->addArgument('username', InputArgument::OPTIONAL, 'Enter the username of the user?');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$fullname   = ucwords($input->getArgument('fullname'));
			$password   = $input->getArgument('password');
			$email      = $input->getArgument('email');
			$role       = $input->getArgument('role');
			$tenantcode = $input->getArgument('tenant');
            $username   = $input->getArgument('username');
            $initials   = "";

            $names = explode(' ', $fullname);

            #create a user name if not specified
            if(!$username){
                $username = implode('', $names);
            }

            #create initials if not specified
            if(!$initials){
                foreach($names as $name){
                    $initials .= $name[0];
                }
            }


			#find user from username
			$user = $this->getContainer()->get('doctrine')
			              ->getRepository('VivaitAuthBundle:User')
			              ->findOneBy(array('username' => $username));


			if($user) {
				$output->writeln(sprintf("Error: There is already a user (%s) by this username: %s", $user->getFullname(), $user->getUsername()));
				return;
			}

			#find user from email address
			$user = $this->getContainer()->get('doctrine')
			              ->getRepository('VivaitAuthBundle:User')
			              ->findOneBy(array('email' => $email));


			if($user) {
				$output->writeln(sprintf("Error: There is already a user (%s) by this email address: %s", $user->getFullname(), $user->getEmail()));
				return;
			}

			#find group
			$group = $this->getContainer()->get('doctrine')
			              ->getRepository('VivaitAuthBundle:Group')
			              ->findOneBy(array('role' => $role));


			if(!$group) {
				$output->writeln(sprintf("Could not find a group with the role of %s", $role));
				return;
			}

			#find tenant
			$tenant = $this->getContainer()->get('doctrine')
			               ->getRepository('VivaitAuthBundle:Tenant')
			               ->findOneBy(array('code' => $tenantcode));

			if($tenantcode && !$tenant) {
				$output->writeln(sprintf("Error: Could not find tenant with code: %s", $tenantcode));
				return null;
			}


			$user = new User();
            $user->setFullname($fullname);
			$user->setUsername($username);
			$user->setActive(true);
			$user->setEmail($email);
			$user->setInitials($initials);
			$user->addGroup($group);

			if($tenant) {
				$user->addTenant($tenant);
			}

			$factory = $this->getContainer()->get('security.encoder_factory');
			$encoder = $factory->getEncoder($this);

			$user->newSalt();
			$user->setPassword($encoder->encodePassword($password, $user->getSalt()));

			#persist
			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($user);
			$em->flush();

			$output->writeln(sprintf("Success: User added successfully\n         Username: %s",$user->getUsername()));

			if($group) {
				$output->writeln(sprintf("            Group: %s", $group->getName()));
			}

			if($tenant) {
				$output->writeln(sprintf("           Tenant: %s", $tenant->getTenant()));
			}
		}
	}
