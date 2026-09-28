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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Component\RadicalSchema\Administrator\Type\FormFieldsAwareType;
use Joomla\Component\RadicalSchema\Administrator\Type\MicrodataType;

/**
 * Schema.org Product type with Offer / AggregateOffer, Review and AggregateRating.
 *
 * @source https://developers.google.com/search/docs/appearance/structured-data/product-snippet
 *
 * @since  __DEPLOY_VERSION__
 */
class Product implements MicrodataType, FormFieldsAwareType
{
    /**
     * Offer type: single price.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const OFFER = 'Offer';

    /**
     * Offer type: price range from several prices.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const AGGREGATE_OFFER = 'AggregateOffer';

    /**
     * Return policy: returns not permitted.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_NOT_PERMITTED = 'MerchantReturnNotPermitted';

    /**
     * Return policy: link to the refund policy page.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_LINK = 'MerchantReturnLink';

    /**
     * Return policy: returns allowed within a number of days.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_FINITE = 'MerchantReturnFiniteReturnWindow';

    /**
     * Return policy: returns allowed without time limit.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_UNLIMITED = 'MerchantReturnUnlimitedWindow';

    /**
     * Return policy categories (returnPolicyCategory values).
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_CATEGORIES = [self::RETURN_FINITE, self::RETURN_UNLIMITED, self::RETURN_NOT_PERMITTED];

    /**
     * Return policy categories where returns are allowed (fees and method are applicable).
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_ALLOWED = [self::RETURN_FINITE, self::RETURN_UNLIMITED];

    /**
     * Return fees values (returnFees).
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_FEES = [
        'FreeReturn'                       => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNFEES_FREE',
        'ReturnFeesCustomerResponsibility' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNFEES_CUSTOMER',
        'ReturnShippingFees'               => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNFEES_SHIPPING',
    ];

    /**
     * Refund type values (refundType).
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    public const REFUND_TYPES = [
        'FullRefund'        => 'COM_RADICALSCHEMA_PARAM_SCHEMA_REFUNDTYPE_FULL',
        'ExchangeRefund'    => 'COM_RADICALSCHEMA_PARAM_SCHEMA_REFUNDTYPE_EXCHANGE',
        'StoreCreditRefund' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_REFUNDTYPE_STORECREDIT',
    ];

    /**
     * Return method values (returnMethod).
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_METHODS = [
        'ReturnByMail'  => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNMETHOD_MAIL',
        'ReturnInStore' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNMETHOD_STORE',
        'ReturnAtKiosk' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNMETHOD_KIOSK',
    ];

    /**
     * Return policy: take from the basic settings of the component.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_GLOBAL = 'global';

    /**
     * Return policy: not used.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const RETURN_NONE = 'none';

    /**
     * Unique id of the schema node.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private $uid = 'radicalschema.schema.page';

    /**
     * Builds the Product schema data.
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
        $item = (object) array_merge($this->getConfig(), (array) $item);

        $description = !empty($item->description) && \is_scalar($item->description) ? trim((string) $item->description) : '';

        if ($description === '')
        {
            try
            {
                $description = trim((string) Factory::getApplication()->getDocument()->getDescription());
            }
            catch (\Throwable $e)
            {
                $description = '';
            }
        }

        $data = [
            'uid'      => $this->uid,
            'priority' => $priority,
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $item->title ? ValueHelper::prepareText($item->title, 110) : '',
        ];

        if ($description !== '')
        {
            $data['description'] = ValueHelper::prepareText($description, 5000);
        }

        // Sku
        if (!empty($item->sku))
        {
            $data['sku'] = $item->sku;
        }

        // MPN
        if (!empty($item->mpn))
        {
            $data['mpn'] = $item->mpn;
        }

        // Brand
        if (!empty($item->brand))
        {
            $data['brand'] = [
                '@type' => 'Brand',
                'name'  => $item->brand
            ];
        }

        // Image: main image + gallery (list of URLs), or only main image
        $gallery = [];

        foreach ((array) $item->gallery as $row)
        {
            $src = ((array) $row)['image'] ?? '';

            if (\is_scalar($src) && trim((string) $src) !== '')
            {
                $gallery[] = ValueHelper::prepareLink(trim((string) $src));
            }
        }

        $mainImage = !empty($item->image) && \is_scalar($item->image) ? ValueHelper::prepareLink($item->image) : '';

        if ($gallery)
        {
            $data['image'] = array_values(array_unique(array_filter(array_merge([$mainImage], $gallery))));
        }
        elseif ($mainImage !== '')
        {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => $mainImage
            ];
        }

        // Offers
        if ($offers = $this->getOffers($item))
        {
            $data['offers'] = $offers;
        }

        // Aggregate rating
        if ($aggregateRating = $this->getAggregateRating($item))
        {
            $data['aggregateRating'] = $aggregateRating;
        }

        // Review
        if ($review = $this->getReview($item))
        {
            $data['review'] = $review;
        }

        return $data;
    }

    /**
     * Builds Offer or AggregateOffer data.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getOffers(object $item): ?array
    {
        $currency = \is_scalar($item->currency) ? trim((string) $item->currency) : '';

        if ($currency === '')
        {
            return null;
        }

        // AggregateOffer: several prices, offerCount is calculated automatically
        if ($item->offerType === self::AGGREGATE_OFFER)
        {
            $prices = [];

            foreach ((array) $item->offerPrices as $row)
            {
                $price = ValueHelper::prepareNumber(((array) $row)['price'] ?? null);

                if ($price !== null)
                {
                    $prices[] = $price;
                }
            }

            if (empty($prices))
            {
                return null;
            }

            return $this->addOfferDetails([
                '@type'         => self::AGGREGATE_OFFER,
                'url'           => Uri::current(),
                'priceCurrency' => $currency,
                'lowPrice'      => min($prices),
                'highPrice'     => max($prices),
                'offerCount'    => \count($prices),
            ], $item, $currency);
        }

        // Offer: single price
        $price = ValueHelper::prepareNumber($item->price);

        if ($price === null)
        {
            return null;
        }

        return $this->addOfferDetails([
            '@type'         => self::OFFER,
            'url'           => Uri::current(),
            'priceCurrency' => $currency,
            'price'         => $price,
        ], $item, $currency);
    }

    /**
     * Adds availability, itemCondition, shippingDetails and hasMerchantReturnPolicy to an offer.
     *
     * @param   array   $offer     Offer data.
     * @param   object  $item      Resolved mapping values.
     * @param   string  $currency  Offer currency.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function addOfferDetails(array $offer, object $item, string $currency): array
    {
        // Availability: InStock, OutOfStock, PreOrder...
        if ($availability = $this->prepareEnum($item->availability))
        {
            $offer['availability'] = $availability;
        }

        // Condition: NewCondition, UsedCondition...
        if ($itemCondition = $this->prepareEnum($item->itemCondition))
        {
            $offer['itemCondition'] = $itemCondition;
        }

        // Shipping details
        $shippingRate    = ValueHelper::prepareNumber($item->shippingRate);
        $shippingCountry = $this->prepareCountries($item->shippingCountry);

        if ($shippingRate !== null && $shippingCountry)
        {
            $shipping = [
                '@type'               => 'OfferShippingDetails',
                'shippingRate'        => [
                    '@type'    => 'MonetaryAmount',
                    'value'    => $shippingRate,
                    'currency' => $currency,
                ],
                'shippingDestination' => [
                    '@type'          => 'DefinedRegion',
                    'addressCountry' => $shippingCountry,
                ],
            ];

            $handlingTime = $this->prepareDays($item->handlingTime);
            $transitTime  = $this->prepareDays($item->transitTime);

            if ($handlingTime || $transitTime)
            {
                $shipping['deliveryTime'] = ['@type' => 'ShippingDeliveryTime'];

                if ($handlingTime)
                {
                    $shipping['deliveryTime']['handlingTime'] = $handlingTime;
                }

                if ($transitTime)
                {
                    $shipping['deliveryTime']['transitTime'] = $transitTime;
                }
            }

            $offer['shippingDetails'] = $shipping;
        }

        // Return policy: from the product or from the basic settings
        $returnPolicy  = \is_scalar($item->returnPolicy) ? (string) $item->returnPolicy : '';
        $returnCountry = $item->returnCountry;
        $returnDays    = $item->returnDays;
        $returnLink    = $item->returnLink;
        $returnFees    = $item->returnFees;
        $returnFeesSum = $item->returnShippingFees;
        $returnMethod  = $item->returnMethod;
        $refundType    = $item->refundType;

        if ($returnPolicy === '' || $returnPolicy === self::RETURN_GLOBAL)
        {
            $params        = ParamsHelper::getComponentParams();
            $returnPolicy  = (string) $params->get('schema_return_policy', self::RETURN_NONE);
            $returnCountry = $params->get('schema_return_country', '');
            $returnDays    = $params->get('schema_return_days', '');
            $returnLink    = $params->get('schema_return_link', '');
            $returnFees    = $params->get('schema_return_fees', '');
            $returnFeesSum = $params->get('schema_return_shipping_fees', '');
            $returnMethod  = $params->get('schema_return_method', '');
            $refundType    = $params->get('schema_refund_type', '');
        }

        // Category: finite window (days), unlimited window, not permitted
        if (\in_array($returnPolicy, self::RETURN_CATEGORIES, true))
        {
            $policy = ['@type' => 'MerchantReturnPolicy'];

            if ($countries = $this->prepareCountries($returnCountry))
            {
                $policy['applicableCountry'] = $countries;
            }

            $policy['returnPolicyCategory'] = 'https://schema.org/' . $returnPolicy;

            if ($returnPolicy === self::RETURN_FINITE && ($days = (int) ValueHelper::prepareNumber($returnDays)) > 0)
            {
                $policy['merchantReturnDays'] = $days;
            }

            // Link to the return policy page (optional)
            if ($link = $this->prepareReturnLink($returnLink))
            {
                $policy['merchantReturnLink'] = $link;
            }

            // Fees and method only when returns are allowed
            if (\in_array($returnPolicy, self::RETURN_ALLOWED, true))
            {
                if (\is_string($returnMethod) && isset(self::RETURN_METHODS[$returnMethod]))
                {
                    $policy['returnMethod'] = 'https://schema.org/' . $returnMethod;
                }

                if (\is_string($refundType) && isset(self::REFUND_TYPES[$refundType]))
                {
                    $policy['refundType'] = 'https://schema.org/' . $refundType;
                }

                if (\is_string($returnFees) && isset(self::RETURN_FEES[$returnFees]))
                {
                    $policy['returnFees'] = 'https://schema.org/' . $returnFees;

                    $amount = ValueHelper::prepareNumber($returnFeesSum);

                    if ($returnFees === 'ReturnShippingFees' && $amount !== null)
                    {
                        $policy['returnShippingFeesAmount'] = [
                            '@type'    => 'MonetaryAmount',
                            'value'    => $amount,
                            'currency' => $currency,
                        ];
                    }
                }
            }

            $offer['hasMerchantReturnPolicy'] = $policy;
        }
        elseif ($returnPolicy === self::RETURN_LINK && ($link = $this->prepareReturnLink($returnLink)))
        {
            $offer['hasMerchantReturnPolicy'] = [
                '@type'              => 'MerchantReturnPolicy',
                'merchantReturnLink' => $link,
            ];
        }

        return $offer;
    }

    /**
     * Converts a menu item id (or a direct link) to an absolute URL.
     *
     * @param   mixed  $value  Menu item id or URL.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareReturnLink($value): string
    {
        if (!\is_scalar($value) || trim((string) $value) === '')
        {
            return '';
        }

        $value = trim((string) $value);

        // Menu item
        if (ctype_digit($value))
        {
            $menuItem = Factory::getApplication()->getMenu()->getItem((int) $value);

            if (!$menuItem)
            {
                return '';
            }

            return Route::_('index.php?Itemid=' . (int) $value, false, Route::TLS_IGNORE, true);
        }

        return ValueHelper::prepareLink($value);
    }

    /**
     * Converts a short enumeration value ("InStock") to a schema.org URL.
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareEnum($value): string
    {
        if (!\is_scalar($value))
        {
            return '';
        }

        $value = trim(strip_tags((string) $value));

        if ($value === '' || stripos($value, 'http') === 0)
        {
            return $value;
        }

        $value = preg_replace('#^(schema\.org/)#i', '', $value);

        return preg_match('/^[A-Za-z]+$/', $value) ? 'https://schema.org/' . $value : '';
    }

    /**
     * Converts a comma separated country list ("RU, BY") to a string or array of ISO codes.
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  string|array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareCountries($value)
    {
        if (!\is_scalar($value))
        {
            return '';
        }

        $countries = array_values(array_unique(array_filter(array_map(
            static fn ($country) => strtoupper(trim($country)),
            explode(',', (string) $value)
        ))));

        if (\count($countries) > 1)
        {
            return $countries;
        }

        return $countries[0] ?? '';
    }

    /**
     * Converts days value ("1-3" or "2") to a QuantitativeValue.
     *
     * @param   mixed  $value  Raw value.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function prepareDays($value): ?array
    {
        if (!\is_scalar($value) || !preg_match_all('/\d+/', (string) $value, $matches))
        {
            return null;
        }

        $numbers = array_map('intval', $matches[0]);

        return [
            '@type'    => 'QuantitativeValue',
            'minValue' => min($numbers),
            'maxValue' => max($numbers),
            'unitCode' => 'DAY',
        ];
    }

    /**
     * Builds AggregateRating data.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getAggregateRating(object $item): ?array
    {
        $ratingValue = ValueHelper::prepareNumber($item->ratingValue);
        $reviewCount = (int) ValueHelper::prepareNumber($item->reviewCount);

        // Google requires ratingValue and a positive reviewCount
        if ($ratingValue === null || $reviewCount <= 0)
        {
            return null;
        }

        // Average rating is taken as is (e.g. 4.5)
        $result = [
            '@type'       => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'reviewCount' => $reviewCount,
        ];

        if ($bestRating = ValueHelper::prepareNumber($item->bestRating))
        {
            $result['bestRating'] = $bestRating;
        }

        // Worst rating: 1 by default
        $result['worstRating'] = ValueHelper::prepareNumber($item->worstRating) ?? 1;

        return $result;
    }

    /**
     * Builds Review data.
     *
     * @param   object  $item  Resolved mapping values.
     *
     * @return  array|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getReview(object $item): ?array
    {
        $author = \is_scalar($item->reviewAuthor) ? trim(strip_tags((string) $item->reviewAuthor)) : '';
        $rating = ValueHelper::prepareNumber($item->reviewRating);

        // Google requires author and reviewRating
        if ($author === '' || $rating === null)
        {
            return null;
        }

        $result = [
            '@type'        => 'Review',
            'author'       => [
                '@type' => 'Person',
                'name'  => $author
            ],
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => $rating,
            ],
        ];

        if ($bestRating = ValueHelper::prepareNumber($item->bestRating))
        {
            $result['reviewRating']['bestRating'] = $bestRating;
        }

        $result['reviewRating']['worstRating'] = ValueHelper::prepareNumber($item->worstRating) ?? 1;

        return $result;
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
            'title'        => '',
            'description'  => '',
            'image'        => '',
            'gallery'      => [],
            'sku'          => '',
            'mpn'          => '',
            'brand'        => '',
            'currency'     => '',
            'offerType'    => self::OFFER,
            'price'        => '',
            'offerPrices'  => [],
            'availability'    => '',
            'itemCondition'   => '',
            'shippingRate'    => '',
            'shippingCountry' => '',
            'handlingTime'    => '',
            'transitTime'     => '',
            'returnPolicy'    => '',
            'returnCountry'   => '',
            'returnDays'      => '',
            'returnFees'         => '',
            'returnShippingFees' => '',
            'returnMethod'       => '',
            'refundType'         => '',
            'returnLink'      => '',
            'ratingValue'  => '',
            'reviewCount'  => '',
            'bestRating'   => '',
            'worstRating'  => '',
            'reviewAuthor' => '',
            'reviewRating' => '',
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
            'offerType'   => [
                'type'    => 'list',
                'default' => self::OFFER,
                'options' => [
                    self::OFFER           => 'COM_RADICALSCHEMA_PARAM_SCHEMA_OFFERTYPE_OFFER',
                    self::AGGREGATE_OFFER => 'COM_RADICALSCHEMA_PARAM_SCHEMA_OFFERTYPE_AGGREGATEOFFER',
                ],
            ],
            'price'       => [
                'showon' => ['offerType' => [self::OFFER]],
            ],
            'gallery'     => [
                'type'   => 'subform',
                'min'    => 0,
                'max'    => 20,
                'fields' => [
                    'image' => [
                        'type'  => 'media',
                        'label' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_IMAGE',
                    ],
                ],
            ],
            'offerPrices' => [
                'type'   => 'subform',
                'min'    => 1,
                'max'    => 10,
                'showon' => ['offerType' => [self::AGGREGATE_OFFER]],
                'fields' => [
                    'price' => ['label' => 'COM_RADICALSCHEMA_PARAM_SCHEMA_PRICE'],
                ],
            ],
            'availability'  => [
                'addoptions' => [
                    'InStock', 'OutOfStock', 'PreOrder', 'BackOrder', 'LimitedAvailability',
                    'OnlineOnly', 'InStoreOnly', 'SoldOut', 'Discontinued',
                ],
            ],
            'itemCondition' => [
                'addoptions' => ['NewCondition', 'UsedCondition', 'RefurbishedCondition', 'DamagedCondition'],
            ],
            'returnPolicy'  => [
                'type'    => 'list',
                'default' => self::RETURN_GLOBAL,
                'options' => [
                    self::RETURN_GLOBAL        => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNPOLICY_GLOBAL',
                    self::RETURN_NONE          => 'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT',
                    self::RETURN_FINITE        => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNPOLICY_FINITE',
                    self::RETURN_UNLIMITED     => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNPOLICY_UNLIMITED',
                    self::RETURN_NOT_PERMITTED => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNPOLICY_NOT_PERMITTED',
                    self::RETURN_LINK          => 'COM_RADICALSCHEMA_PARAM_SCHEMA_RETURNPOLICY_LINK',
                ],
            ],
            'returnCountry' => [
                'showon' => ['returnPolicy' => self::RETURN_CATEGORIES],
            ],
            'returnDays'    => [
                'showon' => ['returnPolicy' => [self::RETURN_FINITE]],
            ],
            'returnFees'    => [
                'type'    => 'list',
                'default' => self::RETURN_NONE,
                'options' => [self::RETURN_NONE => 'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT'] + self::RETURN_FEES,
                'showon'  => ['returnPolicy' => self::RETURN_ALLOWED],
            ],
            'returnShippingFees' => [
                'showon' => ['returnPolicy' => self::RETURN_ALLOWED, 'returnFees' => ['ReturnShippingFees']],
            ],
            'returnMethod'  => [
                'type'    => 'list',
                'default' => self::RETURN_NONE,
                'options' => [self::RETURN_NONE => 'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT'] + self::RETURN_METHODS,
                'showon'  => ['returnPolicy' => self::RETURN_ALLOWED],
            ],
            'refundType'    => [
                'type'    => 'list',
                'default' => self::RETURN_NONE,
                'options' => [self::RETURN_NONE => 'COM_RADICALSCHEMA_GROUP_EXTRA_OPTION_NO_SELECT'] + self::REFUND_TYPES,
                'showon'  => ['returnPolicy' => self::RETURN_ALLOWED],
            ],
            'returnLink'    => [
                'type'   => 'modal_menu',
                'showon' => ['returnPolicy' => array_merge(self::RETURN_CATEGORIES, [self::RETURN_LINK])],
            ],
        ];
    }
}
