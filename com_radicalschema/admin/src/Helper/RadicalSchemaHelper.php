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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\OGHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\SchemaHelper;
use Joomla\Registry\Registry;

class RadicalSchemaHelper
{
	/**
	 * Method to build JSON-LD schema.org
	 *
	 * @param $body
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function buildSchema(&$body, $params)
	{
		$jsonLd = array();

		// Get data from tree
		$schemaData = SchemaHelper::getInstance()->getBuild('root');

		// Trigger event
//        EventHelper::beforeBuild((object) self::class, 'schema', $schemaData);

		foreach ($schemaData as $key => $schema)
		{
			if (RadicalSchemaHelper::checkSchema($params, $key, $body))
			{
				$jsonLd[] = "\n<script type=\"application/ld+json\">" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</script>";
			}
		}

		return implode("\n", $jsonLd);
	}

	/**
	 * Method to build opengraph metatags
	 *
	 * @param $body
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function buildOpengraph(&$body, $params)
	{
		$meta = [];

		// Get data from tree
		$metaData = OGHelper::getInstance()->getBuild('root');

		// Trigger event
//        EventHelper::beforeBuild((object) self::class, 'meta', $metaData);

		foreach ($metaData as $key => $og)
		{
			if ($key === 'radicalschema.meta.og')
			{
				$attribute = 'property';
			}
			else
			{
				$attribute = 'name';
			}

			foreach ($og as $property => $content)
			{
				if (!empty($content) && RadicalSchemaHelper::checkMeta($params, $property, $attribute, $body))
				{
					$meta[] = '<meta ' . $attribute . '="' . $property . '" content="' . $content . '" />';
				}
			}
		}

		return implode("\n", $meta);
	}

	/**
	 * Method to check enable schema type by plugin
	 *
	 * @param   string  $plugin  Plugin name
	 * @param   string  $type    Microdata type
	 *
	 * @return bool
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function checkEnable(string $plugin, string $type = ''): bool
	{
		$params = ParamsHelper::getComponentParams();

		if (empty($type))
		{
			return (int) $params->get($plugin . '_enable', false);
		}

		return (int) $params->get($plugin . '_' . $type . '_enable', false);
	}

	/**
	 * Get breadcrumbs
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getBreadCrumbs($home = true)
	{
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();
		$items   = $pathway->getPathWay();
		$menu    = $app->getMenu();
		$lang    = $app->getLanguage();
		$count   = count($items);

		// We don't use $items here as it references JPathway properties directly
		$crumbs = [];

		for ($i = 0; $i < $count; $i++)
		{
			// Skip null link and empty name properties
			if (is_null($items[$i]->link) || !$items[$i]->name)
			{
				continue;
			}

			$crumbs[$i]       = new \stdClass;
			$crumbs[$i]->name = trim(stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8')));
			$crumbs[$i]->link = Route::_($items[$i]->link, true, 0, true);
		}

		// Add home menu
		if ($home)
		{
			// Get home menu
			$home = Multilanguage::isEnabled() ? $menu->getDefault($lang->getTag()) : $menu->getDefault();

			$item       = new \stdClass;
			$item->name = htmlspecialchars(Text::_('PLG_SYSTEM_RADICALSCHEMA_BREADCRUMBS_HOME'));
			$item->link = ValueHelper::prepareLink(Route::_('index.php?Itemid=' . $home->id, true, 0, true));

			array_unshift($crumbs, $item);
		}

		// Fix last item's missing URL
		end($crumbs);
		if (empty($crumbs->link))
		{
			$crumbs[key($crumbs)]->link = Uri::current();
		}

		return $crumbs;
	}

	/**
	 * @param   Registry  $params
	 * @param   string    $name  - for example 'radicalschema.schema.article.9' for page
	 * @param   string    $body
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function checkSchema(Registry $params, string $name, $body)
	{
		// If empty name
		if (!$name)
		{
			return false;
		}

		$path = explode('.', $name);
		$type = $schemaType = $path[2];

		// Page schema type
		if (count($path) > 3)
		{
			$type = 'page';
		}

		// Check enable param
		if (!$params->get('schema_enable_type_' . $type, 1))
		{
			return false;
		}

		// If don't need to check current microdata
		if (!$params->get('extra_check_current'))
		{
			return true;
		}

		// We need check json-ld and microdata in the body

		// Check JSON-LD in the body
		if (strpos($body, '//schema.org/') !== false)
		{
			$regex = '/<script type="?application\/ld\+json"?>(.*?)<\/script>/msi';

			preg_match_all($regex, $body, $matches, PREG_SET_ORDER, 0);

			// Maybe no json-ld on page
			if ($matches)
			{
				foreach ($matches as $match)
				{
					// Check current schema type
					if (preg_match('/"@type"\s*:\s*"' . $schemaType . '"/si', $match[0]))
					{
						return false;
					}
				}
			}
		}

		// Check microdata
		if (strpos($body, 'itemtype') !== false)
		{
			$regex = '/(itemscope)? itemtype=(\'|")?http(s?):\/\/(www.)?schema.org\/' . $schemaType . '(\'|")?/msi';

			if (preg_match($regex, $body))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param   Registry  $params
	 * @param   string    $name  - for example 'og:image'
	 * @param   string    $attribute
	 * @param   string    $body
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function checkMeta(Registry $params, string $name, string $attribute, $body)
	{
		// If empty name
		if (!$name)
		{
			return false;
		}

		list($collection,) = explode(':', $name);

		// Check enable param
		if (!$params->get('meta_enable_' . $collection, 0))
		{
			return false;
		}

		// If don't need to check current microdata
		if (!$params->get('extra_check_current'))
		{
			return true;
		}

		// We need check meta tags in the body
		if (strpos($body, $attribute) !== false)
		{
			$regex = '/<meta.*' . $attribute . '="' . $name . '".*content="(.*)".*>/';

			if (preg_match($regex, $body))
			{
				return false;
			}
		}

		return true;
	}
}