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

$strTable           = 'tl_iido_shop_product_category';
$archiveTable       = 'tl_iido_shop_archive';

$tableClass         = 'IIDO\ShopBundle\Table\ProductCategoryTable';
$bundlePath         = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath( true );

Controller::loadLanguageFile( $archiveTable );


$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'label'                       => $GLOBALS['TL_LANG'][ $archiveTable ]['categories'][0],
        'dataContainer'               => 'Table',
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
                'pid' => 'index',
                'alias' => 'index',
            )
        ),
        'backlink'                    => 'do=iidoShopProducts'
    ),



    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 5,
            'icon'                    => $bundlePath . '/images/icons/categories.png',
            'paste_button_callback'   => array($tableClass, 'pasteCategory'),
            'panelLayout'             => 'search'
        ),
        'label' => array
        (
            'fields'                  => array('title', 'frontendTitle'),
            'format'                  => '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>',
            'label_callback'          => array($tableClass, 'generateLabel')
        ),
        'global_operations' => array
        (
            'toggleNodes' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href'                => 'ptg=all',
                'class'               => 'header_toggle'
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'copyChilds' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['copyChilds'],
                'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon'                => 'copychilds.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array($tableClass, 'toggleIcon')
            )
        )
    ),



    // Palettes
    'palettes' => array
    (
        '__selector__'      => array
        (
        ),

        'default'           => '{title_legend},title,alias,frontendTitle,cssClass;{settings_legend},color,singleSRC;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo;{publish_legend},published'
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
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
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
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'frontendTitle' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['frontendTitle'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),

        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array($tableClass, 'generateAlias')
            ),
            'sql'                     => "varbinary(128) NOT NULL default ''"
        ),

        'cssClass' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['cssClass'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>128, 'tl_class'=>'w50'),
            'sql'                     => "varchar(128) NOT NULL default ''",
        ),



        'hideInList' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['hideInList'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''",
        ),

        'hideInReader' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['hideInReader'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''",
        ),

        'excludeInRelated' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['excludeInRelated'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''",
        ),



        'jumpTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['jumpTo'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'eval'                    => array('fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager', 'table'=>'tl_page')
        ),



        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG'][ $strTable ]['published'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);


\IIDO\BasicBundle\Helper\DcaHelper::addColorField("color", $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addImageField("singleSRC", $strTable);