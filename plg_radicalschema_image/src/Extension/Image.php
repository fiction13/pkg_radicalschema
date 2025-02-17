<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Image\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\RadicalSchema\Administrator\Adapter\Adapter;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PathHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\OGHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\TypesHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\RadicalSchema\Image\Helper\ImageHelper;

class Image extends Adapter implements SubscriberInterface
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
	 * @var ImageHelper
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $helper;

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
			'onAjaxImage'              => 'onAjaxImage',
			'onContentPrepareForm'     => 'onContentPrepareForm',
		], parent::getSubscribedEvents());
	}

	/**
	 * @param          $subject
	 * @param   array  $config
	 *
	 * @throws \Exception
	 * @since  __DEPLOY_VERSION__
	 *
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Helper
		$this->helper = new ImageHelper();
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
	 *
	 * @return string|bool
	 *
	 * @since         __DEPLOY_VERSION__
	 */
	public function onAjaxImage()
	{
		$app  = Factory::getApplication();
		$task = $app->getInput()->get('task');

		switch ($task)
		{
			case 'generate':
				//redirect to image
				$app->redirect($this->helper->generate(), 302);
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

		$item = $this->helper->getProviderData();

		if (!$item)
		{
			return;
		}

        $priority = 0.5;

        // Set priority for generated from current og image
        if (ParamsHelper::getComponentParams()->get('image_imagetype_generate_background') === 'current')
        {
            $priority = 1;
        }

		// Get and set opengraph data
		$collections = PathHelper::getInstance()->getTypes('meta');

		foreach ($collections as $collection)
		{
			$ogData = TypesHelper::execute('meta', $collection, $item, $priority);
			OGHelper::getInstance()->addChild('root', $ogData);
		}
	}
}