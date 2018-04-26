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
            'fields'                  => array('name'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('name', 'frontendTitle', 'price'),
//            'format'                  => '%s <span class="gray">[%s]</span>',
            'showColumns'             => true,
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

        'default'           => '{type_legend},name,alias,frontendTitle;{info_legend},info;{price_legend},price;'
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

                'maxlength'         => 128,
                'tl_class'          => 'w50'
            ),
            'save_callback' => array
            (
                array($tableClass, 'generateAlias')
            ),
            'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPublishedFieldsToTable($strTable, 'default', '', 'after', true);

\IIDO\BasicBundle\Helper\DcaHelper::addTextareaField('info', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('price', $strTable, array('rgxp'=>'digit'));

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('name', $strTable, array('doNotCopy' => true), '', false, '', array('search' => true));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('frontendTitle', $strTable, array('doNotCopy' => true));