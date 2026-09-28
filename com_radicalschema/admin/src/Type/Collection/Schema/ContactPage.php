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

use Joomla\Component\RadicalSchema\Administrator\Type\AbstractWebPage;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;

/**
 * Schema.org ContactPage type. The organization from the basic settings is the main entity.
 *
 * @source https://schema.org/ContactPage
 *
 * @since  __DEPLOY_VERSION__
 */
class ContactPage extends AbstractWebPage
{
    /**
     * Schema.org type of the page.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const PAGE_TYPE = 'ContactPage';

    /**
     * Adds the organization from the basic settings as the main entity of the contact page.
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
        $params = ParamsHelper::getComponentParams();
        $name   = (string) $params->get('schema_type_organization_title', '');

        if ($name === '')
        {
            return $data;
        }

        $organization = [
            '@type' => 'Organization',
            'name'  => $name,
            'url'   => Uri::root(),
        ];

        $phone       = (string) $params->get('schema_type_organization_phone', '');
        $contactType = (string) $params->get('schema_type_organization_contact_type', '');

        if ($phone !== '')
        {
            $organization['contactPoint'] = [
                '@type'       => 'ContactPoint',
                'telephone'   => $phone,
                'contactType' => $contactType !== '' ? $contactType : 'customer support',
            ];
        }

        $data['mainEntity'] = $organization;

        return $data;
    }
}
