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
            'fields'                  => array('name', 'code'),
//            'showColumns'             => true,
            'format'                  => '%s <span class="gray">[%s]</span>',
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
            'type'
        ),

        'default'           => 'isVoucherUsed;{type_legend},name,alias;{mode_legend},mode;{code_legend},code;{rights_legend},userEmails;'
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



/**
 * Subpalettes
 */

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('mode_percent', 'percentDiscount', $strTable);
\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('mode_amount', 'priceDiscount', $strTable);



/**
 * Fields
 */

\IIDO\BasicBundle\Helper\DcaHelper::addPublishedFieldsToTable($strTable, 'default', '', 'after', true);

\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('mode', $strTable, array('includeBlankOption'=>true,'mandatory'=>true), '', false, '', false, true);
//\IIDO\BasicBundle\Helper\DcaHelper::addTextField('userEmails', $strTable);
$GLOBALS['TL_DCA'][ $strTable ]['fields']['userEmails'] = array
(
    'label'         => &$GLOBALS['TL_LANG'][ $strTable ]['userEmails'],
    'exclude'       => true,
    'inputType'     => 'multiColumnWizard',
    'eval'          => array
    (
        'disableSorting' => true,
        'generateTableless' => false,
        'columnFields' => array
        (
            'email'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['userEmails']['email'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array
                (
                    'mandatory'             => true,
                    'rgxp'                  => 'email',
                    'style'                 => 'width: 280px'
                )
            ),

            'hidden' => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['userEmails']['hidden'],
                'exclude'   => true,
                'inputType' => 'checkbox',
            ),

            'used' => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['userEmails']['used'],
                'exclude'   => true,
                'readonly'  => true,
                'disabled'  => true,
                'inputType' => 'checkbox',
            ),

            'usedDate' => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['userEmails']['usedDate'],
                'exclude'   => true,
                'readonly'  => true,
                'disabled'  => true,
                'inputType' => 'text',
            )
        )
    ),
    'sql'           => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('code', $strTable, array('mandatory'=>true,'doNotCopy'=>true,'unique'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addAddonTextField('percentDiscount', $strTable, '%', array('rgxp'=>'prcnt','mandatory'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addAddonTextField('priceDiscount', $strTable, \IIDO\ShopBundle\Config\ShopConfig::getCurrency(), array('rgxp'=>'digit','mandatory'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('used', $strTable, array('readonly'=>true));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('usedDate', $strTable, array('readonly'=>true));

\IIDO\BasicBundle\Helper\DcaHelper::addBlobField('isVoucherUsed', $strTable, '', array(), '', false, array('input_field_callback'=>array($tableClass, 'renderIsVoucherUsedField')), true);