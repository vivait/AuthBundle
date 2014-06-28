<?php

	namespace Vivait\AuthBundle\Command;

	use Vivait\AuthBundle\Entity\User;
	use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class UserChangePasswordCommand extends ContainerAwareCommand {

		protected function configure() {
			$this
				->setName('auth:user:resetpassword')
				->setDescription('Reset a user password')
				->addArgument('username', InputArgument::REQUIRED, 'Username of the User?')
				->addArgument('password', InputArgument::OPTIONAL, 'New Password?');
		}


		protected function execute(InputInterface $input, OutputInterface $output) {
			$username   = $input->getArgument('username');
			$password   = $input->getArgument('password');

			#find user
			$user = $this->getContainer()->get('doctrine')
			             ->getRepository('VivaitAuthBundle:User')
			             ->findOneBy(array('username' => $username));

			if(!$user) {
				$output->writeln(sprintf("Error: Could not find user with username: %s", $username));
				return null;
			}

			if(!$password) {
				$alphabet = "abcdefghjkmnpqrstuwxyzABCDEFGHJKMNPQRSTUWXYZ23456789";
				$password = '';
				$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
				for ($i = 0; $i < 8; $i++) {
					$n = rand(0, $alphaLength);
					$password[$i] = $alphabet[$n];
				}
			}

			$factory = $this->getContainer()->get('security.encoder_factory');
			$encoder = $factory->getEncoder($this);

			$user->newSalt();
			$user->setPassword($encoder->encodePassword($password, $user->getSalt()));

			$em = $this->getContainer()->get('doctrine')->getManager();
			$em->persist($user);
			$em->flush();

			$output->writeln(sprintf("Password for: %s has been reset to: %s",
			                         $user->getUsername(),
			                         $password
			                 ));

		}
	}