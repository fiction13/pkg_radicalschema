<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\System\RadicalSchema\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\FormHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PathHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PluginsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\SchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\TypesHelper;
use Joomla\Event\SubscriberInterface;

class RadicalSchema extends CMSPlugin implements SubscriberInterface
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
	 *
	 * @var  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $loadAdminMenu = false;

	/**
	 *
	 * @var  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $removeAdminMenu = false;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterInitialise'     => 'onAfterInitialise',
			'onContentPrepareForm'  => 'onContentPrepareForm',
			'onAfterRender'         => 'onAfterRender',
			'onBeforeRender'        => 'onBeforeRender',
			'onPreprocessMenuItems' => 'onPreprocessMenuItems'
		];
	}

	/**
	 * OnAfterInitialise event
	 *
	 * Register RadicalSchema namespace.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onAfterInitialise()
	{
		// Trigger for `onRadicalSchemaRegisterTypes` event.
		PluginsHelper::triggerPlugins(['system', 'radicalschema'], 'onRadicalSchemaRegisterTypes', []);

        // Trigger for `onRadicalSchemaAfterInitialise` event.
		PluginsHelper::triggerPlugins(['system', 'radicalschema'], 'onRadicalSchemaAfterInitialise', []);
	}

	/**
	 * Listener for the `onBeforeRender` event.
	 *
	 * @throws  \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onBeforeRender()
	{
		$app   = Factory::getApplication();
		$input = $app->getInput();

		// Load config js
		if ($app->isClient('administrator')
			&& $input->getCmd('option') === 'com_config'
			&& $input->getCmd('view') === 'component'
			&& $input->getCmd('component') === 'com_radicalschema')
		{
			$assets = $app->getDocument()->getWebAssetManager();
			$assets->getRegistry()->addExtensionRegistryFile('com_radicalschema');
			$assets->usePreset('com_radicalschema.administrator.config');
		}

		// Load other form js
		if ($app->isClient('administrator'))
		{
			$assets = $app->getDocument()->getWebAssetManager();
			$assets->getRegistry()->addExtensionRegistryFile('com_radicalschema');
			$assets->usePreset('com_radicalschema.administrator.form');
		}
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

		if ($formName === 'com_config.component'  && $app->getInput()->get('component') === 'com_radicalschema')
		{
			// Get all collections of types
			$collections = PathHelper::getInstance()->getTypes('meta');

			foreach ($collections as $collection)
			{
				$element = FormHelper::createBoolField('meta_enable_' . $collection, 1);
				$form->setField($element, null, false, 'meta');
			}

            // Load languages
            $plugins = PluginsHelper::getPlugins('radicalschema');
            foreach ($plugins as $plugin)
            {
                PluginsHelper::loadPlugin('radicalschema', $plugin->value);
            }
		}

		return true;
	}

	/**
	 * OnAfterRender event.
	 *
	 * @return  bool|void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAfterRender()
	{
		$app = Factory::getApplication();

		// Check site client
		if ($app->isClient('administrator') || $app->getInput()->get('option') == 'com_ajax')
		{
			return false;
		}

		// Get provider plugins
		$params = ParamsHelper::getComponentParams();

		// Trigger for `onRadicalSchemaProvider` event.
		PluginsHelper::triggerPlugins(['system', 'radicalschema'], 'onRadicalSchemaProvider', []);

		// Set Schema.org and Opengraph to the end of body
		$body   = $app->getBody();
		$schema = $opengraph = '';

		// Add Schema.org
		if ($params->get('enable_schema', 1))
		{
			// Add website schema type
			if ($params->get('schema_enable_type_website', 0))
			{
				$websiteData = TypesHelper::execute('schema', 'website', []);
				SchemaHelper::getInstance()->addChild('root', $websiteData);
			}

			// Add logo schema type
			if ($params->get('schema_enable_type_organization', 0))
			{
				$organizationData = [
					'image'               => $params->get('schema_type_organization_image'),
					'title'               => $params->get('schema_type_organization_title'),
					'addressCountry'      => $params->get('schema_type_organization_country'),
					'addressLocality'     => $params->get('schema_type_organization_locality'),
					'addressRegion'       => $params->get('schema_type_organization_region'),
					'streetAddress'       => $params->get('schema_type_organization_address'),
					'postalCode'          => $params->get('schema_type_organization_code'),
					'postOfficeBoxNumber' => $params->get('schema_type_organization_post'),
					'hasMap'              => $params->get('schema_type_organization_map'),
					'phone'               => $params->get('schema_type_organization_phone'),
					'contactType'         => $params->get('schema_type_organization_contact_type'),
				];

				$organization = TypesHelper::execute('schema', 'organization', $organizationData);
				SchemaHelper::getInstance()->addChild('root', $organization);
			}

			// Add breadcrumbs schema type
			if ($params->get('schema_enable_type_breadcrumblist', 0))
			{
				$breadcrumbsData = TypesHelper::execute('schema', 'breadcrumblist', []);
				SchemaHelper::getInstance()->addChild('root', $breadcrumbsData);
			}

			$schema = RadicalSchemaHelper::buildSchema($body, $params);
		}

		// Add Opengraph
		if ($params->get('enable_meta', 1))
		{
			$opengraph = RadicalSchemaHelper::buildOpengraph($body, $params);
		}

		// Place
		$place      = '</' . $params->get('extra_insert_place', 'body') . '>';
		$textBefore = "\n<!-- RadicalSchema: start -->\n";
		$textAfter  = "\n<!-- RadicalSchema: end -->\n";

		// Insert microdata
		$body = str_replace($place, $textBefore . $opengraph . $schema . $textAfter . $place, $body);

		$app->setBody($body);
	}

	/**
	 * Listener for the `onAfterGetMenuTypeOptions` event.
	 *
	 * @param   $event
	 *
	 * @throws \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onPreprocessMenuItems($event)
	{
		$context  = $event->getArgument(0);
		$children = $event->getArgument(1);
		$app      = Factory::getApplication();

		// Load language
		$app->getLanguage()->load('com_radicalschema', JPATH_ADMINISTRATOR);

		if ($app->isClient('administrator') && $context === 'com_menus.administrator.module')
		{
			if ($this->loadAdminMenu === false)
			{
				$parent = new AdministratorMenuItem (array(
					'title'     => 'COM_RADICALSCHEMA',
					'type'      => 'component',
					'link'      => 'index.php?option=com_config&view=component&component=com_radicalschema',
					'element'   => 'com_config',
					'class'     => 'class:code',
					'ajaxbadge' => null,
					'dashboard' => null,
					'scope'     => 'default',
				));

				/* @var $root AdministratorMenuItem */
				$root = $children[0]->getParent();
				$root->addChild($parent);
				$this->loadAdminMenu = true;

			}
			elseif ($this->removeAdminMenu === false)
			{
				foreach ($children as $child)
				{
					if ($child->type === 'component'
						&& (int) $child->component_id === ComponentHelper::getComponent('com_radicalschema')->id)
					{
						$child->getParent()->removeChild($child);
						$this->removeAdminMenu = true;
					}
				}
			}
		}

		$event->setArgument(1, $children);
	}
}