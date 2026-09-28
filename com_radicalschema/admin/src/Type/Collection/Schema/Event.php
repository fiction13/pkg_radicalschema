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
 * Schema.org Event type (offline, online or mixed).
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/event
 *
 * @since  __DEPLOY_VERSION__
 */
class Event implements MicrodataType, FormFieldsAwareType
{
    /**
     * Attendance mode: offline.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const MODE_OFFLINE = 'OfflineEventAttendanceMode';

    /**
     * Attendance mode: online.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const MODE_ONLINE = 'OnlineEventAttendanceMode';

    /**
     * Attendance mode: mixed.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const MODE_MIXED = 'MixedEventAttendanceMode';

    /**
     * Event status values.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    public const STATUSES = [
        'EventScheduled'   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_EVENTSTATUS_SCHEDULED',
        'EventPostponed'   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_EVENTSTATUS_POSTPONED',
        'EventRescheduled' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_EVENTSTATUS_RESCHEDULED',
        'EventMovedOnline' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_EVENTSTATUS_MOVEDONLINE',
        'EventCancelled'   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_EVENTSTATUS_CANCELLED',
    ];

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * Builds the Event schema data.
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
        $mode = \in_array($item->attendanceMode, [self::MODE_OFFLINE, self::MODE_ONLINE, self::MODE_MIXED], true)
            ? $item->attendanceMode
            : self::MODE_OFFLINE;

        $data = [
            'uid'                 => $this->uid,
            'priority'            => $priority,
            '@context'            => 'https://schema.org',
            '@type'               => 'Event',
            'name'                => $item->title ? ValueHelper::prepareText((string) $item->title, 110) : '',
            'description'         => $item->description ? ValueHelper::prepareText((string) $item->description, 5000) : '',
            'startDate'           => $this->prepareDate($item->startDate),
            'eventAttendanceMode' => 'https://schema.org/' . $mode,
        ];

        if ($endDate = $this->prepareDate($item->endDate))
        {
            $data['endDate'] = $endDate;
        }

        if (\is_string($item->eventStatus) && isset(self::STATUSES[$item->eventStatus]))
        {
            $data['eventStatus'] = 'https://schema.org/' . $item->eventStatus;
        }

        // Image
        if (!empty($item->image))
        {
            $data['image'] = ValueHelper::prepareLink($item->image);
        }

        // Location: Place and/or VirtualLocation
        $locations = [];

        if ($mode !== self::MODE_ONLINE)
        {
            $place   = ['@type' => 'Place'];
            $name    = $this->prepareString($item->locationName);
            $address = $this->prepareString($item->locationAddress);

            if ($name !== '')
            {
                $place['name'] = $name;
            }

            if ($address !== '')
            {
                $place['address'] = [
                    '@type'         => 'PostalAddress',
                    'streetAddress' => $address,
                ];
            }

            if (\count($place) > 1)
            {
                $locations[] = $place;
            }
        }

        if ($mode !== self::MODE_OFFLINE && ($onlineUrl = $this->prepareString($item->onlineUrl)) !== '')
        {
            $locations[] = [
                '@type' => 'VirtualLocation',
                'url'   => ValueHelper::prepareLink($onlineUrl),
            ];
        }

        if ($locations)
        {
            $data['location'] = \count($locations) > 1 ? $locations : $locations[0];
        }

        // Organizer
        if (($organizer = $this->prepareString($item->organizer)) !== '')
        {
            $data['organizer'] = [
                '@type' => 'Organization',
                'name'  => $organizer,
            ];

            if (($organizerUrl = $this->prepareString($item->organizerUrl)) !== '')
            {
                $data['organizer']['url'] = ValueHelper::prepareLink($organizerUrl);
            }
        }

        // Performer
        if (($performer = $this->prepareString($item->performer)) !== '')
        {
            $data['performer'] = [
                '@type' => 'Person',
                'name'  => $performer,
            ];
        }

        // Offer (tickets)
        $price    = ValueHelper::prepareNumber($item->price);
        $currency = $this->prepareString($item->currency);

        if ($price !== null && $currency !== '')
        {
            $data['offers'] = [
                '@type'         => 'Offer',
                'url'           => Uri::current(),
                'price'         => $price,
                'priceCurrency' => $currency,
            ];

            $availability = $this->prepareString($item->availability);

            if ($availability !== '')
            {
                $data['offers']['availability'] = stripos($availability, 'http') === 0
                    ? $availability
                    : 'https://schema.org/' . preg_replace('/[^A-Za-z]/', '', $availability);
            }

            if ($validFrom = $this->prepareDate($item->validFrom))
            {
                $data['offers']['validFrom'] = $validFrom;
            }
        }

        return array_filter($data, static fn ($value) => $value !== '' && $value !== null);
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
     * Converts a date to ISO 8601.
     *
     * @param   mixed  $value  Raw date.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareDate($value): string
    {
        $value = $this->prepareString($value);

        return $value !== '' ? (string) ValueHelper::prepareDate($value) : '';
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
            'title'           => '',
            'description'     => '',
            'image'           => '',
            'startDate'       => '',
            'endDate'         => '',
            'eventStatus'     => '',
            'attendanceMode'  => '',
            'locationName'    => '',
            'locationAddress' => '',
            'onlineUrl'       => '',
            'organizer'       => '',
            'organizerUrl'    => '',
            'performer'       => '',
            'currency'        => '',
            'price'           => '',
            'availability'    => '',
            'validFrom'       => '',
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
            'eventStatus'     => [
                'type'    => 'list',
                'default' => 'EventScheduled',
                'options' => self::STATUSES,
            ],
            'attendanceMode'  => [
                'type'    => 'list',
                'default' => self::MODE_OFFLINE,
                'options' => [
                    self::MODE_OFFLINE => 'COM_RADICALSCHEMA_PARAM_SCHEMA_ATTENDANCEMODE_OFFLINE',
                    self::MODE_ONLINE  => 'COM_RADICALSCHEMA_PARAM_SCHEMA_ATTENDANCEMODE_ONLINE',
                    self::MODE_MIXED   => 'COM_RADICALSCHEMA_PARAM_SCHEMA_ATTENDANCEMODE_MIXED',
                ],
            ],
            'locationName'    => [
                'showon' => ['attendanceMode' => [self::MODE_OFFLINE, self::MODE_MIXED]],
            ],
            'locationAddress' => [
                'showon' => ['attendanceMode' => [self::MODE_OFFLINE, self::MODE_MIXED]],
            ],
            'onlineUrl'       => [
                'showon' => ['attendanceMode' => [self::MODE_ONLINE, self::MODE_MIXED]],
            ],
            'availability'    => [
                'addoptions' => ['InStock', 'SoldOut', 'PreOrder'],
            ],
        ];
    }
}
