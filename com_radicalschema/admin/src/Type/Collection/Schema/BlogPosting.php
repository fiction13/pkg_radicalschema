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

use Joomla\Component\RadicalSchema\Administrator\Type\AbstractArticle;

/**
 * Schema.org BlogPosting type for blog posts.
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/article
 *
 * @since  __DEPLOY_VERSION__
 */
class BlogPosting extends AbstractArticle
{
    /**
     * Schema.org type of the article.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected const ARTICLE_TYPE = 'BlogPosting';
}
