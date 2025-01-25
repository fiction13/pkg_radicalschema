<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Field\Radicalschema;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PathHelper;

class TypesField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'radicalschema_types';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @throws  \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options = [];
		$types   = PathHelper::getInstance()->getTypes('schema');

		$tmp        = new \stdClass();
		$tmp->value = '';
		$tmp->text  = Text::_('COM_RADICALSCHEMA_FIELD_TYPE_SELECT');

		// Show global values
		if ($this->element['useglobal'])
		{
			$componentParams = ParamsHelper::getComponentParams();
			$defaultValue    = $componentParams->get($this->fieldname);

			if ($defaultValue)
			{
				$tmp->text = Text::sprintf('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_DEFAULT', ucfirst($defaultValue));
			}
		}

		$options[] = $tmp;

		foreach ($types as $type)
		{
			$option        = new \stdClass();
			$option->value = $type;
			$option->text  = ucfirst($type);
			$options[]     = $option;
		}

		return $options;
	}
}