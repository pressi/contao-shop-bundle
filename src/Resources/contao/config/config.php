<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/


list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\ShopBundle\Config\BundleConfig::getBundleConfigArray();

$assetsPath = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath(true, false);
$ns         = $namespace . '\\' . $subNamespace;
$modPrefix  = ucfirst( $subName );


// Load icon in Contao 4.2+ backend
if ('BE' === TL_MODE)
{
    $GLOBALS['TL_CSS'][] = $assetsPath . '/css/backend/contao-shop.css';
}



/**
 * API's
 */

$GLOBALS['IIDO']['SHOP']['API'] = array
(
    'weclapp'
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

   ),

));



/**
 * Content elements
 */

$GLOBALS['TL_CTE']['iido_shop']['iido_shop_productList']      = 'IIDO\ShopBundle\ContentElement\ProductListElement';
$GLOBALS['TL_CTE']['iido_shop']['iido_shop_configurator']     = 'IIDO\ShopBundle\ContentElement\ConfiguratorElement';



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