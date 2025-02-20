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

use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PluginsHelper;

class MappingField extends GroupedlistField
{
    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    protected $params = [];

    /**
     * The form field type.
     *
     * @var  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'radicalschema_mapping';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $layout = 'components.radicalschema.field.mapping';

    /**
     * @var null|string
     *
     * @since __DEPLOY_VERSION__
     */
    private $plugin = null;

    /**
     * @var null|bool
     *
     * @since __DEPLOY_VERSION__
     */
    private $useglobal = null;

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if ($return = parent::setup($element, $value, $group))
        {
            $this->plugin    = isset($this->element['plugin']) ? (string) $this->element['plugin'] : false;
            $this->useglobal = isset($this->element['useglobal']) ? (bool) $this->element['useglobal'] : false;
        }

        return $return;
    }

    /**
     * Method to get the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    protected function getGroups()
    {
        $groups = $this->getOptions();
        $result = [];

        // First group
        $result[Text::_('COM_RADICALSCHEMA_GROUP_EXTRA')] = [
            [
                'text'  => Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT'),
                'value' => '_noselect_'
            ],
            [
                'text'  => Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_CUSTOM'),
                'value' => '_custom_'
            ]
        ];

        // Show global values
        if ($this->useglobal)
        {
            $componentParams = ParamsHelper::getComponentParams();
            $defaultValue    = $componentParams->get($this->fieldname, '');

            if ($defaultValue)
            {
                $defaultValues = explode('.', $defaultValue);
                $defaultValue  = end($defaultValues);
            }
            else
            {
                $defaultValue = Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT');
            }

            $result[Text::_('COM_RADICALSCHEMA_GROUP_EXTRA')][0]['text'] = Text::sprintf('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_DEFAULT', ucfirst($defaultValue));
        }

        // Other groups
        foreach ($groups as $groupKey => $group)
        {
            $groupLabel = Text::_('COM_RADICALSCHEMA_GROUP_' . strtoupper($groupKey));

            if (strpos($groupKey, '.') !== FALSE)
            {
                $keyArr = explode('.', $groupKey);
                $groupLabel = Text::_('COM_RADICALSCHEMA_GROUP_' . strtoupper($keyArr[0])) . ' - ' . ucfirst($keyArr[1]);
            }

            if (empty($group))
            {
                continue;
            }

            foreach ($group as $label => $option)
            {
                $tmp = [
                    'text'  => ucfirst(str_replace('_', ' ', $option ?: $label)),
                    'value' => ($groupKey !== 'core' ? $groupKey . '.' : '') . $label
                ];

                $result[$groupLabel][] = $tmp;
            }
        }

        // Add extra options to custom first group
        if ($addOptions = $this->getAttribute('addoptions'))
        {
            $extraArray = [];
            $addOptions = explode(';', $addOptions);

            foreach ($addOptions as $option)
            {
                $optionList = explode(':', $option);

                if (count($optionList) == 1)
                {
                    $tmp = [
                        'text'  => ucfirst($optionList[0]),
                        'value' => $optionList[0]
                    ];
                }
                else
                {
                    $tmp = [
                        'text'  => ucfirst($optionList[0]),
                        'value' => $optionList[1]
                    ];
                }

                $extraArray[Text::_('COM_RADICALSCHEMA_GROUP_EXTRA')][] = $tmp;
            }

            $result = array_merge($extraArray, $result);
        }

        return $result;
    }

    /**
     * @param   null  $item
     *
     * @return mixed
     *
     * @since __DEPLOY_VERSION__
     */
    public function getFields($item = null)
    {
        if (!$this->fields)
        {
            $this->fields = FieldsHelper::getFields('com_content.article', $item);
        }

        return $this->fields;
    }

    /**
     * Add fields to options
     *
     * @since __DEPLOY_VERSION__
     */
    public function getOptions()
    {
        // Trigger for `onRadicalSchemaRegisterTypes` event.
        return PluginsHelper::triggerPlugin('radicalschema', $this->plugin, 'onRadicalSchemaGetMapping', ['plugin' => $this->plugin]);
    }

    /**
     * Method to get the field input markup fora grouped list.
     * Multiselect is enabled by using the multiple attribute.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        // Get the field groups.
        $data['groups'] = (array) $this->getGroups();

        return $this->getRenderer($this->layout)->render($data);
    }
}