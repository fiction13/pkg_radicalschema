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

use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;

/**
 * Base for Schema.org Article types (Article, NewsArticle, BlogPosting).
 * Author is a Person, publisher is the organization from the basic settings.
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/article
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractArticle implements MicrodataType, FormFieldsAwareType
{
    /**
     * Schema.org type of the article.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const ARTICLE_TYPE = 'Article';

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $uid = 'radicalschema.schema.page';

    /**
     * Builds the article schema data.
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
            'uid'              => $this->uid,
            'priority'         => $priority,
            '@context'         => 'https://schema.org',
            '@type'            => static::ARTICLE_TYPE,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => Uri::current(),
            ],
            'headline'         => $item->title ? ValueHelper::prepareText((string) $item->title, 110) : '',
            'description'      => $item->description ? ValueHelper::prepareText((string) $item->description, 5000) : '',
            'datePublished'    => $item->datePublished ? ValueHelper::prepareDate($item->datePublished) : '',
            'dateModified'     => $item->dateModified ? ValueHelper::prepareDate($item->dateModified) : '',
        ];

        // Image: main image + additional images (list of URLs), or only main image
        $gallery = [];

        foreach ((array) $item->gallery as $row)
        {
            $src = ((array) $row)['image'] ?? '';

            if (\is_scalar($src) && trim((string) $src) !== '')
            {
                $gallery[] = ValueHelper::prepareLink(trim((string) $src));
            }
        }

        $mainImage = !empty($item->image) && \is_scalar($item->image) ? ValueHelper::prepareLink($item->image) : '';

        if ($gallery)
        {
            // Google recommends up to 3 main images for articles
            $data['image'] = \array_slice(array_values(array_unique(array_filter(array_merge([$mainImage], $gallery)))), 0, 3);
        }
        elseif ($mainImage !== '')
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => $mainImage,
            ];
        }

        // Author
        if (!empty($item->author) && \is_scalar($item->author))
        {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => ValueHelper::prepareUser($item->author),
            ];

            if (!empty($item->authorUrl) && \is_scalar($item->authorUrl))
            {
                $data['author']['url'] = ValueHelper::prepareLink(trim((string) $item->authorUrl));
            }
        }

        // Publisher: organization from the basic settings
        $params = ParamsHelper::getComponentParams();
        $name   = (string) $params->get('schema_type_organization_title', '');

        if ($name !== '')
        {
            $data['publisher'] = [
                '@type' => 'Organization',
                'name'  => $name,
                'url'   => Uri::root(),
            ];

            if ($logo = $params->get('schema_type_organization_image'))
            {
                $data['publisher']['logo'] = [
                    '@type' => 'ImageObject',
                    'url'   => ValueHelper::prepareLink($logo),
                ];
            }
        }

        return array_filter($data, static fn ($value) => $value !== '' && $value !== null);
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
            'title'         => '',
            'description'   => '',
            'image'         => '',
            'datePublished' => '',
            'dateModified'  => '',
            'author'        => '',
            'authorUrl'     => '',
            'gallery'       => [],
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

    /**
     * Returns form field definitions for non-mapping fields.
     *
     * @return  array<string, array>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFormFields(): array
    {
        return [
            'gallery' => [
                'type'   => 'subform',
                'min'    => 0,
                'max'    => 2,
                'fields' => [
                    'image' => [
                        'type'  => 'media',
                        'label' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_IMAGE',
                    ],
                ],
            ],
        ];
    }
}
