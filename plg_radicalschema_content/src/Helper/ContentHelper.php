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
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\FormHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\TypesHelper;
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
	 * @var array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $params = [];

	/**
	 * @var \Joomla\CMS\Application\CMSApplication
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * @var
	 * @since __DEPLOY_VERSION__
	 */
	protected static $instance;

	/**
	 * @var
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected static ?Registry $item = null;

	/**
	 * @var mixed
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $fields = null;

	/**
	 * @param   Registry  $params
	 *
	 * @throws \Exception
	 */
	public function __construct()
	{
		$this->params = ParamsHelper::getComponentParams();
		$this->app    = Factory::getApplication();
	}

	/**
	 *
	 * @return mixed|ContentHelper
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getInstance()
	{
		if (is_null(static::$instance))
		{
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Get Article
	 *
	 * @return Registry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getItem()
	{
		if (empty(self::$item))
		{
			$itemId = (int) Factory::getApplication()->getInput()->get('id', 0);

			// Get Article
			$model = Factory::getApplication()->bootComponent('com_content')->getMVCFactory()->createModel('Article', 'Site', ['ignore_request' => true]);
			$model->setState('params', ComponentHelper::getParams('com_content'));
			$item = $model->getItem($itemId);

			// Prepare content
			$item->images = (new Registry($item->images))->toArray();

			// Fix for content prepare plugins
			$item->text = $item->introtext . ' ' . $item->fulltext . '{emailcloak=off}';

			// Prepare fields
			$item->fields = $this->getFields($item);

			// Get attribs
			$item->attribs = (new Registry($item->attribs))->toArray();

			// Get params
			$item->params = (new Registry($item->params))->toArray();

			// Process the content plugins.
			PluginHelper::importPlugin('system');
			$item->text = HTMLHelper::_('content.prepare', $item->text, $item->params, 'com_content.article');

			self::$item = new Registry($item);
		}

		return self::$item;
	}

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
	public function getFields($item = null)
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