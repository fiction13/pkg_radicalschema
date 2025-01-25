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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class ParamsHelper
{
	/**
	 * Global Params.
	 *
	 * @var  Registry|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static ?Registry $_component = null;

	/**
	 * Menu item params.
	 *
	 * @var  Registry[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static ?array $_menu = null;

	/**
	 * Menu items ids.
	 *
	 * @var  int[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static ?array $_menuItems = null;

	/**
	 * Method to get component params.
	 *
	 * @return   Registry Component params.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getComponentParams(): Registry
	{
		if (self::$_component === null)
		{
			self::$_component = ComponentHelper::getParams('com_radicalschema');
		}

		return self::$_component;
	}

	/**
	 * Method to get menu params.
	 *
	 * @param   int|null     $pk     Item id.
	 * @param   string|null  $view   Item view
	 * @param   bool         $force  Force get params.
	 *
	 * @return   Registry  Menu item params on success, empty Registry on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getMenuParams(int $pk = null, string $view = null, bool $force = false): Registry
	{
		$pk = (int) $pk;
		if (empty($pk) || empty($view))
		{
			return new Registry();
		}

		if (self::$_menu === null)
		{
			self::$_menu = [];
		}

		if (self::$_menuItems === null)
		{
			self::$_menuItems = [];
		}

		$hash = $view . '_' . $pk;
		if (!isset(self::$_menu[$hash]) || $force)
		{
			$component_id = ComponentHelper::getComponent('com_radicalmart')->id;

			$db    = self::getDatabase();
			$query = $db->getQuery(true)
				->select(['m.id', 'm.params'])
				->from($db->quoteName('#__menu', 'm'))
				->where($db->quoteName('m.type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('m.component_id') . ' = :component_id')
				->whereIn('m.published', [1, 0])
				->bind(':component_id', $component_id, ParameterType::INTEGER);

			$conditionsOption = [
				$db->quoteName('link') . 'LIKE' . $db->quote('%option=com_radicalmart'),
				$db->quoteName('link') . 'LIKE' . $db->quote('%option=com_radicalmart&%'),
			];
			$query->extendWhere('AND', $conditionsOption, 'OR');

			$conditionsView = [
				$db->quoteName('link') . 'LIKE' . $db->quote('%view=' . $view),
				$db->quoteName('link') . 'LIKE' . $db->quote('%view=' . $view . '&%'),
			];
			$query->extendWhere('AND', $conditionsView, 'OR');

			$conditionsId = [
				$db->quoteName('link') . 'LIKE' . $db->quote('%id=' . $pk),
				$db->quoteName('link') . 'LIKE' . $db->quote('%id=' . $pk . '&%'),
			];
			$query->extendWhere('AND', $conditionsId, 'OR');

			$result = $db->setQuery($query, 0, 1)->loadObject();

			if (!empty($result) && !empty($result->id))
			{
				self::$_menu[$hash]      = new Registry($result->params);
				self::$_menuItems[$hash] = (int) $result->id;
			}
			else
			{
				self::$_menu[$hash]      = new Registry();
				self::$_menuItems[$hash] = 0;
			}
		}

		return self::$_menu[$hash];
	}

	/**
	 * Method to correct merge params.
	 *
	 * @param   array  $array  Merging Params array.
	 *
	 * @return Registry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function merge(array $array = []): Registry
	{
		$result = new Registry();

		foreach ($array as $params)
		{
			if (!$params instanceof Registry)
			{
				$params = new Registry($params);
			}

			foreach (array_keys($params->toArray()) as $path)
			{
				$value = $params->get($path);
				if (!$result->exists($path))
				{
					$result->set($path, $value);
				}
				elseif ((string)$value !== '')
				{
					$result->set($path, $value);
				}
			}
		}

		return $result;
	}
}