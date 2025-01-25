<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Type\Collection\Schema;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\InterfaceType;

class NewsArticle implements InterfaceType
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

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

        $data = [
            'uid'              => $this->uid,
            'priority'         => $priority,
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'url'              => Uri::current(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => Uri::current()
            ],
            'headline'         => $item->title ? ValueHelper::prepareText($item->title, 110) : '',
            'articleBody'      => $item->description ? ValueHelper::prepareText($item->description, 5000) : '',
            'datePublished'    => $item->datePublished ? ValueHelper::prepareDate($item->datePublished) : '',
            'dateModified'     => $item->dateModified ? ValueHelper::prepareDate($item->dateModified) : '',
        ];

        // Author
        if ($item->author && !empty($item->author))
        {
            $data['publisher'] = [
                '@type' => 'Person',
                'name'  => ValueHelper::prepareUser($item->author)
            ];
        }

        // Image
        if (isset($item->image) && !empty($item->image))
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => ValueHelper::prepareLink($item->image)
            ];
        }

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
        $config = [
            'title'         => '',
            'description'   => '',
            'datePublished' => '',
            'dateModified'  => '',
            'author'        => '',
            'image'         => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}