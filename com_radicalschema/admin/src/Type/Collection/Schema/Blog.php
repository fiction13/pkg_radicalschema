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
use Joomla\Component\RadicalSchema\Administrator\Type\AbstractWebPage;

/**
 * Schema.org Blog type for the blog main page (list of posts).
 * Posts themselves use the BlogPosting type.
 *
 * @source https://schema.org/Blog
 *
 * @since  __DEPLOY_VERSION__
 */
class Blog extends AbstractWebPage
{
    /**
     * Schema.org type of the page.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const PAGE_TYPE = 'Blog';

    /**
     * Adds the organization from the basic settings as publisher.
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

        return $data;
    }
}
