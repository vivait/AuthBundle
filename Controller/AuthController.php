<?php

namespace Vivait\AuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

class AuthController extends Controller {

	public function loginAction() {
		$request = $this->getRequest();
		$session = $request->getSession();

		// get the login error if there is one
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		}
		else {
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		}

		return $this->render($this->get('vivait_auth.templates.login'), array(
			// last username entered by the user
			'last_username' => $session->get(SecurityContext::LAST_USERNAME),
			'error'         => $error,
		));
	}

	public function heartbeatAction(Request $request) {
		$em   = $this->getDoctrine()->getManager();
		$user = $this->get('security.context')->getToken()->getUser();

		$user->setLastResponse(new \DateTime());
		$user->setLastIP($request->getClientIp());

		$status = $request->query->get('status', 0);
		if ($status == 'active') {
			$user->setStatus(User::STATUS_ONLINE);
			$user->setLastActivity(new \DateTime());
		} elseif ($status == 'idle') {
			$user->setStatus(User::STATUS_AWAY);
		} else {
			$user->setStatus(0);
		}

		$em->persist($user);
		$em->flush();

		$response = new Response();
		$response->setContent('OK');
		$response->setStatusCode(200);
		$response->headers->set('Content-Type', 'text/html');
		return $response;
	}

	public function changepasswordAction(Request $request) {

		$user = $this->getUser();

		$defaultData = array('message' => 'Change Password');
		$form        = $this->createFormBuilder($defaultData)
			->add('oldpassword', 'password', array('label' => 'Old Password'))
			->add('newpassword1', 'password', array('label' => 'New Password'))
			->add('newpassword2', 'password', array('label' => 'Repeat New Password'))
			->getForm();

		if ($request->isMethod('POST')) {
			$form->bind($request);
			$data = $form->getData();

			$factory = $this->get('security.encoder_factory');
			$encoder = $factory->getEncoder($this);
			if ($encoder->encodePassword($data['oldpassword'], $user->getSalt()) == $user->getPassword()) {
				#old password verified
				if ((!$data['newpassword1']) || (strlen($data['newpassword1']) < 8)) {
					$form->get('newpassword1')->addError(new FormError('Your new password must be at least 8 letters!'));
				} elseif ($data['newpassword1'] == $data['newpassword2']) {
					#both new passwords match
					$user->newSalt();
					$user->setPassword($encoder->encodePassword($data['newpassword1'], $user->getSalt()));

					#persist
					$em = $this->getDoctrine()->getManager();
					$em->persist($user);
					$em->flush();
					$this->get('session')->getFlashBag()->add('success', 'Your password has been changed successfully!');

					return $this->render('VivaitBootstrapBundle:Default:redirect.html.twig', array('redirect' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))));
				} else {
					// send error about mismatch new passwords
					$form->get('newpassword1')->addError(new FormError('The two new passwords do not match!'));
					$form->get('newpassword2')->addError(new FormError('The two new passwords do not match!'));
				}
			} else {
				// send error about invalid old password
				$form->get('oldpassword')->addError(new FormError('The old password is incorrect!'));
			}
		}


		if (isset($form)) {
			$formtpl['form'] = $form->createView();
		}

		$formtpl['action'] = $this->generateUrl($this->container->get('request')->get('_route'), $request->query->all());
		$formtpl['title']  = 'Change Password';
		return $this->render('VivaitAuthBundle:Default:changepassword.html.twig', array('form' => array_merge($formtpl, array('parent' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))))));
	}

	public function changetenantAction(Request $request) {
		$user           = $this->getUser();
		$tenants        = $user->getTenants();
		$new_tenant     = $request->get('_tenant');
		$session        = $request->getSession();
		$current_tenant = $this->get('vivait_auth.tenant_manager')->getTenant();

		if ($new_tenant) {
			$session->getFlashBag()->add('success', sprintf('Tenant has been changed to %s', $current_tenant->getTenant()));
			// Redirect them
			return $this->render('VivaitBootstrapBundle:Default:redirect.html.twig', array('redirect' => $request->query->get('parent', $request->request->get('parent', $request->headers->get('referer')))));
		}

		return $this->render('VivaitAuthBundle:Default:changetenants.html.twig', array(
			'tenants' => $tenants
		));
	}
}
