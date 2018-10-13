<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTableFileName   = \IIDO\ShopBundle\Config\BundleConfig::getFileTable( __FILE__ );
$tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
$strTableClass      = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strTableFileName );

$paymentHelperClass = \IIDO\ShopBundle\Helper\PaymentHelper::class;
$arrShippingAddress = \IIDO\ShopBundle\Helper\ShippingHelper::class;


\IIDO\BasicBundle\Helper\DcaHelper::createNewTable( $strTableFileName );



/**
 * Palettes
 */

$arrDefaultFields = array
(
    'contact_legend' => array
    (
        'name',
        'firstname',
        'lastname',

        'phone',
        'email'
    ),

    'address_legend' => array
    (
        'street',
        'postal',
        'city',
        'country',

        'otherShippingAddress'
    ),

    'order_legend' => array
    (
        'items',
        'shippingItems'
    ),

    'payment_legend' => array
    (
        'paymentMethod',
        'hasPayed',
        'paymentInfo'
    ),

    'shipping_legend' => array
    (
        'shippingMethod'
    ),

    'other_legend' => array
    (
        'acceptAGB'
    ),

    'api_legend' => array
    (
        'apiInfo'
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrDefaultFields, $strTableFileName);



/**
 * Subpalette
 */

$arrShippingAddress = array
(
    'shipping_name',
    'shipping_firstname',
    'shipping_lastname',

    'shipping_street',
    'shipping_postal',
    'shipping_city',
    'shipping_country'
);

\IIDO\BasicBundle\Helper\DcaHelper::addSubpalette('shippingAddress', $arrShippingAddress, $strTableFileName);



/**
 * Fields
 */


// CONTACT
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('name', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('firstname', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('lastname', $strTableFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('phone', $strTableFileName, array('rgxp'=>'phone'));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('email', $strTableFileName, array('rgxp'=>'email'));



// ADDRESS
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('street', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('postal', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('city', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('country', $strTableFileName);



// ADDRESS - SHIPPING ADDRESS
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('otherShippingAddress', $strTableFileName, array(), 'clr', false, true);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_name', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_firstname', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_lastname', $strTableFileName);

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_street', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_postal', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_city', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('shipping_country', $strTableFileName);



// PAYMENT
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('paymentMethod', $strTableFileName, array(), '', false, '', false, false, '', array('options_callback'=>array($paymentHelperClass, 'getPaymentMethods')));
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('hasPayed', $strTableFileName, array(), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addBlobField('paymentInfo', $strTableFileName);


// SHIPPING
\IIDO\BasicBundle\Helper\DcaHelper::addSelectField('shippingMethod', $strTableFileName, array(), '', false, '', false, false, '', array('options_callback'=>array($shippingHelperClass, 'getShippingMethods')));

$GLOBALS['TL_DCA'][ $strTableFileName ]['fields']['shippingItems'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTableFileName ]['shippingItems'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'multiple'              => true,
        'dragAndDrop'           => true,
        'tl_class'              => 'w50 hauto',
        'columnFields'          => array
        (
            'id' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['shippingItems_id'],
                'exclude'   => true,

                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 30px;',
                    'readonly' => true
                )
            ),

            'apiID' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['shippingItems_apiID'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 30px;',
                    'readonly' => true
                )
            ),

            'articleNumber' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['shippingItems_articleNumber'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 90px;',
                    'readonly' => true
                )
            ),

            'price' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['shippingItems_price'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 50px;',
                    'readonly' => true,
                    'rgxp' => 'digit'
                )
            ),

            'infos' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['shippingItems_infos'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 200px;',
                    'readonly' => true
                )
            )
        )
    ),
    'sql'                     => "blob NULL"
);



// OTHER
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('acceptAGB', $strTableFileName, array(), 'clr');



// ITEMS
$GLOBALS['TL_DCA'][ $strTableFileName ]['fields']['items'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTableFileName ]['items'],
    'exclude'                 => true,
    'inputType'               => 'multiColumnWizard',
    'eval'                    => array
    (
        'multiple'              => true,
        'dragAndDrop'           => true,
        'tl_class'              => 'w50 hauto',
        'columnFields'          => array
        (
            'id' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_id'],
                'exclude'   => true,

                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 30px;',
                    'readonly' => true
                )
            ),

            'apiID' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_apiID'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 30px;',
                    'readonly' => true
                )
            ),

            'articleNumber' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_articleNumber'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 90px;',
                    'readonly' => true
                )
            ),

            'name' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_name'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 150px;',
                    'readonly' => true
                )
            ),

            'quantity' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_quantity'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 50px;',
                    'readonly' => true,
                    'rgxp' => 'digit'
                )
            ),

            'singlePrice' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_singlePrice'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 50px;',
                    'readonly' => true,
                    'rgxp' => 'digit'
                )
            ),

            'totalPrice' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_totalPrice'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 50px;',
                    'readonly' => true,
                    'rgxp' => 'digit'
                )
            ),

            'infos' => array
            (
                'label'     => &$GLOBALS['TL_LANG'][ $strTable ]['cols']['items_infos'],
                'exclude'   => true,
                'inputType' => 'text',
                'eval' => array
                (
                    'style' => 'width: 200px;',
                    'readonly' => true
                )
            )
        )
    ),
    'sql'                     => "blob NULL"
);



// API
$GLOBALS['TL_DCA'][ $strTableFileName ]['fields']['apiInfo'] = array
(
    'label'                   => &$GLOBALS['TL_LANG'][ $strTableFileName ]['apiInfo'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'input_callback'          => array($strTableClass, 'renderApiInfoField'),
    'sql'                     => "blob NULL"
);



// ADDITIONAL
\IIDO\BasicBundle\Helper\DcaHelper::addCheckboxField('orderComplete', $strTableFileName, array(), 'clr');
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('language', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('apiOrderNumber', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('apiCustomerNumber', $strTableFileName);



// VOUCHER
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('voucherCode', $strTableFileName);