<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/


list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\ShopBundle\Config\BundleConfig::getBundleConfigArray();

$assetsPath = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath(true, false);
$ns         = $namespace . '\\' . $subNamespace;
$modPrefix  = ucfirst( $subName );

$listenerName = \IIDO\ShopBundle\Config\BundleConfig::getListenerName( true );
$strTableFieldPrefix = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();


// Load icon in Contao 4.2+ backend
if( 'BE' === TL_MODE )
{
    $GLOBALS['TL_CSS'][] = $assetsPath . '/css/backend/contao-shop.css';
}
elseif( 'FE' === TL_MODE )
{
    if( !\Config::get( $strTableFieldPrefix . 'enableShopLight') )
    {
        $GLOBALS['TL_JAVASCRIPT']['iido_shop']  = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath( true, false ) . '/javascript/IIDO.Shop.js|static';
    }

    $GLOBALS['TL_JAVASCRIPT']['cookie']     = \IIDO\BasicBundle\Config\BundleConfig::getBundlePath( true, false ) . '/javascript/cookie.min.js|static';
}

$rootDir    = \IIDO\BasicBundle\Helper\BasicHelper::getRootDir();
$tmpFolder  = '/assets/shop_tmp';

if( !is_dir($rootDir . $tmpFolder) )
{
    mkdir( $rootDir . $tmpFolder );
}



/**
 * API's
 */

$GLOBALS['IIDO']['SHOP']['API'] = array
(
    'weclapp'   => array('token', 'tenant')
);



/**
 * Backend modules
 */

$arrBackendMods = array
(

    $prefix . 'Shop' => array
    (
//        $prefix . 'Products' => array
//        (
//            'callback'      => $ns . '\Backend\Module\ProductModule',
//            'tables'        => array($tablePrefix . 'product', $tablePrefix . 'category', 'tl_content'),
////            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
//        ),

        $prefix . $modPrefix . 'Products' => array
        (
            'tables'        => array($tablePrefix . 'archive', $tablePrefix . 'product', $tablePrefix . 'product_category', 'tl_content'),
            'import'        => array($ns . '\Table\ProductTable', 'renderProductImporter')
        ),


        $prefix . $modPrefix . 'API' => array
        (
            'tables'        => array($tablePrefix . 'api')
        ),

        $prefix . $modPrefix . 'Settings' => array
        (
            'tables'        => array($tablePrefix . 'voucher', $tablePrefix . 'payment', $tablePrefix . 'shipping', $tablePrefix . 'shipping_country_option', $tablePrefix . 'configuration'),
            'callback'      => $ns . '\BackendModule\ShopSettingsModule'
        ),

        $prefix . $modPrefix . 'Statistic' => array
        (
//            'tables'        => array($tablePrefix . 'payment', $tablePrefix . 'shipping', $tablePrefix . 'configuration'),
            'callback'      => $ns . '\BackendModule\ShopStatisticModule'
        ),

    )
);

if( \Config::get( $strTableFieldPrefix . 'enableShopLight') )
{
    unset( $arrBackendMods[ $prefix . 'Shop'][ $prefix . $modPrefix . 'Products'] );
    unset( $arrBackendMods[ $prefix . 'Shop'][ $prefix . $modPrefix . 'API'] );
}

array_insert($GLOBALS['BE_MOD'], 3, $arrBackendMods);



/**
 * Content elements
 */

if( !\Config::get( $strTableFieldPrefix . 'enableShopLight') )
{
    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_configurator']       = 'IIDO\ShopBundle\ContentElement\ConfiguratorElement';
    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_configurator_v2']    = 'IIDO\ShopBundle\ContentElement\ConfiguratorV2Element';
    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_configurator_v3']    = 'IIDO\ShopBundle\ContentElement\ConfiguratorV3Element';

    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_productList']        = 'IIDO\ShopBundle\ContentElement\ProductListElement';
    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_productList_v2']     = 'IIDO\ShopBundle\ContentElement\ProductListV2Element';
}
else
{
    $GLOBALS['TL_CTE']['iido_shop']['iido_shop_product']            = 'IIDO\ShopBundle\ContentElement\ProductElement';
}

