<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
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
        '__selector__'      => array(),
        'default'           => ''
    ),



    // Subpalettes
    'subpalettes' => array(),



    // Fields
    'fields' => array()
);

foreach($GLOBALS['IIDO']['SHOP']['API'] as $strApi => $arrFields)
{
    $strApiName         = ucfirst($strApi);
    $enableLabel        = 'enable' . $strApiName . 'Api';
    $enableFieldName    = $tableFieldPrefix . $enableLabel;

    $GLOBALS['TL_DCA'][ $strTable ]['palettes']['default'] .= '{' . $strApi . '_api_legend},' . $tableFieldPrefix . 'enable' . $strApiName . 'Api;';

    $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $enableFieldName ] = array
    (
        'label'                 => &$GLOBALS['TL_LANG'][ $strTable ][ $enableLabel ],
        'inputType'             => 'checkbox',
        'eval'                  => array
        (
            'submitOnChange'        => true,
            'tl_class'              => 'w50 m12 clr'
        )
    );

    if( is_array($arrFields) && count($arrFields) )
    {
        $GLOBALS['TL_DCA'][ $strTable ]['palettes']['__selector__'][] = $tableFieldPrefix . 'enable' . $strApiName . 'Api';

        $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $enableFieldName ] = '';

        foreach($arrFields as $num => $strField)
        {
            $fieldLabel = $strApi . ucfirst($strField);
            $fieldName  = $tableFieldPrefix . $fieldLabel;

            $GLOBALS['TL_DCA'][ $strTable ]['fields'][ $fieldName ] = array
            (
                'label'                 => &$GLOBALS['TL_LANG'][ $strTable ][ $fieldLabel ],
                'inputType'             => 'text',
                'eval'                  => array
                (
                    'tl_class'              => 'w50',
                    'mandatory'             => true
                )
            );

            $GLOBALS['TL_DCA'][ $strTable ]['subpalettes'][ $enableFieldName ] .= (($num > 0) ? ',' : '') . $fieldName;
        }
    }
}