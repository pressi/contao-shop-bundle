<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTableFileName   = \IIDO\ShopBundle\Config\BundleConfig::getFileTable( __FILE__ );
$tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
$strTableClass      = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strTableFileName );


$GLOBALS['TL_DCA'][ $strTableFileName ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'File',
        'onload_callback'             => array
        (
            array( $strTableClass, 'loadDataContainer')
        )
    ),



    // Palettes
    'palettes' => array
    (
        '__selector__'      => array(),

        'default'           =>  '{default_legend:hide},' . $tableFieldPrefix . 'enableShopLight,' . $tableFieldPrefix . 'noImageSRC,' . $tableFieldPrefix . 'currency'
                                . ';{cart_legend:hide},' . $tableFieldPrefix . 'addToCartText,' . $tableFieldPrefix . 'addToCartTextConfigurator'
                                . ';{watchlist_legend:hide},' . $tableFieldPrefix . 'addToWatchlistText,' . $tableFieldPrefix . 'addToWatchlistTextConfigurator'

                                . ';{cartEN_legend:hide},' . $tableFieldPrefix . 'addToCartTextEN,' . $tableFieldPrefix . 'addToCartTextConfiguratorEN'
                                . ';{watchlistEN_legend:hide},' . $tableFieldPrefix . 'addToWatchlistTextEN,' . $tableFieldPrefix . 'addToWatchlistTextConfiguratorEN',


        'ai'                => '{access_legend},' . $tableFieldPrefix . 'aiUsername,' . $tableFieldPrefix . 'aiPassword;'
    ),



    // Subpalettes
    'subpalettes' => array(),



    // Fields
    'fields' => array
    (
//        'id'    => array
//        (
//            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
//        ),
//
//        'tstamp' => array
//        (
//            'sql'                     => "int(10) unsigned NOT NULL default '0'"
//        ),
    )
);


// DEFAULT - Default
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField($tableFieldPrefix . 'enableShopLight', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addImageField($tableFieldPrefix . 'noImageSRC', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField($tableFieldPrefix . 'currency', $strTableFileName);


// DEFAULT - Cart
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToCartText', $strTableFileName, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToCartTextConfigurator', $strTableFileName, array(), '', false, true);


// DEFAULT - Watchlist
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToWatchlistText', $strTableFileName, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToWatchlistTextConfigurator', $strTableFileName, array(), '', false, true);


// EN - Cart
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToCartTextEN', $strTableFileName, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToCartTextConfiguratorEN', $strTableFileName, array(), '', false, true);


// EN - Watchlist
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToWatchlistTextEN', $strTableFileName, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField($tableFieldPrefix . 'addToWatchlistTextConfiguratorEN', $strTableFileName, array(), '', false, true);



// AI - Access
\IIDO\BasicBundle\Helper\DcaHelper::addTextField($tableFieldPrefix . 'aiUsername', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField($tableFieldPrefix . 'aiPassword', $strTableFileName);