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
		$result = [];

		foreach ($array as $params)
		{
			if (!is_array($params))
			{
				$params = (new Registry($params))->toArray();
			}

			foreach (array_keys($params) as $path)
			{
				$value = $params[$path];
				if (!key_exists($path, $result))
				{
					$result[$path] = $value;
				}
				elseif (is_array($value) && count($result[$path]) > 0)
				{
					$result[$path] = $value;
				}
				elseif ((string) $value !== '')
				{
					$result[$path] = $value;
				}
			}
		}

		return new Registry($result);
	}
}