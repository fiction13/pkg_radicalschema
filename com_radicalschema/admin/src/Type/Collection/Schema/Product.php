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

class Product implements InterfaceType
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
            'uid'         => $this->uid,
            'priority'    => $priority,
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $item->title ? ValueHelper::prepareText($item->title, 110) : '',
            'description' => $item->description ? ValueHelper::prepareText($item->description, 5000) : '',
        ];

        // Sku
        if (isset($item->sku))
        {
            $data['sku'] = $item->sku;
        }

        // MPN
        if (isset($item->mpn))
        {
            $data['sku'] = $item->sku;
        }

        // Brand
        if (isset($item->brand) && !empty($item->brand))
        {
            $data['brand'] = [
                '@type' => 'Brand',
                'name'  => $item->brand
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

        // Offer
        if (isset($item->price) && !empty($item->price) && isset($item->currency) && !empty($item->currency))
        {
            $data['offers'] = [
                '@type'         => 'Offer',
                'url'           => Uri::current(),
                'priceCurrency' => $item->currency,
                'price'         => (float) $item->price
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
            'title'       => '',
            'description' => '',
            'image'       => '',
            'sku'         => '',
            'mpn'         => '',
            'brand'       => '',
            'currency'    => '',
            'price'       => '',
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}