<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Content\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\RadicalSchema\Administrator\Traits\FormFieldsPluginsTrait;
use Joomla\Registry\Registry;


/**
 * @package     pkg_radicalschema
 *
 * @since       __DEPLOY_VERSION__
 */
class ContentHelper
{
	use FormFieldsPluginsTrait;

	/**
	 * Check article page view
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function isArticleView()
	{
		$input = Factory::getApplication()->getInput();

		return $input->getCmd('option') === 'com_content'
			&& $input->getCmd('view') === 'article'
			&& is_null($input->getCmd('task'));
	}

	/**
	 * Check YOOtheme builder enabled in article
	 *
	 * @param $description
	 *
	 * @return bool
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function isYoothemeBuider($description)
	{
		if (strlen($description) == 0)
		{
			return false;
		}

		if (substr($description, 0, 4) === '<!--' && substr($description, -3) == '-->')
		{
			return true;
		}

		return false;
	}

	/**
	 * Get all article fields
	 *
	 * @param   null  $item
	 *
	 * @return mixed
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getFields($item = null)
	{
		$result = [];
		$fields = FieldsHelper::getFields('com_content.article', $item);

		foreach ($fields as $field)
		{
			$result[$field->name] = $field->value;
		}

		return $result;
	}
}