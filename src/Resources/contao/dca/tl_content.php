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

$objElement         = \IIDO\BasicBundle\Helper\DcaHelper::getTableElement( $strTable );



/**
 * Dynamically add the permission check and parent table
 */
if( Input::get('do') == 'iidoShopProducts')
{
    $GLOBALS['TL_DCA'][ $strTable ]['config']['ptable'] = $categoryTable;
//    $GLOBALS['TL_DCA'][ $strTable ]['list']['sorting']['headerFields'] = array('name', 'alias');
}



/**
 * Extend the default palettes
 */
//TODO: add sorting and filtering to product list element!!
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_shop_productList', '{config_legend},loadProductsFrom;{filter_legend},iidoShopProductsAddFilter;{detail_legend},iidoShopDetailPage;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_shop_productList_v2', '{config_legend},loadProductsFrom;{filter_legend},iidoShopProductsAddFilter;{detail_legend},iidoShopDetailPage;', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('iido_shop_productDetails', '{link_legend},iidoShopCart,iidoShopWatchlist;', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_configurator", '{config_legend},iidoShopCategories,iidoShopArchive,iidoShopRedirect;{link_legend},iidoShopCart,iidoShopWatchlist,iidoShopExtraLink,iidoShopExtraLinkLabel,iidoShopExtraLinkClass;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_configurator_v2", '{config_legend},iidoShopCategories,iidoShopArchive,iidoShopRedirect;{link_legend},iidoShopCart,iidoShopWatchlist,iidoShopExtraLink,iidoShopExtraLinkLabel,iidoShopExtraLinkClass;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_configurator_v3", '{config_legend},iidoShopCategories,iidoShopArchive,iidoShopRedirect;{tuning_legend},iidoShopTuningItemNumber;{link_legend},iidoShopCart,iidoShopWatchlist,iidoShopExtraLink,iidoShopExtraLinkLabel,iidoShopExtraLinkClass;', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_cart", '{link_legend},iidoShopCartLinks,iidoShopCartCheckOutPage,iidoShopCartCheckOutText,iidoShopEditPage,iidoShopConfiguratorPage;{price_legend},iidoShopCartPriceText;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_watchlist", '{link_legend},iidoShopEditPage,iidoShopConfiguratorPage;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_checkout", '{shipping_legend},iidoShopShippings;{payment_legend},iidoShopPayments;{error_legend},showErrorMessagesOnTop;{redirect_legend},iidoShopCartJumpTo,iidoShopForwardJumpTo,iidoShopAGBLink;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_orderOverview", '{redirect_legend},iidoShopCartJumpTo,iidoShopForwardJumpTo;', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_orderComplete", '{redirect_legend},iidoShopCartJumpTo;', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('htmlOpen', '', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addPalette('htmlClose', '', $strTable);


//Contao\CoreBundle\DataContainer\PaletteManipulator::create()
//    ->addLegend('iido_shopCategories_legend', 'title_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
//    ->addField(array('iidoShopCategories'), 'iido_shopCategories_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
//    ->applyToPalette('configurator', $strTable);


/**
 * Extend the default subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('loadProductsFrom_archive', 'iidoShopProductArchive', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('loadProductsFrom_weclapp', 'iidoShopShowProductsFrom,iidoShopProductsShowMode', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('iidoShopShowProductsFrom_categories', 'iidoShopCategories', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('iidoShopShowProductsFrom_itemNumber', 'iidoShopProductItemNumber', $strTable);


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
        'includeBlankOption'    => true,
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

\IIDO\BasicBundle\Helper\DcaHelper::addLinkField('iidoShopAGBLink', $strTable, array(), 'clr');


if( $objElement )
{
    if( $objElement->type === 'iido_shop_orderOverview' )
    {
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'iidoShopCartJumpTo' ]['label'] = $GLOBALS['TL_LANG'][ $strTable ][ 'iidoShopCartJumpTo_overview' ];
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'iidoShopForwardJumpTo' ]['label'] = $GLOBALS['TL_LANG'][ $strTable ][ 'iidoShopForwardJumpTo_overview' ];

    }
    elseif( $objElement->type === 'iido_shop_orderComplete' )
    {
        $GLOBALS['TL_DCA'][ $strTable ]['fields'][ 'iidoShopCartJumpTo' ]['label'] = $GLOBALS['TL_LANG'][ $strTable ][ 'iidoShopCartJumpTo_complete' ];
    }
}


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
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('iidoShopShowProductsFrom', $strTable, array('mandatory'=>true,'includeBlankOption'=>true), '', false, '', false, true);

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('iidoShopProductsShowMode', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('iidoShopProductsAddFilter', $strTable);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopProductItemNumber', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopTuningItemNumber', $strTable);

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


\IIDO\BasicBundle\Helper\DcaHelper::addLinkField('iidoShopExtraLink', $strTable, array(), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopExtraLinkLabel', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('iidoShopExtraLinkClass', $strTable, array(), 'o50');