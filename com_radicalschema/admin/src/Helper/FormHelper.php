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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper as CoreFormHelper;

defined('_JEXEC') or die;

class FormHelper
{

	/**
	 * @var array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected static $_form = array();

	/**
	 * Site items data.
	 *
	 * @var  array|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static ?array $_items = null;


	/**
	 * @param   string  $component  Component name
	 * @param   string  $name       View name
	 *
	 * @return Form
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getForm($component, $name)
	{
		if (!isset(self::$_form[$name]))
		{
			$_form = Factory::getContainer()->get(FormFactoryInterface::class)->createForm($name);
			$_form->loadFile(JPATH_ADMINISTRATOR . '/components/' . $component . '/forms/' . $name . '.xml');

			self::$_form[$name] = $_form;
		}

		return self::$_form[$name];
	}


	/**
	 * Method for get fields from fields group
	 *
	 * @param   string  $component  Component name
	 * @param           $group
	 * @param   string  $xml
	 *
	 * @return array|false
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getFieldsForm(string $component, string $xml, string $group)
	{
		$result = [];
		$form   = self::getForm($component, $xml);
		$fields = $form->getGroup($group);

		if (!$fields)
		{
			return false;
		}

		// Replace key
		foreach ($fields as $key => $field)
		{
			if ($field instanceof \Joomla\CMS\Form\Field\NoteField)
			{
				continue;
			}

			$key          = str_replace($group . '_', '', $key);
			$result[$key] = '';
		}

		return $result;
	}

	/**
	 * @param   string  $fieldName
	 * @param   null    $dependFieldName
	 * @param   null    $type
	 * @param   string  $default
	 * @param   null    $options
	 * @param   array   $addToName  - extra array of text, added to field label
	 *
	 * @return \SimpleXMLElement|false
	 *
	 * @since __DEPLOY_VERSION__
	 */
//	public static function createField(string $fieldName, $dependFieldName = null, $type = null, $default = '', $options = null, $addToName = array())
	public static function createField(string $fieldName, array $attribs = [], $options = null)
	{
		if (empty($fieldName))
		{
			return false;
		}

		CoreFormHelper::addFieldPrefix('\\Joomla\\Component\\RadicalSchema\\Field');
		CoreFormHelper::addFieldPrefix('\\Joomla\\Plugin\\RadicalSchema\\Content\\Field');

		// Set name
		$attribs['name']  = $fieldName;

		// Set type
		if (empty($attribs['type']))
		{
			$attribs['type'] = self::getFieldType($fieldName);
		}

		// Add label
		foreach (['_meta_', '_schema_'] as $fName)
		{
			if (strpos($fieldName, $fName) !== false)
			{
				$labelArray       = explode($fName, $fieldName);
				$attribs['label'] = ltrim($fName, '_') . $labelArray[1];
				break;
			}
		}

		if (empty($attribs['label']))
		{
			$attribs['label'] = $fieldName;
		}

		$attribs['label'] = Text::_('COM_RADICALSCHEMA_PARAM_' . strtoupper($attribs['label']));

		// Create simple xml element
		$element = new \SimpleXMLElement('<field />');

		if (!empty($attribs['add_to_label']))
		{
			$attribs['label'] .= ' (' . implode(', ', $attribs['add_to_label']) . ')';
			unset($attribs['add_to_label']);
		}

		// Add options
		if ($options)
		{
			foreach ($options as $key => $option)
			{
				if (is_array($option))
				{
					$group = $element->addChild('group', ucfirst($key));
					$group->addAttribute('label', ucfirst($key));

					foreach ($option as $label => $value)
					{
						if (!is_string($label))
						{
							$label = $value;
						}

						$group->addChild('option', ucfirst($label))->addAttribute('value', $value);
					}
				}
				else
				{
					$element->addChild('option', ucfirst($option))->addAttribute('value', $option);
				}

			}
		}

		// Add attribs
		foreach ($attribs as $attribName => $attribValue)
		{
			$element->addAttribute($attribName, $attribValue);
		}

		return $element;
	}

	/**
	 * @param $fieldName
	 *
	 * @return \SimpleXMLElement
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function createBoolField(string $fieldName, $default = 0)
	{
		$element = new \SimpleXMLElement('<field type="radio" />');

		// Add name
		$element->addAttribute('name', $fieldName);

		// Add type
		$element->addAttribute('label', Text::_('PLG_RADICALSCHEMA_PARAM_' . strtoupper($fieldName)));

		// Add default
		if ($default)
		{
			$element->addAttribute('default', $default);
		}

		// Add switcher layout
		$element->addAttribute('layout', 'joomla.form.field.radio.switcher');

		// Add options
		$element->addChild('option', Text::_('JNO'))->addAttribute('value', 0);
		$element->addChild('option', Text::_('JYES'))->addAttribute('value', 1);

		return $element;
	}

	/**
	 * @param $fieldName
	 *
	 * @return string|void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getFieldType($fieldName)
	{
		if (strpos($fieldName, 'image') !== false || strpos($fieldName, 'logo'))
		{
			return 'media';
		}

		if (strpos($fieldName, 'description') !== false)
		{
			return 'textarea';
		}

		if (strpos($fieldName, 'date') !== false)
		{
			return 'calendar';
		}

		return 'text';
	}
}