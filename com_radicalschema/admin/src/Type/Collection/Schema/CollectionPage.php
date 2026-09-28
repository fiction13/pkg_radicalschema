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

/**
 * Schema.org CollectionPage type for category / listing pages.
 *
 * @source https://schema.org/CollectionPage
 *
 * @since  __DEPLOY_VERSION__
 */
class CollectionPage extends AbstractWebPage
{
    /**
     * Schema.org type of the page.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const PAGE_TYPE = 'CollectionPage';
}
