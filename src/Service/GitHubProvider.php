<?php
/**
 * Joomla! Framework Website
 *
 * @copyright  Copyright (C) 2014 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\FrameworkWebsite\Service;

use Joomla\DI\{
	Container, ServiceProviderInterface
};
use Joomla\Github\{
	Github, Http
};
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * GitHub service provider
 */
class GitHubProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
		$container->alias(Github::class, 'github')
			->share('github', [$this, 'getGithubService'], true);
	}

	/**
	 * Get the `github` service
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  Github
	 */
	public function getGithubService(Container $container) : Github
	{
		/** @var Registry $config */
		$config = $container->get('config');

		/** @var HttpFactory $factory */
		$factory = $container->get(HttpFactory::class);

		$options = $config->extract('github');

		$http = new Http($options, $factory->getAvailableDriver());

		return new Github($options, $http);
	}
}
