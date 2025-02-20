<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Menu\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\RadicalSchema\Administrator\Adapter\Adapter;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use stdClass;

class Menu extends Adapter implements SubscriberInterface
{
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
            'onContentPrepareForm'      => 'onContentPrepareForm',
            'onRadicalSchemaGetMapping' => 'onRadicalSchemaGetMapping'
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

        // Load path
        Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');

        // Load language
        $app->getLanguage()->load('com_radicalschema', JPATH_ADMINISTRATOR);

        // Check menu edit form
        if ($app->isClient('administrator') && $formName === 'com_menus.item' && !in_array($data->type, ['heading', 'url', 'separator']))
        {
            // Check and set meta fields
            if (RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
            {
                // Load config
                $form->loadFile('com_menus.item');

                // Set meta fields
                $this->setFormMetaFields($form, 'radicalschema_meta', 'radicalschema_mapping', ['useglobal' => '1']);
            }

            if (RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
            {
                // Load config
                $form->loadFile('com_menus.item');

                // Set schema fields
                $data = new Registry($data);
                $type = $data->get('params.' . $this->_name . '_type');

                if (!$type)
                {
                    $type = ParamsHelper::getComponentParams()->get('content_type', '');
                }

                $this->setFormSchemaFields($form, $type, 'radicalschema_schema', 'radicalschema_mapping', ['useglobal' => '1']);
            }
        }

        // Config
        if ($app->getInput()->get('component') === 'com_radicalschema')
        {
            // Load config
            $form->loadFile('com_config.component');

            // Set meta fields
            $this->setFormMetaFields($form, $this->_name . '_meta', 'radicalschema_mapping', ['showon' => 'menu_meta_enable!:0']);

            // Set schema fields
            $this->setFormSchemaFields($form, '', $this->_name . '_schema', 'radicalschema_mapping', ['showon' => 'menu_schema_enable!:0']);
        }

        return true;
    }

    /**
     * onRadicalSchemaGetMapping event
     *
     * @param   string  $plugin  .
     *
     * @return  array|false Array mapping if true
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onRadicalSchemaGetMapping(string $plugin)
    {
        if ($this->_name !== $plugin)
        {
            return false;
        }

        $fields           = [];
        $fields['core']   = [
            'title'
        ];
        $fields['params'] = [
            'page_title',
            'page_heading',
            'menu-meta_description'
        ];

        return $fields;
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

        if (!RadicalSchemaHelper::checkEnable($this->_name, 'schema') && !RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
        {
            return;
        }

        $item = $this->getItem();

        // Check is article view
        if (!$this->checkActive($item))
        {
            return;
        }

        if ($item)
        {
            $this->setMicrodata($item, 0.9);
        }
    }

    /**
     * Method to index an item.
     *
     * @param   Registry  $params  The item to index as a Result object.
     *
     * @return  boolean  True on success.
     *
     * @throws  \Exception on database error.
     * @since   __DEPLOY_VERSION__
     */
    protected function getItem(Registry $params = null)
    {
        if (empty($this->_items['menu']))
        {
            $item = Factory::getApplication()->getMenu()->getActive();

            // Convert parameter fields to objects.
            $registry   = $item->getParams();
            $item       = new Registry($item);
            $itemParams = clone ParamsHelper::getComponentParams();;

            // Merge with custom params (etc. category)
            if ($params)
            {
                $itemParams->merge($params, true);
            }

            $itemParams = $itemParams->merge($registry, true)->toArray();
            $item->set('params', $itemParams);
            $this->_items['menu'] = $item;
        }

        return $this->_items['menu'];
    }

    /**
     * Method for check current menu page
     *
     * @return mixed|stdClass
     *
     * @since __DEPLOY_VERSION__
     */

    public function checkActive(?Registry $menu)
    {
        if ($menu === null)
        {
            return false;
        }

        $current   = true;
        $inputVars = Factory::getApplication()->input->getArray();

        foreach ($menu->get('query') as $key => $value)
        {
            if (!isset($inputVars[$key]) || $inputVars[$key] !== $value)
            {
                $current = false;
                break;
            }
        }

        return $current;
    }
}