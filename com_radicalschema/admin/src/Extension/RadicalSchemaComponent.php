<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

class RadicalSchemaComponent extends MVCComponent implements
	BootableExtensionInterface
{
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function boot(ContainerInterface $container)
	{
	}

	public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
	{
		return parent::getDispatcher($application);
	}
}
