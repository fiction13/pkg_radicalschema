<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Standard\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\RadicalSchema\Administrator\Adapter\Adapter;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PathHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\OGHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\TypesHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Event\SubscriberInterface;

class Standard extends Adapter implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge([
            'onContentPrepareForm'      => 'onContentPrepareForm'
        ], parent::getSubscribedEvents());
    }

    /**
     * Adds forms for override
     *
     * @param   mixed  $event  Event.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareForm($event)
    {
        $form     = $event->getArgument(0);
        $data     = $event->getArgument(1);
        $app      = Factory::getApplication();
        $formName = $form->getName();

        if ($formName !== 'com_config.component')
        {
            return false;
        }

        // Load path
        Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');

        // Load language
        $app->getLanguage()->load('com_radicalschema', JPATH_ADMINISTRATOR);

        // Config
        if ($app->getInput()->get('component') === 'com_radicalschema')
        {
            // Load config
            $form->loadFile('com_config.component');
        }

        return true;
    }

    /**
     * OnRadicalSchemaProvider event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onRadicalSchemaProvider()
    {
        if (!RadicalSchemaHelper::checkEnable($this->_name))
        {
            return;
        }

        $document = Factory::getApplication()->getDocument();

        // Data object
        $object = new \stdClass();
        $params = ParamsHelper::getComponentParams();

        // Set title
        if ($params->get('standard_title') !== 'none')
        {
            $object->title = $document->getTitle();
        }

        // Set description
        if ($params->get('standard_description') !== 'none')
        {
            $object->description = $document->getDescription();
        }

        // Set image
        if ($params->get('standard_image_choice') === 'static')
        {
            $object->image = $params->get('standard_image');
        }
        else if ($params->get('standard_image_choice') === 'body')
        {
            list(, $body) = explode('<body', Factory::getApplication()->getBody());
            $object->image = ValueHelper::getFirstImage($body);
        }

        // Set site name
        if ($params->get('standard_site_name'))
        {
            $object->site_name = $params->get('standard_site_name');
        }

        // Set locale
        if ($params->get('standard_locale'))
        {
            $language = Factory::getApplication()->getLanguage();
            $tag      = $language->getTag();
            list($locale) = explode('-', $tag);
            $object->locale = $locale;
        }

        // Set twitter site
        if ($params->get('standard_site'))
        {
            $object->site = $params->get('standard_site');
        }

        // Set twitter creator
        if ($params->get('standard_creator'))
        {
            $object->creator = $params->get('standard_creator');
        }

        // Get and set opengraph data
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            $ogData = TypesHelper::execute('meta', $collection, $object, 0.3);
            OGHelper::getInstance()->addChild('root', $ogData);
        }
    }
}