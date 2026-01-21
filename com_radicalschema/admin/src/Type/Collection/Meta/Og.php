<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Type\Collection\Meta;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\MicrodataType;

class Og implements MicrodataType
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.meta.og';

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
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data['uid']                    = $this->uid;
        $data['og:title']               = $item->title ? htmlspecialchars($item->title) : '';
        $data['og:description']         = $item->description ? ValueHelper::prepareText($item->description, 300) : '';
        $data['og:type']                = $item->type ?? '';
        $data['og:url']                 = Uri::current();
        $data['og:image']               = ValueHelper::prepareLink($item->image);
        $data['og:image:width']         = ValueHelper::getImageSize($item->image)->width;
        $data['og:image:height']        = ValueHelper::getImageSize($item->image)->height;
        $data['og:site_name']           = $item->site_name ?? '';
        $data['og:locale']              = $item->locale ?? '';
        $data['priority']               = $priority;

        return $data;
    }

    /**
     * Get config for Form and YOOtheme Pro elements
     *
     * @param   bool  $addUid
     *
     * @return string[]
     *
     * @since __DEPLOY_VERSION__
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'       => '',
            'description' => '',
            'image'       => '',
            'created'     => '',
            'modified'    => '',
            'type'        => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}