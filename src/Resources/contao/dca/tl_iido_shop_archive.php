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

$strTable       = 'tl_iido_shop_archive';
$productTable   = 'tl_iido_shop_product';

$tableClass     = 'IIDO\ShopBundle\Table\ArchiveTable';

$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ctable'                      => array($productTable),
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'onload_callback' => array
        (
            array($tableClass, 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),



    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s'
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
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['edit'],
                'href'                => 'table=' . $productTable,
                'icon'                => 'edit.svg'
            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg',
                'button_callback'     => array($tableClass, 'editHeader')
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg',
                'button_callback'     => array($tableClass, 'copyArchive')
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array($tableClass, 'deleteArchive')
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

        'default'           => '{title_legend},title;'
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

        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'mandatory'     => true,
                'maxlength'     => 255,
                'tl_class'      => 'w50'
            ),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addProtectedFieldsToTable($strTable);