<?php
/**
 * Joomla! Framework Status Application
 *
 * @copyright  Copyright (C) 2014 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Joomla\Status\Model;

/**
 * Default model class for the application
 *
 * @since  1.0
 */
class DashboardModel extends DefaultModel
{
	public function getItems()
	{
		// Parse installed.json to get the currently installed packages, should always be the latest version
		$packages  = array();
		$reports   = array();
		$installed = json_decode(file_get_contents(JPATH_ROOT . '/vendor/composer/installed.json'));

		// Loop through and extract the package name and version for all Joomla! Framework packages
		foreach ($installed as $package)
		{
			if (strpos($package->name, 'joomla') !== 0)
			{
				continue;
			}

			$packages[str_replace('joomla/', '', $package->name)] = ['version' => $package->version];
		}

		// Get the package data for each of our packages
		$db = $this->getDb();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__packages'));

		foreach ($packages as $name => $package)
		{
			$query->where(
				$db->quoteName('package') . ' = ' . $db->quote($name) . ' AND ' . $db->quoteName('version') . ' = ' . $db->quote($package['version']),
				'OR'
			);
		}

		$packs = $db->setQuery($query)->loadObjectList();

		// Loop through the packs and get the reports
		foreach ($packs as $pack)
		{
			$query->clear()
				->select('*')
				->from($db->quoteName('#__test_results'))
				->where($db->quoteName('package_id') . ' = ' . (int) $pack->id)
				->order('id DESC')
				->setLimit(1);

			$result = $db->setQuery($query)->loadObject();

			// If we didn't get any data, build a new object
			if (!$result)
			{
				$result = new \stdClass;
			}

			// Special handling for the display name in the grid
			if ($pack->package == 'di')
			{
				$result->displayName = 'DI';
			}
			elseif ($pack->package == 'github')
			{
				$result->displayName = 'GitHub';
			}
			elseif ($pack->package == 'http')
			{
				$result->displayName = 'HTTP';
			}
			elseif ($pack->package == 'ldap')
			{
				$result->displayName = 'LDAP';
			}
			elseif ($pack->package == 'linkedin')
			{
				$result->displayName = 'LinkedIn';
			}
			elseif ($pack->package == 'oauth1')
			{
				$result->displayName = 'OAuth1';
			}
			elseif ($pack->package == 'oauth2')
			{
				$result->displayName = 'OAuth2';
			}
			else
			{
				$result->displayName = ucfirst($pack->package);
			}

			$result->version = $pack->version;

			// For repos with -api appended, handle separately
			if (in_array($pack->package, ['facebook', 'github', 'google', 'linkedin', 'twitter']))
			{
				$result->repoName = $pack->package . '-api';
			}
			else
			{
				$result->repoName = $pack->package;
			}

			$reports[$pack->package] = $result;
		}

		// Sort the array
		ksort($reports);

		return $reports;
	}
}