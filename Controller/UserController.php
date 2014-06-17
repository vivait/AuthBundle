<?php

	namespace Vivait\AuthBundle\Controller;

	use Doctrine\ORM\EntityRepository;
	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Symfony\Component\HttpFoundation\Request;
	use Doctrine\ORM\Query;
    use Vivait\AuthBundle\Entity\User;

    class UserController extends Controller {

		public function indexAction() {
			################################################  SETTINGS  ################################################
			$twig = 'VivaitAuthBundle:Default:users.html.twig';
			############################################################################################################
			$db = $this->getDoctrine()
			           ->getRepository('VivaitAuthBundle:User')
			           ->findAllFull()
			           ->getResult();

			$params = array();
			return $this->render($twig, array('db' => $db, 'params' => $params));
		}

		public function editAction(Request $request) {

			################################################  SETTINGS  ################################################
			$name             = 'user';
			$repo             = 'VivaitAuthBundle:User';
			$formtpl['title'] = 'Add/Edit ' . ucfirst($name);
			$obj              = new User();
			$key              = $request->query->get('id', 0);
			$foreign_objs     = array( #	array(
			                           #		'repo'   => 'VivaBravoBundle:Product',
			                           #		'key'    => $request->query->get('pid', 0),
			                           #		'method' => 'setProduct',
			                           #		'name'   => 'product'),
			);
			############################################################################################################

			if(!$key) {
				### CREATING A NEW OBJECT ###

				#if there are foreign objects that should be bound to this object, bind them all here
				foreach($foreign_objs as $fo) {
					$foreign_obj = $this->getDoctrine()
					                    ->getRepository($fo['repo'])
					                    ->find($fo['key']);
					if(!$foreign_obj) {
						$this->get('session')->getFlashBag()->add('error', sprintf("Could not find the %s", $fo['name']));
						return $this->redirect($request->query->get('parent', $request->request->get('parent', $request->headers->get('referer'))));
					}
					call_user_func(array($obj, $fo['method'], $foreign_obj));
				}
			} else {
				### EDITING AN EXISTING OBJECT ###
				$obj = $this->getDoctrine()
				            ->getRepository($repo)
				            ->find($key);

				if(!$obj) {
					$this->get('session')->getFlashBag()->add('error', sprintf("Could not find the %s", $name));
				}
			}

			if($this->getUser()->getTenants()->count() > 1) {
				$tenant_namefield = 'tenantedFullname';
			} else {
				$tenant_namefield = 'fullname';
			}

			##############################################  CREATE FORM  ###############################################

			$form = $this->createFormBuilder($obj)
			             ->add('username', 'text', array('label' => 'Username'))
			             ->add('initials', 'text', array('label' => 'Initials'))
			             ->add('fullname', 'text', array('label' => 'Full Name'))
			             ->add('appstatus', 'checkbox', array('label' => 'Show App Status instead of Queue 4?'))
			             ->add('email', 'email', array('label' => 'Email Address'))
			             ->add('password', 'password', array('label' => 'New Password'))
			             ->add('active', 'checkbox', array('label' => 'Active'))
			             ->add('jobtitle', 'text', array('label' => 'Job Title', 'required' => false))
			             ->add('department', 'text', array('label' => 'Department', 'required' => false))
			             ->add('location', 'text', array('label' => 'Location', 'required' => false))
			             ->add('telephone', 'text', array('label' => 'Telephone', 'required' => false))
			             ->add('groups', 'entity', array(
					'class'    => 'VivaitAuthBundle:Group',
					'property' => 'name',
					'multiple' => true,
					'required' => true,
					'attr'     => array('size' => 15),
					'label'    => 'Groups'
				))
			             ->add('tenants', 'entity', array(
					'class'    => 'VivaitAuthBundle:Tenant',
					'property' => 'tenant',
					'multiple' => true,
					'attr'     => array('size' => 15),
					'required' => true,
					'label'    => 'Tenants'
				))
			             ->getForm();
			############################################################################################################

			if($request->isMethod('POST')) {
				// get a copy of the previous object before fields have been modified
				$prevobj = clone $obj;

				$form->handleRequest($request);
				if($form->isValid()) {

					######  RESET PASSWORD IF NEW PASSWORD IS SET ######
					if(strlen($obj->getPassword())) {
						# new password set
						$obj->newSalt();
						$factory  = $this->get('security.encoder_factory');
						$encoder  = $factory->getEncoder($this);
						$password = $encoder->encodePassword($obj->getPassword(), $obj->getSalt());
						$obj->setPassword($password);
					} else {
						#retain existing password
						$obj->setPassword($prevobj->getPassword());
					}
					####################################################


					$em = $this->getDoctrine()->getManager();
					$em->persist($obj);
					$em->flush();
					$this->get('session')->getFlashBag()->add('success', sprintf('The %s has been %s successfully', $name, $key ? 'modified' : 'created'));
					return $this->render('VivaitBootstrapBundle:Default:redirect.html.twig', array('redirect' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))));
				}
			}
			if(isset($form)) {
				$formtpl['form'] = $form->createView();
			}
			$formtpl['action'] = $this->generateUrl($this->container->get('request')->get('_route'), $request->query->all());

			return $this->render('VivaitBootstrapBundle:Default:form.html.twig', array(
				'form' => array_merge($formtpl, array('parent' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))))));
		}


		public function deleteAction(Request $request) {
			################################################  SETTINGS  ################################################
			$name         = 'user';
			$repo         = 'VivaitAuthBundle:User';
			$id           = $request->query->get('id', 0);
			$msg_notfound = "The $name could not be found";
			$msg_success  = "The $name has been removed";
			############################################################################################################

			$obj = $this->getDoctrine()
			            ->getRepository($repo)
			            ->find($id);

			if(!$obj) {
				$this->get('session')->getFlashBag()->add('error', $msg_notfound);
			} else {
				$em = $this->getDoctrine()->getManager();
				$em->remove($obj);
				$em->flush();
				$this->get('session')->getFlashBag()->add('success', $msg_success);
			}

			return $this->redirect($request->headers->get('referer'));
		}

		public function impersonateAction(Request $request) {
			################################################  SETTINGS  ################################################
			$repo = 'VivaitAuthBundle:User';
			$twig = 'VivaitAuthBundle:Partials:impersonateuser.html.twig';
			############################################################################################################
			$db = $this->getDoctrine()
			           ->getRepository($repo)
			           ->findAll();



			$params['parent'] = $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')));
			return $this->render($twig, array('db' => $db, 'params' => $params));
		}


