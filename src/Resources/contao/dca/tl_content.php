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

$strTable       = 'tl_content';
$categoryTable  = 'tl_iido_shop_product_category';
$archiveTable   = 'tl_iido_shop_archive';


$categoryTableClass = 'IIDO\ShopBundle\Table\ProductCategoryTable';



/**
 * Extend the default palettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addPalette("iido_shop_configurator", '{config_legend},iidoShopCategories,iidoShopArchive,iidoShopRedirect;{link_legend},iidoShopCart,iidoShopWatchlist;', $strTable);
//Contao\CoreBundle\DataContainer\PaletteManipulator::create()
//    ->addLegend('iido_shopCategories_legend', 'title_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
//    ->addField(array('iidoShopCategories'), 'iido_shopCategories_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
//    ->applyToPalette('configurator', $strTable);



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


$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopArchive'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['iidoShopArchive'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'multiple'              => false,
        'tl_class'              => 'w50 hauto'
    ),
    'sql'                     => "blob NULL"
);