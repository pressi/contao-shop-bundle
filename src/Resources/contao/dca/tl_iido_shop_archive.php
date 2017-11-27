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
$categoryTable  = 'tl_iido_shop_product_category';

$tableClass     = 'IIDO\ShopBundle\Table\ArchiveTable';
$categoryClass  = 'IIDO\ShopBundle\Table\ProductCategoryTable';

$bundlePath     = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath( true, false );


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
            array($tableClass, 'checkPermission'),

            array($tableClass, 'checkCategoryPermission'),
            array($tableClass, 'adjustPalette')
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
            'categories' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['categories'],
                'href'                => 'table=' . $categoryTable,
                'icon'                => $bundlePath . '/images/icons/categories.png',
                'class'               => 'header_shop_product_categories',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"'
            ),

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
//            'limitCategories'
        ),

        'default'           => '{title_legend},title;{categories_legend},limitCategories;'
    ),



    // Subpalettes
    'subpalettes' => array
    (
//        'limitCategories'   => 'categories'
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
                'mandatory'         => true,
                'maxlength'         => 255,
                'tl_class'          => 'w50'
            ),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),



        'limitCategories' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['limitCategories'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array
            (
                'submitOnChange'    => true
            ),
            'sql'                     => "char(1) NOT NULL default ''"
        ),

        'categories' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['categories'],
            'exclude'                 => true,
            'inputType'               => 'treePicker',
            'foreignKey'              => $categoryTable . '.title',
            'eval'                    => array
            (
                'mandatory'         => true,
                'multiple'          => true,
                'fieldType'         => 'checkbox',
                'foreignTable'      => $categoryTable,
                'titleField'        => 'title',
                'searchField'       => 'title',
                'managerHref'       => 'do=iidoShopProducts&table=' . $categoryTable
            ),
            'sql'                     => "blob NULL"
        )
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addProtectedFieldsToTable($strTable);

//$objArchive = \Database::getInstance()->prepare("SELECT limitCategories FROM " . $strTable . " WHERE id=?")->limit(1)->execute(\Input::get("id"));

//if( $objArchive && $objArchive->limitCategories )
//{
//    $GLOBALS['TL_DCA'][ $strTable ]['palettes']['default'] = str_replace('limitCategories;', 'limitCategories,categories;', $GLOBALS['TL_DCA'][ $strTable ]['palettes']['default']);
//}