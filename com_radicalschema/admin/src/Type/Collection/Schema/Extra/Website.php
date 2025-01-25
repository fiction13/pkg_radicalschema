<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Type\Collection\Schema\Extra;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Type\InterfaceType;

class Website implements InterfaceType
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.website';

    /**
     * @param $item
     * @param $priority
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function execute($item, $priority)
    {
        if (is_array($item))
        {
            $item = (object) $item;
        }

        $data = [
            'uid'      => $this->uid,
            'priority' => $priority,
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'url'      => Uri::root(),
        ];

        return $data;
    }

    /**
     * Get config for JForm and Yootheme Pro elements
     *
     * @param   bool  $addUid
     *
     * @return string[]
     *
     * @since __DEPLOY_VERSION__
     */
    public function getConfig($addUid = true)
    {
        $config = [];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return array();
    }

}