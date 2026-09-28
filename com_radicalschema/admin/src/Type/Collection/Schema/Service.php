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
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\FormFieldsAwareType;
use Joomla\Component\RadicalSchema\Administrator\Type\MicrodataType;

/**
 * Schema.org Service type. One service per page, or several services
 * (added with "+") output as @graph.
 *
 * @source https://schema.org/Service
 *
 * @since  __DEPLOY_VERSION__
 */
class Service implements MicrodataType, FormFieldsAwareType
{
    /**
     * Area served: worldwide.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const AREA_WORLDWIDE = 'Worldwide';

    /**
     * Area served: custom list of regions / countries.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const AREA_CUSTOM = 'custom';

    /**
     * Area served: not used.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const AREA_NONE = 'none';

    /**
     * Languages for the availableLanguage select.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    public const LANGUAGES = [
        'Afrikaans', 'Albanian', 'Amharic', 'Arabic', 'Armenian', 'Azerbaijani', 'Basque', 'Belarusian',
        'Bengali', 'Bosnian', 'Bulgarian', 'Burmese', 'Catalan', 'Chinese', 'Croatian', 'Czech', 'Danish',
        'Dutch', 'English', 'Esperanto', 'Estonian', 'Filipino', 'Finnish', 'French', 'Galician', 'Georgian',
        'German', 'Greek', 'Gujarati', 'Hausa', 'Hebrew', 'Hindi', 'Hungarian', 'Icelandic', 'Igbo',
        'Indonesian', 'Irish', 'Italian', 'Japanese', 'Javanese', 'Kannada', 'Kazakh', 'Khmer', 'Korean',
        'Kurdish', 'Kyrgyz', 'Lao', 'Latin', 'Latvian', 'Lithuanian', 'Luxembourgish', 'Macedonian',
        'Malagasy', 'Malay', 'Malayalam', 'Maltese', 'Maori', 'Marathi', 'Mongolian', 'Nepali', 'Norwegian',
        'Pashto', 'Persian', 'Polish', 'Portuguese', 'Punjabi', 'Romanian', 'Russian', 'Serbian', 'Sinhala',
        'Slovak', 'Slovenian', 'Somali', 'Spanish', 'Swahili', 'Swedish', 'Tajik', 'Tamil', 'Tatar', 'Telugu',
        'Thai', 'Tibetan', 'Turkish', 'Turkmen', 'Ukrainian', 'Urdu', 'Uzbek', 'Vietnamese', 'Welsh',
        'Yiddish', 'Yoruba', 'Zulu',
    ];

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * Builds the Service schema data.
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

        // Common properties for all services of the page
        $common = [];

        if ($provider = $this->getProvider($item))
        {
            $common['provider'] = $provider;
        }

        if ($areaServed = $this->getAreaServed($item))
        {
            $common['areaServed'] = $areaServed;
        }

        if ($languages = $this->getLanguages($item))
        {
            $common['availableLanguage'] = $languages;
        }

        // Several services: one Service per row in @graph
        $services = [];

        foreach ((array) $item->services as $row)
        {
            $row  = (array) $row;
            $name = $this->prepareString($row['name'] ?? '');

            if ($name === '')
            {
                continue;
            }

            $service = [
                '@type'       => 'Service',
                'name'        => $name,
                'serviceType' => $this->prepareString($row['serviceType'] ?? '') ?: $name,
            ];

            if (($description = $this->prepareString($row['description'] ?? '')) !== '')
            {
                $service['description'] = ValueHelper::prepareText($description, 5000);
            }

            $services[] = $service + $common;
        }

        if ($services)
        {
            return [
                'uid'      => $this->uid,
                'priority' => $priority,
                '@context' => 'https://schema.org',
                '@graph'   => $services,
            ];
        }

        // One service for the page
        $data = [
            'uid'         => $this->uid,
            'priority'    => $priority,
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'name'        => $item->title ? ValueHelper::prepareText((string) $item->title, 110) : '',
            'description' => $item->description ? ValueHelper::prepareText((string) $item->description, 5000) : '',
            'url'         => Uri::current(),
        ];

        if (($serviceType = $this->prepareString($item->serviceType)) !== '')
        {
            $data['serviceType'] = $serviceType;
        }

        if (!empty($item->image) && \is_scalar($item->image))
        {
            $data['image'] = ValueHelper::prepareLink($item->image);
        }

        $data += $common;

        // Offer
        $price    = ValueHelper::prepareNumber($item->price);
        $currency = $this->prepareString($item->currency);

        if ($price !== null && $currency !== '')
        {
            $data['offers'] = [
                '@type'         => 'Offer',
                'url'           => Uri::current(),
                'priceCurrency' => $currency,
                'price'         => $price,
            ];
        }

        return array_filter($data, static fn ($value) => $value !== '' && $value !== null);
    }

    /**
     * Builds the provider: mapped name or organization name from the basic settings.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getProvider(object $item): ?array
    {
        $provider = $this->prepareString($item->provider);

        if ($provider === '')
        {
            $provider = (string) ParamsHelper::getComponentParams()->get('schema_type_organization_title', '');
        }

        if ($provider === '')
        {
            return null;
        }

        return [
            '@type' => 'Organization',
            'name'  => $provider,
            'url'   => Uri::root(),
        ];
    }

    /**
     * Builds areaServed: Worldwide or a list of regions / countries as Place.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getAreaServed(object $item): ?array
    {
        $type = $this->prepareString($item->areaServedType);

        if ($type === self::AREA_NONE)
        {
            return null;
        }

        if ($type === self::AREA_CUSTOM)
        {
            $names = $this->splitList($item->areaServed);
        }
        else
        {
            // Worldwide (default)
            $names = [self::AREA_WORLDWIDE];
        }

        $places = array_map(static fn ($name) => ['@type' => 'Place', 'name' => $name], $names);

        if (!$places)
        {
            return null;
        }

        return \count($places) > 1 ? $places : $places[0];
    }

    /**
     * Builds availableLanguage from the multiple select.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getLanguages(object $item): ?array
    {
        $names = [];

        foreach ((array) $item->availableLanguage as $language)
        {
            if (($language = $this->prepareString($language)) !== '')
            {
                $names[] = $language;
            }
        }

        $names = array_values(array_unique($names));
        $list  = array_map(static fn ($name) => ['@type' => 'Language', 'name' => $name], $names);

        if (!$list)
        {
            return null;
        }

        return \count($list) > 1 ? $list : $list[0];
    }

    /**
     * Splits a comma separated value into a list.
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function splitList($value): array
    {
        if (\is_array($value))
        {
            $value = implode(',', array_filter($value, 'is_scalar'));
        }

        if (!\is_scalar($value))
        {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', strip_tags((string) $value))))));
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
            'title'             => '',
            'description'       => '',
            'image'             => '',
            'serviceType'       => '',
            'provider'          => '',
            'areaServedType'    => '',
            'areaServed'        => '',
            'availableLanguage' => [],
            'currency'          => '',
            'price'             => '',
            'services'          => [],
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
            'areaServedType'    => [
                'type'    => 'list',
                'default' => self::AREA_WORLDWIDE,
                'options' => [
                    self::AREA_WORLDWIDE => 'COM_RADICALSCHEMA_PARAM_SCHEMA_AREASERVEDTYPE_WORLDWIDE',
                    self::AREA_CUSTOM    => 'COM_RADICALSCHEMA_PARAM_SCHEMA_AREASERVEDTYPE_CUSTOM',
                    self::AREA_NONE      => 'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT',
                ],
            ],
            'areaServed'        => [
                'showon' => ['areaServedType' => [self::AREA_CUSTOM]],
            ],
            'availableLanguage' => [
                'type'     => 'list',
                'multiple' => true,
                'options'  => array_combine(self::LANGUAGES, self::LANGUAGES),
            ],
            'services'          => [
                'type'   => 'subform',
                'layout' => 'joomla.form.field.subform.repeatable',
                'min'    => 0,
                'max'    => 30,
                'fields' => [
                    'name'        => [
                        'type'  => 'text',
                        'label' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_SERVICES_NAME',
                    ],
                    'serviceType' => [
                        'type'  => 'text',
                        'label' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_SERVICETYPE',
                    ],
                    'description' => [
                        'type'    => 'textarea',
                        'label'   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_DESCRIPTION',
                        'attribs' => ['rows' => '3'],
                    ],
                ],
            ],
        ];
    }
}
