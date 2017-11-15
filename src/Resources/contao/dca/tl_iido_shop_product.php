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

$strTable       = 'tl_iido_shop_product';
$archiveTable   = 'tl_iido_shop_archive';

$tableClass     = 'IIDO\ShopBundle\Table\ProductTable';

$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => $archiveTable,
//        'ctable'                      => array('tl_content'),
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
                'id' => 'primary',
                'alias' => 'index',
                'pid' => 'index'
            )
        )
    ),



    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('name DESC'),
            'headerFields'            => array('title', 'tstamp', 'protected'),
            'panelLayout'             => 'filter;sort,search,limit',
            'child_record_callback'   => array($tableClass, 'listProductArticles'),
            'child_record_class'      => 'no_padding'
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
//            'edit' => array
//            (
//                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['edit'],
//                'href'                => 'table=tl_content',
//                'icon'                => 'edit.svg'
//            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['editmeta'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.svg'
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable]['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['toggle'],
                'icon'                => 'visible.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array($tableClass, 'toggleIcon')
            ),
            'feature' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['feature'],
                'icon'                => 'featured.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFeatured(this,%s)"',
                'button_callback'     => array($tableClass, 'iconFeatured')
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable]['show'],
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

        'default'           => '{name_legend},name,alias,itemNumber;'
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

        'pid' => array
        (
            'foreignKey'              => $archiveTable . '.title',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'belongsTo', 'load'=>'eager')
        ),

        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),

        'name' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['name'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'mandatory'         => true,
                'maxlength'         => 255,
                'tl_class'          => 'w50'
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
                'maxlength'         => 128,
                'tl_class'          => 'w50 clr'
            ),
            'save_callback' => array
            (
                array($tableClass, 'generateAlias')
            ),
            'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),

        'itemNumber' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['itemNumber'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => array
            (
                'mandatory'         => true,
                'maxlength'         => 255,
                'tl_class'          => 'w50'
            ),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),



        'featured' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['featured'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array
            (
                'tl_class'          => 'w50'
            ),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPublishedFieldsToTable( $strTable );