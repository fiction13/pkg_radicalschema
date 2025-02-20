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
     * Menu item params.
     *
     * @var  Registry[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public static ?array $_menu = null;

    /**
     * Menu items ids.
     *
     * @var  int[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public static ?array $_menuItems = null;

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
     * Method to merge params.
     *
     * @param   mixed          $params       Params array.
     * @param   Registry|null  $extraParams  Extra params
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public static function merge($params, Registry $extraParams = null): array
    {
        // Prepare params
        if (!$params instanceof Registry)
        {
            $params = new Registry($params);
        }

        $configParams = clone ParamsHelper::getComponentParams();;

        // Merge with custom params (etc. category)
        if ($extraParams)
        {
            $configParams->merge($extraParams, true);
        }

        return $configParams->merge($params, true)->toArray();
    }
}