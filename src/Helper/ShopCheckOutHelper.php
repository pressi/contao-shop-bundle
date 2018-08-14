<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


class ShopCheckOutHelper
{
    protected static $arrFormFields = array
    (
        'col-left' => array
        (
            'type'      => 'div',
            'fields'    => array
            (
                'billing_address' => array
                (
                    'type'      => 'box',
                    'fields'    => array
                    (
                        'firstname_name',
                        'street',
                        'city',
                        'postal',
                        'country'
                    )
                ),

                'shipping_address' => array
                (
                    'type'      => 'radioGroup',
                    'fields'    => array
                    (
                        'like_billing_address',
                        'other'
                    ),
                ),

                'shipping_address_box' => array
                (
                    'type'      => 'hidden_box',
                    'fields'    => array
                    (
                        'shipping_firstname_name',
                        'shipping_street',
                        'shipping_city',
                        'shipping_postal',
                        'shipping_country'
                    ),
                    'wrapper'   => 'shipping-address-fields',
                    'dependent' => array
                    (
                        'field'     => 'shipping_address',
                        'value'     => 'other'
                    )
                )
            )
        ),

        'col-right' => array
        (
            'type'      => 'div',
            'fields'    => array
            (
                'contact_data' => array
                (
                    'type'      => 'box',
                    'fields'    => array
                    (
                        'phone' => 'widget-phone not-mandatory',
                        'email' => 'widget-email'
                    )
                ),

                'shippings' => array('type' => 'shipping'),
                'payments'  => array('type' => 'payment'),

                'agb' => array
                (
                    'type'      => 'radioGroup',
                    'fields'    => array('agb_text')
                )
            ),
        )
    );


    protected static $keyFormFieldTypes = array
    (
        "radioGroup",
        "shipping",
        "payment"
    );



    public static function getFormFields()
    {
        return self::$arrFormFields;
    }



    public static function getFormInputs( $includePosts = false )
    {
        $arrFields = array();

        foreach( self::$arrFormFields as $k => $arrFieldConfig)
        {
            if( is_array($arrFieldConfig) )
            {
                if( in_array($arrFieldConfig['type'], self::$keyFormFieldTypes) )
                {
                    if( $arrFieldConfig['type'] === "payment" || $arrFieldConfig['type'] === "shipping" )
                    {
                        $k = $arrFieldConfig['type'];
                    }

                    if( $includePosts )
                    {
                        $arrFields[ $k ] = array
                        (
                            'mandatory' => true,
                            'value' => \Input::post( $k ),
                            'check'     => 'default'
                        );
                    }
                    else
                    {
                        $arrFields[] = array
                        (
                            'mandatory' => true,
                            'value'     => $k,
                            'check'     => 'default'
                        );
                    }
                }
                else
                {
                    if( isset($arrFieldConfig['dependent']) )
                    {
                        if( \Input::post($arrFieldConfig['dependent']['field']) !== $arrFieldConfig['dependent']['value'] )
                        {
                            continue;
                        }
                    }

                    foreach($arrFieldConfig['fields'] as $key => $fieldConfig)
                    {

                        if( is_array($fieldConfig) )
                        {
                            if( in_array($fieldConfig['type'], self::$keyFormFieldTypes) )
                            {

                                if( $fieldConfig['type'] === "payment" || $fieldConfig['type'] === "shipping" )
                                {
                                    $key = $fieldConfig['type'];
                                }

                                if( $includePosts )
                                {
                                    $arrFields[ $key ] = array
                                    (
                                        'mandatory' => true,
                                        'value'     => \Input::post( $key ),
                                        'check'     => 'default'
                                    );
                                }
                                else
                                {
                                    $arrFields[] = array
                                    (
                                        'mandatory' => true,
                                        'value'     => $key,
                                        'check'     => 'default'
                                    );
                                }
                            }
                            else
                            {
                                if( isset($fieldConfig['dependent']) )
                                {
                                    if( \Input::post($fieldConfig['dependent']['field']) !== $fieldConfig['dependent']['value'] )
                                    {
                                        continue;
                                    }
                                }

                                foreach($fieldConfig['fields'] as $strKey => $strFieldConfig)
                                {
                                    $config = '';

                                    if( !is_numeric($strKey) )
                                    {
                                        $config         = $strFieldConfig;
                                        $strFieldConfig = $strKey;
                                    }

                                    if( $includePosts )
                                    {
                                        $arrFields[ $strFieldConfig ] = array
                                        (
                                            'mandatory' => preg_match('/not-mandatory/', $config) ? false : true,
                                            'value'     => \Input::post( $strFieldConfig ),
                                            'check'     => preg_match('/widget-([A-Za-z]{0,})/', $config, $arrConfigMatches) ? $arrConfigMatches[1] : 'default'
                                        );
                                    }
                                    else
                                    {
                                        $arrFields[] = array
                                        (
                                            'mandatory' => preg_match('/not-mandatory/', $config) ? false : true,
                                            'value'     => $strFieldConfig,
                                            'check'     => preg_match('/widget-([A-Za-z]{0,})/', $config, $arrConfigMatches) ? $arrConfigMatches[1] : 'default'
                                        );
                                    }
                                }
                            }
                        }
                        else
                        {
                            if( $includePosts )
                            {
                                $arrFields[ $fieldConfig ] = array
                                (
                                    'mandatory' => true,
                                    'value'     => \Input::post( $fieldConfig ),
                                    'check'     => 'default'
                                );
                            }
                            else
                            {
                                $arrFields[] = array
                                (
                                    'mandatory' => true,
                                    'value'     => $fieldConfig,
                                    'check'     => 'default'
                                );
                            }
                        }
                    }
                }
            }
            else
            {
                if( $includePosts )
                {
                    $arrFields[ $arrFieldConfig ] = array
                    (
                        'mandatory' => true,
                        'value'     => \Input::post( $arrFieldConfig ),
                        'check'     => 'default'
                    );
                }
                else
                {
                    $arrFields[] = array
                    (
                        'mandatory' => true,
                        'value'     => $arrFieldConfig,
                        'check'     => 'default'
                    );
                }
            }
        }

        if( count($arrFields) && $includePosts )
        {
            foreach($arrFields as $strName => $strValue)
            {
                if( $strName === "address" && is_array($strValue) )
                {
                    $arrFields[ $strName ] = self::getAddress( $strValue );
                }
            }
        }

        return $arrFields;
    }