//		public function twofactorAction(Request $request) {
//			################################################  SETTINGS  ################################################
//			$name             = 'User';
//			$repo             = 'VivaitAuthBundle:User';
//			$formtpl['title'] = '2-Factor Authentication';
//			$key              = $this->get('security.context')->getToken()->getUser();
//			############################################################################################################
//
//			$obj = $this->getDoctrine()
//				->getRepository($repo)
//				->find($key);
//
//			if(!$obj) {
//				$this->get('session')->getFlashBag()->add('error', sprintf("Could not find the %s", $name));
//			}
//
//
//			$form = $this->createFormBuilder();
//			if($obj->getTfkey()) {
//				$formtpl['content'] = '2-Factor authentication has been enabled for this account, to disable it click the button below.';
//				$form->add('disable', 'submit', array('label' => 'Disable'));
//			} else {
//				$formtpl['content'] = '2-Factor authentication protects your account by making sure that to access it, you need your username, password and your token generator (typically a mobile device)';
//				$form->add('enable', 'submit', array('label' => 'Enable'));
//			}
//			$form = $form->getForm();
//
//			if($request->isMethod('POST')) {
//				$form->bind($request);
//				if($form->isValid()) {
//
//					if($obj->getTfkey() && $form->get('disable')->isClicked()) {
//						$obj->setTfkey(null);
//					} else {
//
//						$bytes = openssl_random_pseudo_bytes(10);
//						$string   = bin2hex($bytes);
//						$this->get('session')->getFlashBag()->add('success', $string);
//						$obj->setTfkey($bytes);
//					}
//
//
//					$em = $this->getDoctrine()->getManager();
//					$em->persist($obj);
//					$em->flush();
//					$this->get('session')->getFlashBag()->add('success', sprintf('2-Factor authentication has been %s', $obj->getTfkey() ? 'enabled' : 'disabled'));
//					return $this->render('VivaitBootstrapBundle:Default:redirect.html.twig', array('redirect' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))));
//				}
//			}
//
//			if(isset($form)) {
//				$formtpl['form'] = $form->createView();
//			}
//			$formtpl['action'] = $this->generateUrl($this->container->get('request')->get('_route'), $request->query->all());
//			return $this->render('VivaitBootstrapBundle:Default:form.html.twig', array('form' => array_merge($formtpl, array('parent' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))))));
//
//
//		}
//
//		private static function base32_decode($b32) {
//			$lut = array("A" => 0, "B" => 1,
//			             "C" => 2, "D" => 3,
//			             "E" => 4, "F" => 5,
//			             "G" => 6, "H" => 7,
//			             "I" => 8, "J" => 9,
//			             "K" => 10, "L" => 11,
//			             "M" => 12, "N" => 13,
//			             "O" => 14, "P" => 15,
//			             "Q" => 16, "R" => 17,
//			             "S" => 18, "T" => 19,
//			             "U" => 20, "V" => 21,
//			             "W" => 22, "X" => 23,
//			             "Y" => 24, "Z" => 25,
//			             "2" => 26, "3" => 27,
//			             "4" => 28, "5" => 29,
//			             "6" => 30, "7" => 31
//			);
//
//			$b32    = strtoupper($b32);
//			$l      = strlen($b32);
//			$n      = 0;
//			$j      = 0;
//			$binary = "";
//
//			for($i = 0; $i < $l; $i++) {
//
//				$n = $n << 5;
//				$n = $n + $lut[$b32[$i]];
//				$j = $j + 5;
//
//				if($j >= 8) {
//					$j = $j - 8;
//					$binary .= chr(($n & (0xFF << $j)) >> $j);
//				}
//			}
//
//			return $binary;
//		}
	}
