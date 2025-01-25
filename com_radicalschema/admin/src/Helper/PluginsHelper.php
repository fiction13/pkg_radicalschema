<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\DummyPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;

class PluginsHelper
{
	/**
	 * Plugins loaded.
	 *
	 * @var  array|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static ?array $_plugins = null;

	/**
	 * Plugins classes.
	 *
	 * @var  array|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static ?array $_classes = null;

	/**
	 * Plugins methods map.
	 *
	 * @var  array|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static ?array $_methods = null;

	/**
	 * Calls the plugins handler associated with the event.
	 *
	 * @param   string|array|null  $types  The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null        $event  The event name.
	 * @param   array  |null       $args   An array of arguments (optional).
	 *
	 * @throws \Exception
	 *
	 * @return array Plugin event result.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function triggerPlugins($types = null, ?string $event = null, ?array $args = []): array
	{
		$result = [];
		if (!is_array($types))
		{
			$types = [$types];
		}
		foreach ($types as $type)
		{
			if ($plugins = PluginHelper::getPlugin($type))
			{
				foreach ($plugins as $plugin)
				{
					$key = self::getPluginKey($type, $plugin->name);
					try
					{
						$result[$key] = self::triggerPlugin($type, $plugin->name, $event, $args);
					}
					catch (\Throwable $e)
					{
						if (JDEBUG || $e instanceof \RuntimeException)
						{
							throw $e;
						}

						$result[$key] = false;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Calls the plugin handler associated with the event.
	 *
	 * @param   string|null  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null  $plugin  The plugin name.
	 * @param   string|null  $event   The event name.
	 * @param   mixed        $args    Method arguments (optional).
	 *
	 * @throws \Exception
	 *
	 * @return mixed Plugin event result.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function triggerPlugin(string $type = null, string $plugin = null, string $event = null, $args = [])
	{
		// Load plugin
		self::loadPlugin($type, $plugin);

		// Check plugin enable
		if (!PluginHelper::isEnabled($type, $plugin))
		{
			return false;
		}

		// Get plugin
		$class = Factory::getApplication()->bootPlugin($plugin, $type);
		if ($class instanceof DummyPlugin)
		{
			return false;
		}

		// Find method
		$method = $event;
		if ($class instanceof SubscriberInterface)
		{
			$subscribedEvents = $class::getSubscribedEvents();

			$method = (isset($subscribedEvents[$event])) ? $subscribedEvents[$event] : false;
		}

		if (empty($method))
		{
			return false;
		}

		return (method_exists($class, $method)) ? call_user_func_array([$class, $method], $args) : false;
	}

	/**
	 * Method to load plugins.
	 *
	 * @param   string|null  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null  $plugin  The plugin name.
	 *
	 * @throws \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function loadPlugin(string $type = null, string $plugin = null)
	{
		if (empty($type) || empty($plugin))
		{
			return;
		}

		if (self::$_plugins === null)
		{
			self::$_plugins = [];
		}

		$key = self::getPluginKey($type, $plugin);

		if (!isset(self::$_plugins[$key]))
		{
			// Import plugin
			PluginHelper::importPlugin($type, $plugin);

			// Load language
			Factory::getApplication()->getLanguage()->load($key, JPATH_ADMINISTRATOR);
			if (isset(Factory::$language))
			{
				Factory::getLanguage()->load($key, JPATH_ADMINISTRATOR);
			}
		}
	}

	/**
	 * Get plugin class name.
	 *
	 * @param   string|null  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null  $plugin  The plugin name.
	 *
	 * @throws \ReflectionException|\Exception
	 *
	 * @return string|false Class na exist and has method, False if not.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getPluginClass(string $type = null, string $plugin = null)
	{
		if (empty($type) || empty($plugin))
		{
			return false;
		}

		// Get class
		if (self::$_classes === null)
		{
			self::$_classes = [];
		}

		$key = self::getPluginKey($type, $plugin);
		if (!isset(self::$_classes[$key]))
		{
			// Load plugin
			self::loadPlugin($type, $plugin);

			// Check plugin enable
			if (!PluginHelper::isEnabled($type, $plugin))
			{
				self::$_classes[$key] = false;

				return false;
			}

			// Get plugin
			$class = Factory::getApplication()->bootPlugin($plugin, $type);
			if ($class instanceof DummyPlugin)
			{
				self::$_classes[$key] = false;

				return false;
			}

			// Get class name
			$className = get_class($class);
			if (strpos($className, 'Joomla\\Plugin\\') !== false)
			{
				$className = '\\' . $className;
			}

			// Cache class name
			self::$_classes[$key] = $className;
		}

		return self::$_classes[$key];
	}

	/**
	 * Calls the plugin handler associated with the event.
	 *
	 * @param   string|null  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null  $plugin  The plugin name.
	 * @param   string|null  $event   The event name.
	 *
	 * @throws \ReflectionException|\Exception
	 *
	 * @return bool True if plugin exist and has method, False if not.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function checkPluginEvent(string $type = null, string $plugin = null, string $event = null): bool
	{
		if (empty($type) || empty($plugin) || empty($event))
		{
			return false;
		}

		if (self::$_methods === null)
		{
			self::$_methods = [];
		}

		$key = self::getPluginKey($type, $plugin);
		if (!isset(self::$_methods[$key]))
		{
			self::$_methods[$key] = [];
		}

		if (!isset(self::$_methods[$key][$event]))
		{
			// Load plugin
			self::loadPlugin($type, $plugin);

			// Check plugin enable
			if (!PluginHelper::isEnabled($type, $plugin))
			{
				self::$_classes[$key] = false;

				return false;
			}

			// Find plugin
			$class = Factory::getApplication()->bootPlugin($plugin, $type);
			if ($class instanceof DummyPlugin)
			{
				return false;
			}

			// Find methods
			$method = $event;
			if ($class instanceof SubscriberInterface)
			{
				$subscribedEvents = $class::getSubscribedEvents();
				if (isset($subscribedEvents[$event]))
				{
					$method = $subscribedEvents[$event];
				}
			}

			if (empty($method))
			{
				self::$_methods[$key][$method] = false;

				return false;
			}

			$result = method_exists($class, $method);

			self::$_methods[$key][$event] = $result;
			if ($event !== $method)
			{
				self::$_methods[$key][$method] = $result;
			}

			return $result;
		}

		return self::$_methods[$key][$event];
	}

	/**
	 * Get plugin cache key.
	 *
	 * @param   string|null  $type    The plugin type, relates to the subdirectory in the plugins directory.
	 * @param   string|null  $plugin  The plugin name.
	 *
	 * @return string Plugin cache key.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static function getPluginKey(string $type = null, string $plugin = null): string
	{
		return 'plg_' . $type . '_' . $plugin;
	}
}