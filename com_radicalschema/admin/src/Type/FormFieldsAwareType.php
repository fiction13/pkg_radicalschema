<?php

/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Type;

\defined('_JEXEC') or die;

/**
 * Type that describes its own form fields instead of plain mapping fields.
 *
 * Definition format (key = config key):
 *  - type     string  'list' | 'subform' | 'radicalschema_mapping' (default)
 *  - default  string  Default value
 *  - options  array   value => language constant (for list)
 *  - showon   array   configKey => [values]  (resolved to prefixed showon string)
 *  - fields   array   name => definition (for subform rows)
 *  - min/max  int     Rows limit (for subform)
 *
 * @since  __DEPLOY_VERSION__
 */
interface FormFieldsAwareType
{
    /**
     * Returns form field definitions keyed by config key.
     *
     * @return  array<string, array>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFormFields(): array;
}
