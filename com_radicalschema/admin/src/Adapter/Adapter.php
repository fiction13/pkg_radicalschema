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
use Joomla\CMS\Language\Text;
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
     * Groups of item data available for mapping (see onRadicalSchemaGetMapping).
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    protected const MAPPING_GROUPS = ['fields', 'images', 'attribs', 'params', 'urls', 'metadata'];

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
            $item['params']    = ParamsHelper::getItemParams($item['attribs'] ?? $item['params'], $this->_name);

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
            $key = $item->get('params.' . $this->_name . '_schema_' . $configField);

            $object->{$configField} = $this->resolveMappingValue($item, $key);
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

            $object->{$f} = $this->resolveMappingValue($item, $key);
        }

        return $object;
    }

    /**
     * Resolves a mapping value: registry key of the item ("fields.price"),
     * manual input, or rows of a subform (array of mappings).
     *
     * @param   Registry  $item  Item data.
     * @param   mixed     $key   Saved mapping value.
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function resolveMappingValue(Registry $item, $key)
    {
        // Multiple select: list of values
        if (\is_array($key) && $key && array_filter($key, 'is_scalar') === $key && array_is_list($key))
        {
            return array_values(array_filter(array_map('strval', $key), 'strlen'));
        }

        // Subform rows
        if (\is_array($key) || \is_object($key))
        {
            $rows = [];

            foreach ((array) $key as $row)
            {
                if (!\is_array($row) && !\is_object($row))
                {
                    continue;
                }

                $resolvedRow = [];

                foreach ((array) $row as $name => $value)
                {
                    $resolvedRow[$name] = $this->resolveMappingValue($item, $value);
                }

                $rows[] = $resolvedRow;
            }

            return $rows;
        }

        // Empty or turned off in the item ("Do not use")
        if ($key === null || $key === '' || !\is_scalar($key) || $key === '_none_')
        {
            return '';
        }

        $key = (string) $key;

        if ($item->exists($key))
        {
            return $item->get($key);
        }

        // Mapping to an empty item field ("fields.price") - no value.
        // Manual input with dots (URL, "4.5", e-mail) is returned as is.
        return $this->isMappingKey($item, $key) ? '' : $key;
    }

    /**
     * Checks whether a value is a mapping key of the item ("fields.price", "images.image_intro"),
     * not a manual value like "https://site.com/page" or "4.5".
     *
     * @param   Registry  $item  Item data.
     * @param   string    $key   Saved mapping value.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function isMappingKey(Registry $item, string $key): bool
    {
        if (!preg_match('/^([a-z][a-z0-9_\-]*)\.[a-z0-9_\-]+(\.[a-z0-9_\-]+)*$/i', $key, $matches))
        {
            return false;
        }

        return \in_array($matches[1], self::MAPPING_GROUPS, true) || $item->exists($matches[1]);
    }

    /**
     * Detects the schema type selected in an item edit form.
     * On save Joomla prepares the form with empty data, so the posted value is checked too,
     * otherwise type-specific fields are filtered out and their values are lost.
     *
     * @param   mixed   $data   Form data passed to onContentPrepareForm.
     * @param   string  $group  Form fields group ("attribs", "params").
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getFormSchemaType($data, string $group): string
    {
        $fieldName = $this->_name . '_type';
        $type      = (new Registry($data))->get($group . '.' . $fieldName, '');

        // Save / apply / save2copy: data is empty, use posted value
        if (empty($type))
        {
            $jform = Factory::getApplication()->getInput()->get('jform', [], 'array');
            $type  = $jform[$group][$fieldName] ?? '';
        }

        if (empty($type))
        {
            $type = ParamsHelper::getComponentParams()->get($fieldName, '');
        }

        return \is_string($type) ? preg_replace('/[^a-zA-Z0-9_]/', '', $type) : '';
    }

    /**
     * Creates a form field element from a type field definition.
     *
     * @param   string  $name          Full field name.
     * @param   string  $prefix        Field name prefix (e.g. "content_schema_").
     * @param   array   $definition    Field definition from FormFieldsAwareType.
     * @param   array   $attribs       Base attribs (type, plugin, useglobal, showon).
     *
     * @return  \SimpleXMLElement|false
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createSchemaFieldElement(string $name, string $prefix, array $definition, array $attribs)
    {
        $useGlobal = !empty($attribs['useglobal']);
        $type      = $definition['type'] ?? '';
        $key       = substr($name, \strlen($prefix));
        $typeName  = $definition['_type'] ?? '';

        // Showon: "offerType" => ["AggregateOffer"] -> "content_schema_offerType:AggregateOffer"
        if (!empty($definition['showon']))
        {
            $conditions = [];

            foreach ($definition['showon'] as $field => $values)
            {
                $values = (array) $values;

                // Item form: empty value means "from settings"
                if ($useGlobal)
                {
                    $fieldDefinition = TypesHelper::getFormFields('schema', $definition['_type'] ?? '')[$field] ?? [];
                    $globalValue     = ParamsHelper::getTypeParam($this->_name, $definition['_type'] ?? '', $field, $fieldDefinition['default'] ?? '');

                    if (\in_array($globalValue, $values, true))
                    {
                        $values[] = '';
                    }
                }

                $conditions[] = $prefix . $field . ':' . implode(',', $values);
            }

            $attribs['showon'] = implode('[AND]', array_filter([$attribs['showon'] ?? '', implode('[AND]', $conditions)]));
        }

        // Mapping field (with optional predefined values in the "Extra" group)
        if ($type === '' || $type === 'radicalschema_mapping')
        {
            if (!empty($definition['addoptions']))
            {
                $attribs['addoptions'] = implode(';', (array) $definition['addoptions']);
            }

            return FormHelper::createField($name, $attribs);
        }

        unset($attribs['useglobal']);
        $plugin          = $attribs['plugin'] ?? '';
        $attribs['type'] = $type;

        // Menu item in a standard Joomla modal window (with search and filters)
        if ($type === 'modal_menu')
        {
            $attribs['addfieldprefix'] = 'Joomla\\Component\\Menus\\Administrator\\Field';
            $attribs['select']         = 'true';
            $attribs['edit']           = 'true';
            $attribs['clear']          = 'true';
            $attribs['clientid']       = '0';
            $attribs['disable']        = 'separator,heading';

            // Item form: empty value means "from settings"
            if ($useGlobal)
            {
                $globalValue     = ParamsHelper::getTypeParam($this->_name, $typeName, $key, '');
                $attribs['hint'] = Text::sprintf('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_DEFAULT', $this->getMenuItemTitle($globalValue));
            }

            return FormHelper::createField($name, $attribs);
        }

        // Multiple select (fancy select). Empty value in the item form means "from settings".
        if ($type === 'list' && !empty($definition['multiple']))
        {
            $attribs['multiple'] = 'true';
            $attribs['layout']   = 'joomla.form.field.list-fancy-select';

            if ($useGlobal)
            {
                $globalValue     = (array) ParamsHelper::getTypeParam($this->_name, $typeName, $key, []);
                $globalLabels    = array_map(static fn ($value) => Text::_($definition['options'][$value] ?? $value), $globalValue);
                $attribs['hint'] = Text::sprintf(
                    'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_DEFAULT',
                    $globalLabels ? implode(', ', $globalLabels) : Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT')
                );
            }

            $element = FormHelper::createField($name, $attribs);

            foreach ($definition['options'] ?? [] as $value => $label)
            {
                $element->addChild('option', htmlspecialchars(Text::_($label), ENT_COMPAT, 'UTF-8'))->addAttribute('value', (string) $value);
            }

            return $element;
        }

        // List field / menu item field
        if ($type === 'list' || $type === 'menuitem')
        {
            $options = [];

            if ($useGlobal)
            {
                $globalValue = ParamsHelper::getTypeParam($this->_name, $typeName, $key, $definition['default'] ?? '');
                $globalLabel = $type === 'menuitem'
                    ? $this->getMenuItemTitle($globalValue)
                    : ($definition['options'][$globalValue] ?? $globalValue);
                $options[''] = Text::sprintf('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_DEFAULT', Text::_($globalLabel));
            }
            else
            {
                $attribs['default'] = $definition['default'] ?? '';

                if ($type === 'menuitem')
                {
                    $options[''] = Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT');
                }
            }

            foreach ($definition['options'] ?? [] as $value => $label)
            {
                $options[$value] = Text::_($label);
            }

            $element = FormHelper::createField($name, $attribs);

            foreach ($options as $value => $label)
            {
                $element->addChild('option', htmlspecialchars($label, ENT_COMPAT, 'UTF-8'))->addAttribute('value', (string) $value);
            }

            return $element;
        }

        // Subform (repeatable rows of mapping fields)
        if ($type === 'subform')
        {
            $attribs['multiple']  = 'true';
            $attribs['layout']    = $definition['layout'] ?? 'joomla.form.field.subform.repeatable-table';
            $attribs['min']       = (string) ($definition['min'] ?? 0);
            $attribs['max']       = (string) ($definition['max'] ?? 10);
            $attribs['buttons']   = 'add,remove,move';
            $attribs['groupByFieldset'] = 'false';

            $element = FormHelper::createField($name, $attribs);
            $form    = $element->addChild('form');

            foreach ($definition['fields'] ?? [] as $childName => $childDefinition)
            {
                $child = $form->addChild('field');
                $child->addAttribute('name', $childName);
                $child->addAttribute('type', $childDefinition['type'] ?? 'radicalschema_mapping');
                $child->addAttribute('plugin', $plugin);
                $child->addAttribute('addfieldprefix', 'Joomla\\Component\\RadicalSchema\\Administrator\\Field');
                $child->addAttribute('label', Text::_($childDefinition['label'] ?? $childName));

                // Extra attributes (filter, rows, hint...)
                foreach ($childDefinition['attribs'] ?? [] as $attribName => $attribValue)
                {
                    $child->addAttribute($attribName, (string) $attribValue);
                }
            }

            return $element;
        }

        // Simple field (text, textarea...)
        if (!$useGlobal && isset($definition['default']))
        {
            $attribs['default'] = (string) $definition['default'];
        }

        if (!empty($definition['hint']))
        {
            $attribs['hint'] = (string) $definition['hint'];
        }

        return FormHelper::createField($name, $attribs);
    }

    /**
     * Returns the title of a site menu item.
     *
     * @param   mixed  $id  Menu item id.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getMenuItemTitle($id): string
    {
        if (!(int) $id)
        {
            return Text::_('COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT');
        }

        try
        {
            $menuItem = Factory::getApplication()->getMenu('site')->getItem((int) $id);
        }
        catch (\Throwable $e)
        {
            $menuItem = null;
        }

        return $menuItem ? $menuItem->title : '#' . (int) $id;
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
            $definitions  = TypesHelper::getFormFields('schema', $type);

            if ($configFields)
            {
                foreach ($configFields as $configField)
                {
                    $attribs = [
                        'type'   => $fieldType,
                        'plugin' => $this->_name
                    ];
                    $attribs = array_merge($attribs, $extraAttribs);

                    // Item form: default value is taken from the settings of this type
                    if (!empty($attribs['useglobal']))
                    {
                        $attribs['globalkey'] = $prefix . $type . '_' . $configField;
                    }

                    if (isset($definitions[$configField]))
                    {
                        $definition          = $definitions[$configField];
                        $definition['_type'] = $type;
                        $element             = $this->createSchemaFieldElement($prefix . $configField, $prefix, $definition, $attribs);
                    }
                    else
                    {
                        $element = FormHelper::createField($prefix . $configField, $attribs);
                    }

                    $form->setField($element, null, false, $group);
                }
            }
        }

        return true;
    }

    /**
     * Adds schema fields of all types to the settings form. Each type has its own
     * field names ("content_schema_product_price"), so defaults of every type are kept
     * when the selected type is changed. Only fields of the selected type are shown.
     *
     * @param   Form    $form          Form.
     * @param   string  $group         Fieldset name.
     * @param   string  $fieldType     Field type.
     * @param   array   $extraAttribs  Extra attribs (showon...).
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setFormSchemaTypesFields(Form $form, string $group, string $fieldType, array $extraAttribs = []): bool
    {
        $params      = ParamsHelper::getComponentParams();
        $typeField   = $this->_name . '_type';
        $currentType = (string) $params->get($typeField, '');

        foreach (PathHelper::getInstance()->getTypes('schema') as $type)
        {
            $prefix       = $this->_name . '_schema_' . $type . '_';
            $configFields = array_keys(TypesHelper::getConfig('schema', $type, false));
            $definitions  = TypesHelper::getFormFields('schema', $type);
            $baseShowon   = implode('[AND]', array_filter([$extraAttribs['showon'] ?? '', $typeField . ':' . $type]));

            if (!$configFields)
            {
                continue;
            }

            // Type heading
            $note = new \SimpleXMLElement('<field />');
            $note->addAttribute('name', $prefix . 'note');
            $note->addAttribute('type', 'note');
            $note->addAttribute('heading', 'h4');
            $note->addAttribute('label', 'Schema.org: ' . ucfirst($type));
            $note->addAttribute('showon', $baseShowon);
            $form->setField($note, null, false, $group);

            foreach ($configFields as $configField)
            {
                $attribs = array_merge(['type' => $fieldType, 'plugin' => $this->_name], $extraAttribs);

                $attribs['showon'] = $baseShowon;
                $attribs['label']  = Text::_('COM_RADICALSCHEMA_PARAM_SCHEMA_' . strtoupper($configField));

                // Values saved by previous versions (common keys) for the selected type
                $legacyValue = $params->get($this->_name . '_schema_' . $configField);

                if ($type === $currentType && \is_scalar($legacyValue) && $legacyValue !== '')
                {
                    $attribs['default'] = (string) $legacyValue;
                }

                if (isset($definitions[$configField]))
                {
                    $definition          = $definitions[$configField];
                    $definition['_type'] = $type;

                    if (isset($attribs['default']))
                    {
                        $definition['default'] = $attribs['default'];
                    }

                    $element = $this->createSchemaFieldElement($prefix . $configField, $prefix, $definition, $attribs);
                }
                else
                {
                    $element = FormHelper::createField($prefix . $configField, $attribs);
                }

                $form->setField($element, null, false, $group);
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