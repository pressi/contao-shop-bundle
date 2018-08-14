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


$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'onload_callback'             => array
        (
            array( $tableClass, 'loadPaymentTable' )
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        ),
        'backlink'                    => 'do=iidoShopSettings'
    ),



    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('type', 'name'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('type', 'alias', 'name'),
            'showColumns'             => true,
//            'format'                  => '%s %s',
            'label_callback'          => array($tableClass, 'renderLabel')
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
            'type'
        ),

        'default'           => '{type_legend},type,name,alias;{config_legend},;{info_legend},info;'
    ),



    // Subpalettes
    'subpalettes' => array
    (
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

        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['type'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options_callback'        => array($tableClass, 'getPaymentTypes'),
            'eval'                    => array
            (
                'mandatory'         => true,
                'maxlength'         => 255,
                'tl_class'          => 'w50'
            ),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['name'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'maxlength'         => 255,
                'tl_class'          => 'clr w50',
                'doNotCopy'         => true,
            ),
            'load_callback' => array
            (
                array($tableClass, 'getPaymentName')
            ),

            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'rgxp'              => 'alias',
                'doNotCopy'         => true,
                'unique'            => true,
                'readonly'          => true,

                'maxlength'         => 128,
                'tl_class'          => 'w50'
            ),
            'save_callback' => array
            (
                array($tableClass, 'generateAlias')
            ),
            'load_callback' => array
            (
                array($tableClass, 'getPaymentAlias')
            ),
            'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPublishedFieldsToTable($strTable, 'default', '', 'after', true);
\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('info', $strTable);

$usedFields = array();

foreach(\IIDO\ShopBundle\Helper\PaymentHelper::getAll( true ) as $arrPayment)
{
    if( isset($arrPayment['fields']) )
    {
        foreach( $arrPayment['fields'] as $strField )
        {
            if( !in_array($strField, $usedFields) )
            {
                \IIDO\BasicBundle\Helper\DcaHelper::addTextField( $strField, $strTable );

                $usedFields[] = $strField;
            }
        }

        $strFields  = implode(",", $arrPayment['fields'] );
        $strPalette = $arrPayment['alias'];


        \IIDO\BasicBundle\Helper\DcaHelper::copyPalette( $strPalette, "default", $strTable);
        \IIDO\BasicBundle\Helper\DcaHelper::replacePaletteFields( $strPalette, 'config_legend},', 'config_legend},' . $strFields, $strTable);
    }
}