    public static function getErrorMessage()
    {
        return \Session::getInstance()->get("checkOutFormErrorMessage");
    }



    public static function hasError()
    {
        return \Session::getInstance()->get("hasCheckOutFormError");
    }



    public static function getFormInputsFromSession()
    {
        $arrFields = \Session::getInstance()->get("checkOutFormFields");

        if( !is_array($arrFields) )
        {
            $arrFields = array();
        }

        return $arrFields;
    }



    public static function checkForm( $returnMessage = false )
    {
        \Controller::loadLanguageFile("iido_shop_checkout");

        $arrMessage = array('fields' => array(), 'message' => array());

        $langError  = $GLOBALS['TL_LANG']['iido_shop_checkout']['error'];
        $error      = false;

        $arrFields  = ShopCheckOutHelper::getFormInputs( true );

        foreach( $arrFields as $strField => $arrField )
        {
            $fieldValue = $arrField['value'];
            if( !strlen( $fieldValue ) && !$arrField['mandatory'] )
            {
                continue;
            }

            if( !strlen( $fieldValue ) )
            {
                $error = true;

                $arrMessage['fields'][]     = $strField;
                $arrMessage['message'][]    = $langError[ $strField ];
            }
            else
            {
                $check = $arrField['check'];

                switch( $check )
                {
                    case "email":
                        if( !\Validator::isEmail( $fieldValue ) )
                        {
                            $error = true;

                            $arrMessage['fields'][]     = $strField;
                            $arrMessage['message'][]    = $langError[ $strField ];
                        }
                        break;

                    case "phone":
                        if( !\Validator::isPhone( $fieldValue ) )
                        {
                            $error = true;

                            $arrMessage['fields'][]     = $strField;
                            $arrMessage['message'][]    = $langError[ $strField ];
                        }
                        break;
                }
            }
        }

        return ($returnMessage ? array($error, $arrMessage) : $error);
    }



    protected static function getAddress( $arrAddress )
    {
        $strAddress = $arrAddress[0];

        unset($arrAddress[0]);

        foreach($arrAddress as $address)
        {
            if( strlen($address) )
            {
                $strAddress = (strlen($strAddress) ? '<br>' : '') . $address;
            }
        }

        return $strAddress;
    }



    public static function setFormError( $error )
    {
        \Session::getInstance()->set("hasCheckOutFormError", $error);
    }



    public static function setFormErrorMessage( $errorMessage )
    {
        \Session::getInstance()->set("checkOutFormErrorMessage", $errorMessage);
    }



    public static function setFormFields()
    {
        \Session::getInstance()->set("checkOutFormFields", self::getFormInputs( true ));
    }



    public static function getFullName( $prefix = '' )
    {
        $strName = \Input::post( $prefix  . "firstname_name" );

        if( !$strName )
        {
            $strName = \Input::post( $prefix  . "name_firstname" );
        }

        if( !$strName )
        {
            $strName = \Input::post( $prefix  . "name" );
        }

        if( !$strName )
        {
            $strName = \Input::post( $prefix  . "firstname" ) . ' ' . \Input::post( $prefix  . "lastname" );
        }

        return trim($strName);
    }
}