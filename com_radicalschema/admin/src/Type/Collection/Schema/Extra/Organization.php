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
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\InterfaceType;

/**
 * @package     RadicalSchema\Types\Collections\Schema\Extra
 *
 * @source      https://developers.google.com/search/docs/advanced/structured-data/logo
 *
 * @since       __DEPLOY_VERSION__
 */
class Organization implements InterfaceType
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.organization';

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
            '@type'    => 'Organization',
            'url'      => Uri::root(),
            'logo'     => ValueHelper::prepareLink($item->image),
            'name'     => $item->title,
            'hasMap'   => $item->hasMap
        ];

        if ($item->addressCountry || $item->addressLocality || $item->addressRegion || $item->streetAddress || $item->postalCode || $item->postOfficeBoxNumber)
        {
            $data['address'] = [
                '@type' => 'PostalAddress'
            ];

            if ($item->addressCountry)
            {
                $data['address']['addressCountry'] = $item->addressCountry;
            }

            if ($item->addressLocality)
            {
                $data['address']['addressLocality'] = $item->addressLocality;
            }

            if ($item->addressRegion)
            {
                $data['address']['addressRegion'] = $item->addressRegion;
            }

            if ($item->streetAddress)
            {
                $data['address']['streetAddress'] = $item->streetAddress;
            }

            if ($item->postalCode)
            {
                $data['address']['postalCode'] = $item->postalCode;
            }

            if ($item->postOfficeBoxNumber)
            {
                $data['address']['postOfficeBoxNumber'] = $item->postOfficeBoxNumber;
            }
        }

        // Add contact point
        if ($item->phone || $item->contactType)
        {
            $data['contactPoint'] = [
                '@type' => 'ContactPoint'
            ];

            if ($item->phone)
            {
                $data['address']['telephone'] = $item->phone;
            }

            if ($item->contactType)
            {
                $data['address']['contactType'] = $item->contactType;
            }
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
            'image'               => '',
            'title'               => '',
            'addressCountry'      => '',
            'addressLocality'     => '',
            'addressRegion'       => '',
            'streetAddress'       => '',
            'postalCode'          => '',
            'postOfficeBoxNumber' => '',
            'hasMap'              => '',
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}