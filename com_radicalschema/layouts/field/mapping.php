<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $groups         Groups of options available for this field.
 * @var   string  $dataAttribute  Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes Miscellaneous data attribute for eg, data-*
 */

$app    = Factory::getApplication();
$assets = $app->getDocument()->getWebAssetManager();
$assets->getRegistry()->addExtensionRegistryFile('com_radicalschema');
$assets->useScript('com_radicalschema.administrator.field.mapping');

$html = array();
$attr = '';

// Initialize some field attributes.
$attr .= !empty($class) ? ' class="form-select ' . $class . '"' : ' class="form-select"';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= $multiple ? ' multiple' : '';
$attr .= $required ? ' required' : '';
$attr .= $autofocus ? ' autofocus' : '';
$attr .= $dataAttribute;

// To avoid user's confusion, readonly="true" should imply disabled="true".
if ($disabled)
{
    $attr .= ' disabled="disabled"';
}

// Check custom
$isCustom = true;

if ($groups)
{
    foreach ($groups as $group)
    {
        $key = array_search($value, array_column($group, 'value'));

        if ($key !== false)
        {
            $isCustom = false;
            break;
        }
    }
}

$attr .= 'data-value="' . ($isCustom ? '_custom_' : $value) . '"';

// Create a regular list.
$html[] = '<div data-radicalschema-mapping-container>';
$html[] = HTMLHelper::_(
    'select.groupedlist',
    $groups,
    '',
    array(
        'list.attr'          => $attr, 'id' => $id, 'list.select' => $value, 'group.items' => null, 'option.key.toHtml' => false,
        'option.text.toHtml' => false,
    )
);
$html[] = '<input style="margin-top: 1rem;" type="' . ($isCustom ? 'text' : 'hidden') . '" class="form-control" name="' . $name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" placeholder="' . Text::_('PLG_RADICALSCHEMA_CONTENT_FIELD_CONTENT_PLACEHOLDER') . '">';
$html[] = '</div>';

echo implode($html);