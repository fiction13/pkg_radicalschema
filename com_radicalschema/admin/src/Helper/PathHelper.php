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

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

defined('_JEXEC') or die;

final class PathHelper
{
    /**
     * @var
     * @since __DEPLOY_VERSION__
     */
    protected static $instance;

    /**
     * A list of paths
     *
     * @var array
     * @since __DEPLOY_VERSION__
     */
    protected $_paths = array(
        'meta'         => [
            JPATH_ROOT . '/administrator/components/com_radicalschema/src/Type/Collection/Meta'
        ],
        'schema'       => [
            JPATH_ROOT . '/administrator/components/com_radicalschema/src/Type/Collection/Schema'
        ],
        'schema_extra' => [
            JPATH_ROOT . '/administrator/components/com_radicalschema/src/Type/Collection/Schema/Extra'
        ]
    );

    /**
     * @var array
     * @since __DEPLOY_VERSION__
     */
    protected $_collections = [];

    /**
     *
     * @return mixed|PathHelper
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getInstance()
    {
        if (is_null(static::$instance))
        {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Register a path and register classes for new path
     *
     * @param   string  $path            The path to register
     * @param   string  $collectionType  Collection type - schema, meta, extra
     *
     * @since __DEPLOY_VERSION__
     */
    public function register($path, $collectionType = 'schema')
    {
        if (!is_string($path))
        {
            return;
        }

        if (!isset($this->_paths[$collectionType]))
        {
            $this->_paths[$collectionType] = array();
        }

        $this->_paths[$collectionType][] = $path;
    }

    /**
     * Get all collected types
     *
     * @param $type  - schema, meta, extra
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function getTypes($type)
    {
        if (!isset($this->_collections[$type]))
        {
            $result = [];
            $paths  = $this->_paths[$type];

            if ($paths)
            {
                foreach ($paths as $path)
                {
                    if (file_exists($path))
                    {
                        $files = Folder::files($path, '.php');

                        if ($files)
                        {
                            foreach ($files as $file)
                            {
                                $result[] = lcfirst(File::stripExt($file));
                            }
                        }
                    }
                }

                $this->_collections[$type] = $result;
            }
        }

        return $this->_collections[$type];
    }
}
