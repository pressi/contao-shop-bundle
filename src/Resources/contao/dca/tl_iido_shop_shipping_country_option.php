<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable       = \IIDO\ShopBundle\Config\BundleConfig::getTableName( __FILE__ );
$tableClass     = \IIDO\ShopBundle\Config\BundleConfig::getTableClass( $strTable );

$shippingTable      = 'tl_iido_shop_shipping';
$shippingTableClass = \IIDO\ShopBundle\Config\BundleConfig::getTableClass( $shippingTable );

//\Controller::loadDataContainer( $shippingTable );
\Controller::loadLanguageFile( 'countries' );


$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id'    => 'primary'
            )
        ),
        'backlink'                    => 'do=iidoShopSettings&mode=shipping&table=' . $shippingTable
    ),



    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('country'),
            'flag'                    => 11,
            'panelLayout'             => 'search,limit',
//            'disableGrouping'         => true
        ),
        'label' => array
        (
            'fields'                  => array('country'),
//            'group_callback'          => array( $tableClass, 'groupCallback')
//            'format'                  => '%s <span class="gray">[%s]</span>',
//            'showColumns'             => true,
//            'label_callback'          => array($tableClass, 'renderLabel')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),



    // Palettes
    'palettes' => array
    (
        '__selector__'      => array
        (
        ),

        'default'           => '{country_legend},country;{price_legend},addPriceOnShipping;'
    ),



    // Subpalettes
    'subpalettes' => array
    (
        'addPriceOnShipping'        => 'shippingMethod,addToPrice,apiShippingProduct'
    ),



    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPublishedFieldsToTable($strTable, 'default', '', 'after', true);


\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('country', $strTable, array("chosen"=>true,'isAssociative'=>true), '', false, '', false, false, '', array('flag'=>3,'length'=>1,'search'=>true,'options'=>$GLOBALS['TL_LANG']['CNT'],'reference'=>$GLOBALS['TL_LANG']['CNT']));



//PRICE
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('addPriceOnShipping', $strTable, array(), '', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('shippingMethod', $strTable, array('multiple'=>true), '', false, false, '', array('foreignKey' => $shippingTable . '.name', 'relation' => array('type'=>'hasOne', 'load'=>'lazy')));

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('addToPrice', $strTable, array('rgxp'=>'digit'));

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('apiShippingProduct', $strTable, array(), '', false, '', false, false, '', array('options_callback' => array($shippingTableClass, 'getApiShippingArticles')));