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


// Load icon in Contao 4.2+ backend
if( 'BE' === TL_MODE )
{
    $GLOBALS['TL_CSS'][] = $assetsPath . '/css/backend/contao-shop.css';
}
elseif( 'FE' === TL_MODE )
{
    $GLOBALS['TL_JAVASCRIPT']['iido_shop']  = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath( true ) . '/javascript/IIDO.Shop.js|static';
    $GLOBALS['TL_JAVASCRIPT']['cookie']     = \IIDO\BasicBundle\Config\BundleConfig::getBundlePath( true ) . '/javascript/cookie.min.js|static';
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

array_insert($GLOBALS['BE_MOD'], 3, array
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
            'tables'        => array($tablePrefix . 'archive', $tablePrefix . 'product', $tablePrefix . 'product_category'),
            'import'        => array($ns . '\Table\ProductTable', 'renderProductImporter')
        ),


        $prefix . $modPrefix . 'API' => array
        (
            'tables'        => array($tablePrefix . 'api')
        ),

        $prefix . $modPrefix . 'Settings' => array
        (
            'tables'        => array($tablePrefix . 'payment', $tablePrefix . 'shipping', $tablePrefix . 'configuration'),
            'callback'      => $ns . '\BackendModule\ShopSettingsModule'
        ),

   ),

));



/**
 * Content elements
 */

$GLOBALS['TL_CTE']['iido_shop']['iido_shop_productList']        = 'IIDO\ShopBundle\ContentElement\ProductListElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_productDetails']     = 'IIDO\ShopBundle\ContentElement\ProductDetailsElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_configurator']       = 'IIDO\ShopBundle\ContentElement\ConfiguratorElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_cart']               = 'IIDO\ShopBundle\ContentElement\ShopCartElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_watchlist']          = 'IIDO\ShopBundle\ContentElement\WatchlistElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_checkout']           = 'IIDO\ShopBundle\ContentElement\ShopCheckOutElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_orderOverview']      = 'IIDO\ShopBundle\ContentElement\OrderOverviewElement';



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



/**
 * Add model
 */

$GLOBALS['TL_MODELS']['tl_iido_shop_product']           = 'IIDO\ShopBundle\Model\IidoShopProductModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_product_category']  = 'IIDO\ShopBundle\Model\IidoShopProductCategoryModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_archive']           = 'IIDO\ShopBundle\Model\IidoShopArchiveModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_payment']           = 'IIDO\ShopBundle\Model\IidoShopPaymentModel';
$GLOBALS['TL_MODELS']['tl_iido_shop_shipping']          = 'IIDO\ShopBundle\Model\IidoShopShippingModel';



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