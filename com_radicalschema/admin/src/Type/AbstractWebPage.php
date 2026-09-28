<?php

/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Type;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;

/**
 * Base for Schema.org WebPage types (WebPage, CollectionPage, ContactPage...).
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractWebPage implements MicrodataType
{
    /**
     * Schema.org type of the page.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const PAGE_TYPE = 'WebPage';

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $uid = 'radicalschema.schema.page';

    /**
     * Builds the page schema data.
     *
     * @param   object|array  $item      Resolved mapping values.
     * @param   float         $priority  Priority.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function execute($item, $priority)
    {
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data = [
            'uid'         => $this->uid,
            'priority'    => $priority,
            '@context'    => 'https://schema.org',
            '@type'       => static::PAGE_TYPE,
            'name'        => $item->title ? ValueHelper::prepareText((string) $item->title, 110) : '',
            'description' => $item->description ? ValueHelper::prepareText((string) $item->description, 5000) : '',
            'url'         => Uri::current(),
        ];

        if (!empty($item->image) && \is_scalar($item->image))
        {
            $data['image'] = ValueHelper::prepareLink($item->image);
        }

        // Link to the website
        $data['isPartOf'] = [
            '@type' => 'WebSite',
            'url'   => Uri::root(),
        ];

        try
        {
            $siteName = (string) Factory::getApplication()->get('sitename');
        }
        catch (\Throwable $e)
        {
            $siteName = '';
        }

        if ($siteName !== '')
        {
            $data['isPartOf']['name'] = $siteName;
        }

        $data = $this->extend($data, $item);

        return array_filter($data, static fn ($value) => $value !== '' && $value !== null);
    }

    /**
     * Adds type specific properties.
     *
     * @param   array   $data  Schema data.
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function extend(array $data, object $item): array
    {
        return $data;
    }

    /**
     * Get config for JForm and Yootheme Pro elements
     *
     * @param   bool  $addUid  Add uid key.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'       => '',
            'description' => '',
            'image'       => '',
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }
}
