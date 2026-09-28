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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\FormFieldsAwareType;
use Joomla\Component\RadicalSchema\Administrator\Type\MicrodataType;

/**
 * Schema.org FAQPage type. Questions and answers are collected from the page HTML
 * by CSS classes (e.g. <div class="faq-question">...</div><div class="faq-answer">...</div>).
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/faqpage
 *
 * @since  __DEPLOY_VERSION__
 */
class FaqPage implements MicrodataType, FormFieldsAwareType
{
    /**
     * Default CSS class of a question element.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const QUESTION_CLASS = 'faq-question';

    /**
     * Default CSS class of an answer element.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const ANSWER_CLASS = 'faq-answer';

    /**
     * HTML tags allowed by Google in the answer text.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private const ALLOWED_TAGS = '<h1><h2><h3><h4><h5><h6><br><ol><ul><li><a><p><div><b><strong><i><em>';

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * Builds the FAQPage schema data.
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
        $item          = (object) array_merge($this->getConfig(), (array) $item);
        $questionClass = $this->prepareClass($item->questionClass) ?: self::QUESTION_CLASS;
        $answerClass   = $this->prepareClass($item->answerClass) ?: self::ANSWER_CLASS;

        // Page HTML: the whole rendered page (article, YOOtheme builder, modules)
        try
        {
            $html = (string) Factory::getApplication()->getBody();
        }
        catch (\Throwable $e)
        {
            $html = '';
        }

        $questions = $this->parseQuestions($html, $questionClass, $answerClass);

        // FAQPage without questions is invalid
        if (empty($questions))
        {
            return [];
        }

        $data = [
            'uid'      => $this->uid,
            'priority' => $priority,
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'url'      => Uri::current(),
        ];

        if (!empty($item->title) && \is_scalar($item->title))
        {
            $data['name'] = ValueHelper::prepareText((string) $item->title, 110);
        }

        $data['mainEntity'] = $questions;

        return $data;
    }

    /**
     * Collects question/answer pairs from HTML by CSS classes.
     * Each answer is paired with the nearest preceding question.
     *
     * @param   string  $html           Page HTML.
     * @param   string  $questionClass  Question CSS class.
     * @param   string  $answerClass    Answer CSS class.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function parseQuestions(string $html, string $questionClass, string $answerClass): array
    {
        if ($html === '' || stripos($html, $questionClass) === false || stripos($html, $answerClass) === false)
        {
            return [];
        }

        $dom      = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($dom);
        $has   = static fn (string $class) => "contains(concat(' ', normalize-space(@class), ' '), ' " . $class . " ')";
        $nodes = $xpath->query('//body//*[' . $has($questionClass) . ' or ' . $has($answerClass) . ']');

        $result   = [];
        $question = null;

        foreach ($nodes as $node)
        {
            $classes = preg_split('/\s+/', trim((string) $node->getAttribute('class')));

            if (\in_array($questionClass, $classes, true))
            {
                $question = $this->cleanText($node->textContent);
                continue;
            }

            if ($question === null || $question === '')
            {
                continue;
            }

            $answer = $this->cleanAnswer($this->innerHtml($node));

            if ($answer !== '')
            {
                $result[] = [
                    '@type'          => 'Question',
                    'name'           => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $answer,
                    ],
                ];
            }

            $question = null;
        }

        return $result;
    }

    /**
     * Returns inner HTML of a node.
     *
     * @param   \DOMNode  $node  Node.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function innerHtml(\DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child)
        {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    /**
     * Cleans the answer HTML: removes scripts and not allowed tags, collapses whitespace.
     *
     * @param   string  $html  Answer HTML.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function cleanAnswer(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Remove attributes except href
        $html = preg_replace_callback('#<(\w+)(\s[^>]*)?>#', static function ($m) {
            if (strtolower($m[1]) === 'a' && preg_match('#href\s*=\s*("[^"]*"|\'[^\']*\')#i', $m[2] ?? '', $href))
            {
                return '<a href=' . $href[1] . '>';
            }

            return '<' . $m[1] . '>';
        }, $html);

        return $this->cleanText($html, false);
    }

    /**
     * Decodes entities and collapses whitespace.
     *
     * @param   string  $text       Text.
     * @param   bool    $stripTags  Strip all tags.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function cleanText(string $text, bool $stripTags = true): string
    {
        if ($stripTags)
        {
            $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        else
        {
            // Keep HTML entities like &lt; escaped in the answer HTML
            $text = str_replace(['&nbsp;', '&amp;'], [' ', '&'], $text);
        }
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Normalizes a CSS class name (".faq-question" -> "faq-question").
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareClass($value): string
    {
        return \is_scalar($value) ? preg_replace('/[^A-Za-z0-9_\-]/', '', ltrim(trim((string) $value), '.')) : '';
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
            'questionClass' => '',
            'answerClass'   => '',
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
            'questionClass' => [
                'type'    => 'text',
                'default' => self::QUESTION_CLASS,
                'hint'    => self::QUESTION_CLASS,
            ],
            'answerClass'   => [
                'type'    => 'text',
                'default' => self::ANSWER_CLASS,
                'hint'    => self::ANSWER_CLASS,
            ],
        ];
    }
}
