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
use Joomla\Utilities\ArrayHelper;

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
     * Item Params.
     *
     * @var  Registry|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public static ?Registry $_item = null;

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
     * Method to get component params.
     *
     * @return   Registry Component params.
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getItemParams(Registry $params, string $plugin = ''): Registry
    {
        $componentParams = self::getComponentParams();

        if ($plugin !== '')
        {
            $componentParams = self::getTypeDefaults($plugin, (string) $params->get($plugin . '_type', ''));
        }

        return self::merge([$componentParams, $params]);
    }

    /**
     * Returns component params where the defaults of the given schema type
     * ("content_schema_product_price") are copied to the common keys ("content_schema_price").
     *
     * @param   string  $plugin  Plugin name (content, menu).
     * @param   string  $type    Schema type of the item, empty - type from the settings.
     *
     * @return  Registry
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getTypeDefaults(string $plugin, string $type = ''): Registry
    {
        $componentParams = self::getComponentParams();
        $type            = $type ?: (string) $componentParams->get($plugin . '_type', '');
        $result          = new Registry($componentParams->toArray());

        if ($type === '')
        {
            return $result;
        }

        $typePrefix = $plugin . '_schema_' . $type . '_';

        foreach ($componentParams->toArray() as $key => $value)
        {
            if (strpos($key, $typePrefix) !== 0 || $value === '' || $value === null || $value === [])
            {
                continue;
            }

            $result->set($plugin . '_schema_' . substr($key, \strlen($typePrefix)), $value);
        }

        return $result;
    }

    /**
     * Returns the default value of a schema field for a type from the settings.
     * Falls back to the common key used by previous versions.
     *
     * @param   string  $plugin   Plugin name (content, menu).
     * @param   string  $type     Schema type.
     * @param   string  $key      Config key of the type (price, offerType...).
     * @param   mixed   $default  Default value.
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getTypeParam(string $plugin, string $type, string $key, $default = null)
    {
        $params = self::getComponentParams();

        foreach ([$plugin . '_schema_' . $type . '_' . $key, $plugin . '_schema_' . $key] as $name)
        {
            $value = $params->get($name);

            if ($value !== null && $value !== '' && $value !== [])
            {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Method to correct merge params.
     *
     * @param   array  $array  Merging Params array.
     *
     * @return Registry
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function merge(array $array = []): Registry
    {
        $result    = new Registry();
        $overrides = [];

        foreach ($array as $params)
        {
            // Prepare params
            if (!$params instanceof Registry)
            {
                $params = new Registry($params);
            }

            $result->merge($params, true);

            // Repeatable values (subform rows) must replace global rows, not be merged by index
            foreach ($params->toArray() as $key => $value)
            {
                if (\is_array($value) && !empty($value) && self::isRows($value))
                {
                    $overrides[$key] = $value;
                }
            }
        }

        foreach ($overrides as $key => $value)
        {
            $result->set($key, ArrayHelper::toObject($value));
        }

        return $result;
    }

    /**
     * Checks whether a value looks like subform rows (list of arrays).
     *
     * @param   array  $value  Value to check.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected static function isRows(array $value): bool
    {
        foreach ($value as $row)
        {
            if (!\is_array($row))
            {
                return false;
            }
        }

        return true;
    }
}