<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Content\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\RadicalSchema\Administrator\Adapter\Adapter;
use Joomla\Component\RadicalSchema\Administrator\Helper\FormHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Traits\FormFieldsPluginsTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

class Content extends Adapter implements SubscriberInterface
{
	use FormFieldsPluginsTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = 'com_content';

	/**
	 * The table name.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $table = '#__content';

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return array_merge([
			'onContentPrepareForm'     => 'onContentPrepareForm',
			'onRadicalSchemaGetMapping' => 'onRadicalSchemaGetMapping'
		], parent::getSubscribedEvents());
	}

	/**
	 * Adds forms for override
	 *
	 * @param   mixed  $event  Event.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($event)
	{
		$form     = $event->getArgument(0);
		$data     = $event->getArgument(1);
		$app      = Factory::getApplication();
		$formName = $form->getName();

		if ($formName !== 'com_config.component' && $formName !== 'com_content.article')
		{
			return false;
		}

		// Load path
		Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');

		// Load language
		$app->getLanguage()->load('com_radicalschema', JPATH_ADMINISTRATOR);

		// Config
		if ($app->getInput()->get('component') === 'com_radicalschema')
		{
			// Load config
			$form->loadFile('com_config.component');

			// Set meta fields
			$this->setFormMetaFields($form, $this->_name . '_meta', 'radicalschema_mapping', ['showon' => 'content_meta_enable!:0']);

			// Set schema fields
			$this->setFormSchemaFields($form, '', $this->_name . '_schema', 'radicalschema_mapping', ['showon' => 'content_schema_enable!:0']);
		}

		// Article
		if ($formName === 'com_content.article' && $app->isClient('administrator'))
		{
			// Check and set meta fields
			if (RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
			{
				// Load config
				$form->loadFile('com_content.article');

				$this->setFormMetaFields($form, 'radicalschema_meta', 'radicalschema_mapping', ['useglobal' => '1']);
			}

			// Check and set schema fields
			if (RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
			{
				$form->loadFile('com_content.article');

				$data = new Registry($data);
				$type = $data->get('attribs.content_type', '');

				if (!$type)
				{
					$type = ParamsHelper::getComponentParams()->get('content_type');
				}

				$this->setFormSchemaFields($form, $type, 'radicalschema_schema', 'radicalschema_mapping', ['useglobal' => '1']);
			}
		}

		return true;
	}

	/**
	 * onRadicalSchemaGetMapping event
	 *
	 * @param   string  $plugin  .
	 *
	 * @return  array|false Array mapping if true
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onRadicalSchemaGetMapping(string $plugin)
	{
		if ($this->_name !== $plugin)
		{
			return false;
		}

		$fields           = [];
		$fields['core']   = $this->getItemFromDatabase();
		$fields['images'] = FormHelper::getFieldsForm('com_content', 'article', 'images');

		// Add text
		$fields['core']['text'] = '';

		// Fields
		$accessFields = [
			'text',
			'textarea',
			'list',
			'groupedlist',
			'select',
			'editor',
			'email',
			'integer',
			'number',
			'checkbox',
			'checkboxes',
			'tel'
		];

		$contentFields = FieldsHelper::getFields('com_content.article');

		if ($contentFields)
		{
			foreach ($contentFields as $field)
			{
				if (!in_array($field->type, $accessFields))
				{
					continue;
				}

				$fields['fields'][$field->label] = 'field.' . $field->name;
			}
		}

		// Unset multiple core params
		unset(
			$fields['core']['metadata'],
			$fields['core']['attribs'],
			$fields['core']['urls'],
		);

		return $fields;
	}

	/**
	 * OnRadicalSchemaProvider event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onRadicalSchemaProvider()
	{
		$input = Factory::getApplication()->getInput();

		if ($input->getCmd('option') !== 'com_content'
			|| $input->getCmd('view') !== 'article'
			|| !is_null($input->getCmd('task')))
		{
			return;
		}

		$item = $this->getItem();

		if ($item)
		{
			$this->setMicrodata($item, 0.7);
		}
	}
}