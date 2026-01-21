<?php

/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Adapter;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\RadicalSchema\Administrator\Helper\FormHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PathHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\RadicalSchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\OGHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\SchemaHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\TypesHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

/**
 * Prototype adapter class for the Finder indexer package.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class Adapter extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * The extension name.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $extension;

    /**
     * The database object.
     *
     * @var    DatabaseInterface
     * @since  __DEPLOY_VERSION__
     */
    protected $db;

    /**
     * The table name.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $table;

    /**
     * Site items data.
     *
     * @var  array|null
     *
     * @since  __DEPLOY_VERSION__
     */
    protected ?array $_items = null;

    /**
     * Method to instantiate the indexer adapter.
     *
     * @param   DispatcherInterface  $dispatcher  The object to observe.
     * @param   array                $config      An array that holds the plugin configuration.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        // Call the parent constructor.
        parent::__construct($dispatcher, $config);
    }

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
            'onRadicalSchemaProvider' => 'onRadicalSchemaProvider',
        ];
    }

    /**
     * Method to index an item.
     *
     * @param   Registry  $params  The item to index as a Result object.
     *
     * @return  Registry
     *
     * @throws  \Exception on database error.
     * @since   __DEPLOY_VERSION__
     */
    protected function getItem(Registry $params = null)
    {
        $id = (int) Factory::getApplication()->getInput()->get('id', 0);

        if (empty($this->_items[$id]))
        {
            $this->_items[$id] = $this->getItemFromDatabase($id);
            $item              = $this->_items[$id];
            $item['params']    = ParamsHelper::merge($item['attribs'] ?? $item['params'], $params);

            $this->_items[$id] = new Registry($item);
        }

        return $this->_items[$id];
    }

    /**
     * Get item from database
     *
     * @param   int|string  $pk     Target column value search in
     * @param   bool        $force  Get force target without cache
     *
     * @return array|false
     *
     * @since __DEPLOY_VERSION__
     */
    public function getItemFromDatabase($pk = 0, $force = false)
    {
        if (!$this->table)
        {
            return false;
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->from($db->qn($this->table));

        // Get item
        if ($pk !== 0)
        {
            $query->select('*')
                ->where($db->qn('id') . ' = ' . $db->q($pk));

            $db->setQuery($query);

            return ValueHelper::prepareArray($db->loadAssoc());
        }

        // Get table columns data
        $item = $db->getTableColumns($this->table);

        return array_fill_keys(array_keys($item), null);
    }

    /**
     * Method get provider data
     *
     * @return void|object
     *
     * @since __DEPLOY_VERSION__
     */
    public function getSchemaObject()
    {
        // Get item object
        $item = $this->getItem();

        // Data object
        $object     = new \stdClass();
        $object->id = $item->get('id');

        // Config field for current schema type
        $configFields = array_keys(TypesHelper::getConfig('schema', $item->get('params.' . $this->_name . '_type'), false));

        foreach ($configFields as $configField)
        {
            $key           = $item->get('params.' . $this->_name . '_schema_' . $configField);
            $isRegistryKey = !empty($key) && strpos($key, '.') !== false && !preg_match('/\s/', $key);

            if ($item->exists($key))
            {
                $object->{$configField} = $item->get($key);
                continue;
            }

            $object->{$configField} = !$isRegistryKey ? $key : '';
        }

        return $object;
    }

    /**
     * Method get provider data
     *
     * @return void|object
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMetaObject()
    {
        // Get item object
        $item = $this->getItem();

        // Data object
        $object     = new \stdClass();
        $object->id = $item->get('id');

        // Config field for meta type
        $configFields = $this->getMetaFields($this->_name . '_meta_');

        foreach ($configFields as $f => $field)
        {
            $key = $item->get('params.' . $field['name']);
            $isRegistryKey = !empty($key) && strpos($key, '.') !== false && !preg_match('/\s/', $key);

            if ($item->exists($key))
            {
                $object->{$f} = $item->get($key);
                continue;
            }

            $object->{$f} = !$isRegistryKey ? $key : '';
        }

        return $object;
    }

    /**
     * Set meta fields to Form
     *
     * @param   Form    $form          Form
     * @param   string  $group         Field form group
     * @param   string  $fieldType     Field Type
     * @param   array   $extraAttribs  Extra attribs
     *
     * @since __DEPLOY_VERSION__
     */
    public function setFormMetaFields(
        Form   $form,
        string $group = '',
        string $fieldType = '',
        array  $extraAttribs = []
    )
    {
        $prefix = $this->_name . '_meta_';

        // Add fields to fieldset
        $addFields = self::getMetaFields($prefix);

        // In result - add fields to form
        if ($addFields)
        {
            foreach ($addFields as $key => $field)
            {
                $attribs = [
                    'type'         => $fieldType,
                    'default'      => $field['default'],
                    'add_to_label' => $field['type'],
                    'plugin'       => $this->_name
                ];
                $attribs = array_merge($attribs, $extraAttribs);
                $element = FormHelper::createField($field['name'], $attribs);
                $form->setField($element, null, false, $group);
            }
        }

        return true;
    }

    /**
     * Set schema.org fields to Form
     *
     * @param   Form    $form          Form
     * @param   string  $type          Schema type
     * @param   string  $group         Field form group
     * @param   string  $fieldType     Field Type
     * @param   array   $extraAttribs  Extra attribs
     *
     * @since __DEPLOY_VERSION__
     */
    public function setFormSchemaFields(
        Form   $form,
        string $type = '',
        string $group = '',
        string $fieldType = '',
        array  $extraAttribs = []
    )
    {
        if (empty($type))
        {
            $type = ComponentHelper::getComponent('com_radicalschema')->getParams()->get($this->_name . '_type');
        }

        $prefix = $this->_name . '_schema_';

        if ($type)
        {
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    $attribs = [
                        'type'   => $fieldType,
                        'plugin' => $this->_name
                    ];
                    $attribs = array_merge($attribs, $extraAttribs);
                    $element = FormHelper::createField($prefix . $configField, $attribs);
                    $form->setField($element, null, false, $group);
                }
            }
        }

        return true;
    }

    /**
     * Get all config fields for all meta collections
     *
     * @param   string  $prefix  Field name prefix
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMetaFields(string $prefix)
    {
        $addFields = [];

        // Get all collections of types
        $collections = PathHelper::getInstance()->getTypes('meta');

        foreach ($collections as $collection)
        {
            // Get config of each meta type
            $collectionConfig = TypesHelper::getConfig('meta', $collection, false);

            if (!empty($collectionConfig))
            {
                $fields = array_keys($collectionConfig);

                // Add each field of config
                foreach ($fields as $field)
                {
                    if (!isset($addFields[$field]))
                    {
                        $addFields[$field] = [
                            'name'    => $prefix . $field,
                            'default' => $collectionConfig[$field],
                        ];
                    }

                    $addFields[$field]['type'][] = ucfirst($collection);
                }
            }
        }

        return $addFields;
    }

    /**
     * Set microdata
     *
     * @param   Registry  $item      Item
     * @param   float     $priority  Priority
     *
     * @since __DEPLOY_VERSION__
     */
    public function setMicrodata(Registry $item, $priority = 0.5)
    {
        // Get schema type
        if (RadicalSchemaHelper::checkEnable($this->_name, 'schema'))
        {
            $type = $item->get('params.' . $this->_name . '_type');

            // Get and set schema data
            $schemaObject = $this->getSchemaObject();

            if ($schemaObject)
            {
                $schemaData = TypesHelper::execute('schema', $type, $schemaObject, $priority);
                SchemaHelper::getInstance()->addChild('root', $schemaData);
            }
        }

        // Get and set opengraph data
        if (RadicalSchemaHelper::checkEnable($this->_name, 'meta'))
        {
            $metaObject = $this->getMetaObject();

            if ($metaObject)
            {
                $collections = PathHelper::getInstance()->getTypes('meta');

                foreach ($collections as $collection)
                {
                    $ogData = TypesHelper::execute('meta', $collection, $metaObject, $priority);
                    OGHelper::getInstance()->addChild('root', $ogData);
                }
            }
        }
    }
}