$GLOBALS['TL_CTE']['iido_shop']['iido_shop_productDetails']     = 'IIDO\ShopBundle\ContentElement\ProductDetailsElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_cart']               = 'IIDO\ShopBundle\ContentElement\ShopCartElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_watchlist']          = 'IIDO\ShopBundle\ContentElement\WatchlistElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_checkout']           = 'IIDO\ShopBundle\ContentElement\ShopCheckOutElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_orderOverview']      = 'IIDO\ShopBundle\ContentElement\OrderOverviewElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_orderComplete']      = 'IIDO\ShopBundle\ContentElement\OrderCompleteElement';

$GLOBALS['TL_CTE']['html_wrapper']['htmlOpen']                  = 'IIDO\ShopBundle\ContentElement\HTMLWrapperOpenElement';
$GLOBALS['TL_CTE']['html_wrapper']['htmlClose']                 = 'IIDO\ShopBundle\ContentElement\HTMLWrapperCloseElement';



/**
 * Hooks
 */

$GLOBALS['TL_HOOKS']['outputBackendTemplate'][]             = array($listenerName . '.backend_template', 'outputShopBackendTemplate');
//$GLOBALS['TL_HOOKS']['parseBackendTemplate'][]              = array($listenerName . '.backend_template', 'parseShopBackendTemplate');



/**
 * Add permissions
 */

$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopArchives';
$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopArchivep';

$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopProductCategories';
$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopProductCategories_default';

$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopSettings';
$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopStatistic';

//$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopProductCountryOptions';
//$GLOBALS['TL_PERMISSIONS'][] = 'iidoShopProductCountryOptions_default';



/**
 * Add model
 */

$GLOBALS['TL_MODELS']['tl_iido_shop_product']           = 'IIDO\ShopBundle\Model\IidoShopProductModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_product_category']  = 'IIDO\ShopBundle\Model\IidoShopProductCategoryModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_archive']           = 'IIDO\ShopBundle\Model\IidoShopArchiveModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_payment']           = 'IIDO\ShopBundle\Model\IidoShopPaymentModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_shipping']          = 'IIDO\ShopBundle\Model\IidoShopShippingModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_voucher']           = 'IIDO\ShopBundle\Model\IidoShopVoucherModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_statistic_order']   = 'IIDO\ShopBundle\Model\IidoShopStatisticOrderModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_statistic_questionnaire']   = 'IIDO\ShopBundle\Model\IidoShopStatisticQuestionnaireModel';

$GLOBALS['TL_MODELS']['tl_iido_shop_shipping_country_option'] = 'IIDO\ShopBundle\Model\IidoShopShippingCountryOptionModel';



/**
 * Ajax Actions
 */

$GLOBALS['AJAX']['iidoShop'] = array
(
    'actions' => array
    (
        // run in content element: shop cart
        'getPrice' => array
        (
            'arguments'     => array('itemNumber', 'productName', 'quantity'),
            'optional'      => array()
        ),


        'renderPrice' => array
        (
            'arguments'     => array('price', 'useDecimals'),
            'optional'      => array('useDecimals')
        ),


        'getAddToCartMessage' => array
        (
            'arguments'     => array('productName')
        ),
        'getConfiguratorAddToCartMessage' => array
        (
            'arguments'     => array('productName')
        ),


        'getAddToWatchlistMessage' => array
        (
            'arguments'     => array('productName')
        ),
        'getConfiguratorAddToWatchlistMessage' => array
        (
            'arguments'     => array('productName')
        )
    ),
);



/**
 * Wrapper elements
 */

// START
$GLOBALS['TL_WRAPPERS']['start'][]      = 'htmlOpen';


// STOP
$GLOBALS['TL_WRAPPERS']['stop'][]       = 'htmlClose';