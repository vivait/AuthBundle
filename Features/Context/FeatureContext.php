<?php

namespace Vivait\AuthBundle\Features\Context;

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\Step;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityRepository;
use Nelmio\Alice\Loader\Base;
use Nelmio\Alice\Loader\Yaml;
use Nelmio\Alice\ORM\Doctrine;
use PHPUnit_Framework_Assert;
use Symfony\Component\BrowserKit\Cookie;
use Viva\AuthBundle\Entity\User;
use Viva\AuthBundle\Entity\UserRepository;
use Vivait\AuthBundle\Features\Context\AuthContextTrait;
use Vivait\BehatAliceLoader\Behat;
use Symfony\Component\Config\Definition\Exception\Exception;

//
// Require 3rd-party libraries here:
//
// require_once 'PHPUnit/Autoload.php';
// require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext {
	use KernelDictionary;
	use AuthContextTrait;


	/**
	 * @Given /^(.*) without redirection$/
	 */
	public function theRedirectionsAreIntercepted($step) {
		$this->canIntercept();
		$this->getSession()
			->getDriver()
			->getClient()
			->followRedirects(false);

		return new Step\Given($step);
	}

	/**
	 * @When /^I follow the redirection$/
	 * @Then /^I should be redirected$/
	 */
	public function iFollowTheRedirection() {
		$this->canIntercept();
		$client = $this->getSession()->getDriver()->getClient();
		$client->followRedirects(true);
		$client->followRedirect();
	}

	public function canIntercept() {
		$driver = $this->getSession()->getDriver();
		if (!$driver instanceof GoutteDriver) {
			throw new UnsupportedDriverActionException(
				'You need to tag the scenario with ' .
				'"@mink:goutte" or "@mink:symfony". ' .
				'Intercepting the redirections is not ' .
				'supported by %s', $driver
			);
		}
	}
}