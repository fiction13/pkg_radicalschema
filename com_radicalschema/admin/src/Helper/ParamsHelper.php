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
    public static function getItemParams(Registry $params): Registry
    {
        $componentParams = self::getComponentParams();

        return self::merge([$componentParams, $params]);
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
        $result = new Registry();

        foreach ($array as $params)
        {
            // Prepare params
            if (!$params instanceof Registry)
            {
                $params = new Registry($params);
            }

            $result->merge($params, true);
        }

        return $result;
    }
}