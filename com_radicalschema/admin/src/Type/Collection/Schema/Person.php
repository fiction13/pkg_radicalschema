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

\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\FormFieldsAwareType;
use Joomla\Component\RadicalSchema\Administrator\Type\MicrodataType;

/**
 * Schema.org Person type for author / team member / profile pages.
 * Output as ProfilePage with Person as main entity.
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/profile-page
 *
 * @since  __DEPLOY_VERSION__
 */
class Person implements MicrodataType, FormFieldsAwareType
{
    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * Builds the ProfilePage / Person schema data.
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
        $name = $this->prepareString($item->title);

        if ($name === '')
        {
            return [];
        }

        $person = [
            '@type' => 'Person',
            'name'  => ValueHelper::prepareText($name, 110),
            'url'   => Uri::current(),
        ];

        if (!empty($item->description) && \is_scalar($item->description))
        {
            $person['description'] = ValueHelper::prepareText((string) $item->description, 5000);
        }

        if (!empty($item->image))
        {
            $person['image'] = ValueHelper::prepareLink($item->image);
        }

        foreach (['jobTitle', 'email', 'telephone'] as $key)
        {
            if (($value = $this->prepareString($item->{$key})) !== '')
            {
                $person[$key] = $value;
            }
        }

        if (($worksFor = $this->prepareString($item->worksFor)) !== '')
        {
            $person['worksFor'] = [
                '@type' => 'Organization',
                'name'  => $worksFor,
            ];
        }

        // Profiles in social networks
        $sameAs = [];

        foreach ((array) $item->sameAs as $row)
        {
            $url = $this->prepareString(((array) $row)['url'] ?? '');

            if ($url !== '')
            {
                $sameAs[] = $url;
            }
        }

        if ($sameAs)
        {
            $person['sameAs'] = array_values(array_unique($sameAs));
        }

        return [
            'uid'        => $this->uid,
            'priority'   => $priority,
            '@context'   => 'https://schema.org',
            '@type'      => 'ProfilePage',
            'mainEntity' => $person,
        ];
    }

    /**
     * Converts a value to a trimmed plain string.
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareString($value): string
    {
        return \is_scalar($value) ? trim(strip_tags((string) $value)) : '';
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
            'jobTitle'    => '',
            'worksFor'    => '',
            'email'       => '',
            'telephone'   => '',
            'sameAs'      => [],
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
            'sameAs' => [
                'type'   => 'subform',
                'min'    => 0,
                'max'    => 20,
                'fields' => [
                    'url' => [
                        'type'    => 'url',
                        'label'   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_TYPE_ORGANIZATION_SAMEAS_URL',
                        'attribs' => ['filter' => 'url'],
                    ],
                ],
            ],
        ];
    }
}
