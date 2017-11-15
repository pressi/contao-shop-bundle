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

$strTable           = 'tl_iido_shop_api';
$tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();

$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'     => 'File',
        'closed'            => true
    ),



    // Palettes
    'palettes' => array
    (
        '__selector__'      => array
        (
            $tableFieldPrefix . 'enableWeclappApi'
        ),

        'default'           => '{weclapp_api_legend},' . $tableFieldPrefix . 'enableWeclappApi;'
    ),



    // Subpalettes
    'subpalettes' => array
    (
        $tableFieldPrefix . 'enableWeclappApi'      => $tableFieldPrefix . 'weclappToken,' . $tableFieldPrefix . 'weclappTenant'
    ),



    // Fields
    'fields' => array
    (
        $tableFieldPrefix . 'enableWeclappApi' => array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['enableWeclappApi'],
            'inputType'             => 'checkbox',
            'eval'                  => array
            (
                'submitOnChange'        => true,
                'tl_class'              => 'w50 m12 clr'
            )
        ),

        $tableFieldPrefix . 'weclappToken' => array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['weclappToken'],
            'inputType'             => 'text',
            'eval'                  => array
            (
                'tl_class'              => 'w50',
                'mandatory'             => true
            )
        ),

        $tableFieldPrefix . 'weclappTenant' => array
        (
            'label'                 => &$GLOBALS['TL_LANG'][ $strTable ]['weclappTenant'],
            'inputType'             => 'text',
            'eval'                  => array
            (
                'tl_class'              => 'w50',
                'mandatory'             => true
            )
        )
    )
);