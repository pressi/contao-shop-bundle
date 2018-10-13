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

$bundlePath     = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath( true, false );

$countryOptionsTable = $strTable . '_country_option';

$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'onload_callback' => array
        (
//            array($tableClass, 'checkPermission'),

//            array($tableClass, 'checkCountryOptionPermission'),
//            array($tableClass, 'adjustPalette')
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
            'fields'                  => array('name'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('name', 'frontendTitle', 'price'),
//            'format'                  => '%s <span class="gray">[%s]</span>',
            'showColumns'             => true,
            'label_callback'          => array($tableClass, 'renderLabel')
        ),
        'global_operations' => array
        (
            'countryOptions' => array
            (
                'label'               => &$GLOBALS['TL_LANG'][ $strTable ]['countryOptions'],
                'href'                => 'table=' . $countryOptionsTable,
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

        'default'           => '{type_legend},name,alias,frontendTitle;{api_legend},;{info_legend},info;{add_legend},useShippingPerCountry,linkPaymentMethod;{price_legend},price,enablePricePerCountry,freeOnPriceLimit;'
    ),



    // Subpalettes
    'subpalettes' => array
    (
        'enablePricePerCountry'             => 'pricePerCountry',
        'linkPaymentMethod'                 => 'linkedPaymentMethod',

        'useShippingPerCountry_enable'      => 'shippingPerCountry',
        'useShippingPerCountry_disable'     => 'shippingPerCountry',

        'freeOnPriceLimit'                  => 'freeOnCartPrice,freeOnlyPerCountry'
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



// API
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('apiMethod', $strTable, array(), '', false, '', false, false, '', array('options_callback'=>array($tableClass, 'getApiMethods')));


if( \IIDO\ShopBundle\Helper\ApiHelper::enableApis() )
{
    \IIDO\BasicBundle\Helper\DcaHelper::replacePaletteFields('default', '{api_legend},', '{api_legend},apiMethod', $strTable);
}



// ADD
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('useShippingPerCountry', $strTable, array('includeBlankOption'=>true), 'clr', false, '', false, true);


$GLOBALS['TL_DCA'][ $strTable ]['fields']['shippingPerCountry'] = array
(
    'label'         => &$GLOBALS['TL_LANG'][ $strTable ]['shippingPerCountry'],
    'exclude'       => true,
    'inputType'     => 'multiColumnWizard',
    'eval'          => array
    (
        'columnFields' => array
        (
            'country'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['shippingPerCountry']['country'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback'   => array($tableClass, 'getShippingCountries'),
                'eval'      => array
                (
                    'mandatory'             => true,
                    'includeBlankOption'    => true,
                    'chosen'                => true,
                    'style'                 => 'width: 320px'
                )
            )
        )
    ),
    'sql'           => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('linkPaymentMethod', $strTable, array(), 'clr', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('linkedPaymentMethod', $strTable, array('includeBlankOption'=>true,'mandatory'=>true), 'w50', false, '', false, false, '', array('foreignKey' => 'tl_iido_shop_payment.type', 'relation' => array('type'=>'hasOne', 'load'=>'lazy'),'reference'=>$GLOBALS['TL_LANG'][ $strTable ]['options']['linkedPaymentMethod']));



// PRICE
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('enablePricePerCountry', $strTable, array(), 'clr', false, true);
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('freeOnPriceLimit', $strTable, array(), 'clr', false, true);


$GLOBALS['TL_DCA'][ $strTable ]['fields']['pricePerCountry'] = array
(
    'label'         => &$GLOBALS['TL_LANG'][ $strTable ]['pricePerCountry'],
    'exclude'       => true,
    'inputType'     => 'multiColumnWizard',
    'eval'          => array
    (
        'columnFields' => array
        (
            'country'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['pricePerCountry']['country'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback'   => array($tableClass, 'getShippingCountries'),
                'eval'      => array
                (
                    'mandatory'             => true,
                    'includeBlankOption'    => true,
                    'chosen'                => true,
                    'style'                 => 'width: 280px'
                )
            ),

            'price'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['pricePerCountry']['price'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array
                (
                    'rgxp'      => 'digit',
                    'style'     => 'width: 70px'
                )
            ),

            'label' => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['pricePerCountry']['label'],
                'exclude'   => true,
                'inputType' => 'text',
            ),

            'apiArticle' => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['pricePerCountry']['apiArticle'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => array($tableClass, 'getApiShippingArticles'),
            )
        )
    ),
    'sql'           => "blob NULL"
);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('freeOnCartPrice', $strTable, array('rgxp'=>'digit'));

$GLOBALS['TL_DCA'][ $strTable ]['fields']['freeOnlyPerCountry'] = array
(
    'label'         => &$GLOBALS['TL_LANG'][ $strTable ]['freeOnlyPerCountry'],
    'exclude'       => true,
    'inputType'     => 'multiColumnWizard',
    'eval'          => array
    (
        'columnFields' => array
        (
            'country'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['freeOnlyPerCountry']['country'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback'   => array($tableClass, 'getShippingCountries'),
                'eval'      => array
                (
                    'mandatory'             => true,
                    'includeBlankOption'    => true,
                    'chosen'                => true,
                    'style'                 => 'width: 320px'
                )
            ),

            'freeOnCartPrice'   => array
            (
                'label'     => $GLOBALS['TL_LANG'][ $strTable ]['field']['freeOnlyPerCountry']['freeOnCartPrice'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => array
                (
                    'rgxp'      => 'digit'
                )
            )
        )
    ),
    'sql'           => "blob NULL"
);