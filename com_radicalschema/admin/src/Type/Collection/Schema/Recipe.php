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

defined('_JEXEC') or die;

use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\InterfaceType;

class Recipe implements InterfaceType
{
    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * @param $item
     * @param $priority
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    public function execute($item, $priority)
    {
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $data = [
            'uid'                => $this->uid,
            'priority'           => $priority,
            '@context'           => 'https://schema.org',
            '@type'              => 'Recipe',
            'name'               => $item->title ? ValueHelper::prepareText($item->title, 110) : '',
            'description'        => $item->description ? ValueHelper::prepareText($item->description, 5000) : '',
            'datePublished'      => $item->datePublished ? ValueHelper::prepareDate($item->datePublished) : '',
            'prepTime'           => $item->prepTime ? 'PT' . $item->prepTime . 'M' : '',
            'cookTime'           => $item->cookTime ? 'PT' . $item->cookTime . 'M' : '',
            'totalTime'          => $item->totalTime ? 'PT' . $item->totalTime . 'M' : '',
            'calories'           => $item->calories ?? '',
            'recipeCategory'     => $item->recipeCategory ?? '',
            'recipeYield'        => $item->recipeYield ?? '',
            'recipeCuisine'      => $item->recipeCuisine ?? '',
            'recipeInstructions' => $item->recipeInstructions ?? '',
        ];

        // Author
        if ($item->author && !empty($item->author))
        {
            $data['publisher'] = [
                '@type' => 'Person',
                'name'  => ValueHelper::prepareUser($item->author)
            ];
        }

        // Image
        if (isset($item->image) && !empty($item->image))
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => ValueHelper::prepareLink($item->image)
            ];
        }

        // Ingredients
        if (isset($item->recipeIngredient) && !empty($item->recipeIngredient))
        {
            $data['recipeIngredient'] = ValueHelper::getArrayFromText($item->recipeIngredient);
        }

        return $data;
    }

    /**
     * Get config for JForm and Yootheme Pro elements
     *
     * @param   bool  $addUid
     *
     * @return string[]
     *
     * @since __DEPLOY_VERSION__
     */
    public function getConfig($addUid = true)
    {
        $config = [
            'title'       => '',
            'description' => '',
            'image'       => '',
            'author'      => '',
            'datePublished'      => '',
            'prepTime'           => '',
            'cookTime'           => '',
            'totalTime'          => '',
            'calories'           => '',
            'recipeCategory'     => '',
            'recipeYield'        => '',
            'recipeCuisine'      => '',
            'recipeInstructions' => '',
            'recipeIngredient'  => ''
        ];

        if ($addUid)
        {
            $config['uid'] = $this->uid;
        }

        return $config;
    }

}