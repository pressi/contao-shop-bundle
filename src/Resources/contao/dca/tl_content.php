<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable       = \IIDO\ShopBundle\Config\BundleConfig::getTableName( __FILE__ ); //'tl_content';
$categoryTable  = \IIDO\ShopBundle\Model\IidoShopProductCategoryModel::getTable(); //'tl_iido_shop_product_category';
$archiveTable   = 'tl_iido_shop_archive';


$categoryTableClass = \IIDO\ShopBundle\Config\BundleConfig::getTableClass( $categoryTable ); //'IIDO\ShopBundle\Table\ProductCategoryTable';
$shippingTableClass = \IIDO\ShopBundle\Config\BundleConfig::getTableClass( \IIDO\ShopBundle\Model\IidoShopShippingModel::getTable() );
$paymentTableClass  = \IIDO\ShopBundle\Config\BundleConfig::getTableClass( \IIDO\ShopBundle\Model\IidoShopPaymentModel::getTable() );
$strTableClass      = 'IIDO\ShopBundle\Table\ContentTable'; //\IIDO\ShopBundle\Config\BundleConfig::getTableClass( $strTable );



/**
 * Extend the default palettes
 */
//TODO: add sorting and filtering to product list element!!
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_shop_productList', '{config_legend},loadProductsFrom;{detail_legend},iidoShopDetailPage;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_shop_productDetails', '{link_legend},iidoShopCart,iidoShopWatchlist;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_configurator", '{config_legend},iidoShopCategories,iidoShopArchive,iidoShopRedirect;{link_legend},iidoShopCart,iidoShopWatchlist;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_cart", '{link_legend},iidoShopCartLinks,iidoShopCartCheckOutPage,iidoShopCartCheckOutText,iidoShopEditPage,iidoShopConfiguratorPage;{price_legend},iidoShopCartPriceText;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_watchlist", '{link_legend},iidoShopEditPage,iidoShopConfiguratorPage;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_checkout", '{shipping_legend},iidoShopShippings;{payment_legend},iidoShopPayments;{error_legend},showErrorMessagesOnTop;{redirect_legend},iidoShopCartJumpTo,iidoShopForwardJumpTo;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_orderOverview", '{redirect_legend},iidoShopCartJumpTo,iidoShopForwardJumpTo;', $strTable);


//Contao\CoreBundle\DataContainer\PaletteManipulator::create()
//    ->addLegend('iido_shopCategories_legend', 'title_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
//    ->addField(array('iidoShopCategories'), 'iido_shopCategories_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
//    ->applyToPalette('configurator', $strTable);


/**
 * Extend the default subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('loadProductsFrom_archive', 'iidoShopProductArchive', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('loadProductsFrom_weclapp', 'iidoShopProductItemNumber', $strTable);


/**
 * Add fields content class
 */

$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopCategories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopCategories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
//    'foreignKey'              => $categoryTable . '.title',
    'options_callback'        => array($categoryTableClass, 'getShopCategories'),
    'eval'                    => array
    (
        'multiple'              => true,
        'tl_class'              => 'w50 hauto'
    ),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopArchive'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopArchive'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'foreignKey'              => $archiveTable . '.title',
//    'options_callback'        => array($categoryTableClass, 'getShopCategories'),
    'eval'                    => array
    (
        'multiple'              => false,
        'tl_class'              => 'w50 hauto'
    ),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('iidoShopRedirect', $strTable, 'jumpTo', 'tl_module');

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('iidoShopCart', $strTable, 'jumpTo', 'tl_module', true, 'w50 hauto');
\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('iidoShopWatchlist', $strTable, 'jumpTo', 'tl_module', true, 'w50 hauto');

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('iidoShopEditPage', $strTable, 'jumpTo', 'tl_module', true);

\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('iidoShopCartPriceText', $strTable, array(), '', false, true);

\IIDO\BasicBundle\Helper\DcaHelper::copyFieldFromTable('iidoShopCartCheckOutPage', $strTable, 'jumpTo', 'tl_module', true, 'clr w50 hauto');
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopCartCheckOutText', $strTable, array(), 'w50');

//\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shippingInfosStore', $strTable, array(), 'w50');
//\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shippingInfosAddress', $strTable, array(), 'w50');

//\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shippingPriceStore', $strTable, array('rgxp'=>'natural'), 'w50');
//\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shippingPriceAddress', $strTable, array('rgxp'=>'natural'), 'w50');

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('showErrorMessagesOnTop', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addPageField('iidoShopCartJumpTo', $strTable, array(), 'clr w50 hauto');
\IIDO\BasicBundle\Helper\DcaHelper::addPageField('iidoShopForwardJumpTo', $strTable, array(), 'w50 hauto');


$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopShippings'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopShippings'],
    'exclude'                 => true,
    'inputType'               => 'checkboxWizard',
    'options_callback'        => array($shippingTableClass, 'getShippings'),
    'eval'                    => array
    (
        'multiple'              => true,
        'tl_class'              => 'w50 hauto'
    ),
    'sql'                     => "blob NULL"
);


$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopPayments']           = $GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopShippings'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopPayments']['label']  = &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopPayments'];
$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopPayments']['options_callback'] = array($paymentTableClass, 'getPayments');


$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopCartLinks'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopCartLinks'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'multiple'              => false,
        'dragAndDrop'           => true,
        'tl_class'              => 'w50 hauto',
        'columnFields'          => array
        (
            'link' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['links']['link'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array
                (
                    'rgxp'              => 'url',
                    'decodeEntities'    => true,
                    'maxlength'         => 255,
                    'dcaPicker'         => true,
//                    'wizard'            => true,
                    'tl_class'          => 'w50 wizard'
                ),
//                'wizard' => array
//                (
//                    array('IIDO\BasicBundle\Table\AllTables', 'pagePicker')
//                )
            ),

            'text' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['links']['text'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array
                (
                    'style' => 'width:200px'
                )
            )
        )
    ),
    'sql'                     => "blob NULL"
);


\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('loadProductsFrom', $strTable, array('mandatory'=>true,'includeBlankOption'=>true), '', false, '', false, true, '', array('options_callback'=>array($strTableClass, 'checkShopApi')));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopProductItemNumber', $strTable);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopProductArchive'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopProductArchive'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array($strTableClass, 'getProductArchives'),
    'eval'                    => array
    (
        'multiple'              => true,
        'mandatory'             => true,
        'tl_class'              => 'w50 hauto'
    ),
    'sql'                     => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addPageField('iidoShopDetailPage', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPageField('iidoShopConfiguratorPage', $strTable);