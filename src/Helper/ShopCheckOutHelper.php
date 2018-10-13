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
                        'firstname_name' => 'widget-fullname',
                        'street',
                        'city',
                        'postal',
                        'country' => array('type' => 'country')
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
                        'shipping_country' => array('type' => 'country')
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

                                        if( is_array($config) )
                                        {
                                            $config = $strKey;
                                        }
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
//echo "<pre>"; print_r( $arrFields ); exit;
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
                $arrMessage['message'][]    = array('class'=>$strField,'text'=>$langError[ $strField ]);
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
//                            $arrMessage['message'][]    = $langError[ $strField ];
                            $arrMessage['message'][]    = array('class'=>$strField,'text'=>$langError[ $strField ]);
                        }
                        break;

                    case "phone":
                        if( !\Validator::isPhone( $fieldValue ) )
                        {
                            $error = true;

                            $arrMessage['fields'][]     = $strField;
//                            $arrMessage['message'][]    = $langError[ $strField ];
                            $arrMessage['message'][]    = array('class'=>$strField,'text'=>$langError[ $strField ]);
                        }
                        break;

                    case "fullname":
                        $arrName = explode(" ", $fieldValue);

                        if( count($arrName) === 1 )
                        {
                            $error = true;
                            $arrMessage['fields'][]     = $strField;
//                            $arrMessage['message'][]    = $langError['fullname'];
                            $arrMessage['message'][]    = array('class'=>'fullname','text'=>$langError[ 'fullname' ]);
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



    /**
     * @param $error
     */
    public static function setFormError( $error )
    {
        \Session::getInstance()->set("hasCheckOutFormError", $error);
    }



    /**
     * @param $errorMessage
     */
    public static function setFormErrorMessage( $errorMessage )
    {
        \Session::getInstance()->set("checkOutFormErrorMessage", $errorMessage);
    }



    /**
     * @param boolean $setFieldsEmpty
     */
    public static function setFormFields( $setFieldsEmpty = false )
    {
        \Session::getInstance()->set("checkOutFormFields", ($setFieldsEmpty ? array() : self::getFormInputs( true )) );
    }



    public static function removeSessions()
    {
        self::setFormError( false );
        self::setFormErrorMessage( array() );
        self::setFormFields( true );
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



    public static function getShippingCountries( $arrShippings )
    {
        $arrCountries = array();
        $allCountries = false;

        \Controller::loadLanguageFile("countries");

        foreach($arrShippings as $objShipping)
        {
            if( $objShipping->enablePricePerCountry )
            {
                $allCountries = false;
                $arrShippCountries = \StringUtil::deserialize($objShipping->pricePerCountry, TRUE);

                foreach($arrShippCountries as $arrShippCountry)
                {
                    $country    = $arrShippCountry['country'];
                    $strLabel   = $arrShippCountry['label'] ?:$GLOBALS['TL_LANG']['CNT'][ $country ];

                    if( $country === "eu" )
                    {
                        foreach($GLOBALS['TL_LANG']['SHOP']['countries']['eu'] as $key => $countryName)
                        {
                            $arrCountries[ $key ] = $GLOBALS['TL_LANG']['CNT'][ $key ];
                        }
                    }
                    elseif( $country === "world" )
                    {
                        foreach($GLOBALS['TL_LANG']['CNT'] as $key => $countryName)
                        {
                            if( !array_key_exists($key, $arrCountries) )
                            {
                                $arrCountries[ $key ] = $countryName;
                            }
                        }
                    }
                    else
                    {
                        $arrCountries[ $country ] = $strLabel;
                    }
                }
            }
            else
            {
                $allCountries = true;
            }
        }

        if( $allCountries && !count($arrCountries) )
        {
            $arrCountries = $GLOBALS['TL_LANG']['CNT'];
        }

//        ksort( $arrCountries );
        asort( $arrCountries );

        if( key_exists('at', $arrCountries) )
        {
            $countryAustria = $arrCountries['at'];

            unset( $arrCountries['at'] );

            array_insert($arrCountries, 0, array
            (
                'at' => $countryAustria
            ));
        }


        return $arrCountries;
    }



    public static function getCountry( $countryCode )
    {
        $strCountry = '';

        if( $countryCode )
        {
            \Controller::loadLanguageFile("countries");

            $strCountry = $GLOBALS['TL_LANG']['CNT'][ $countryCode ];
        }

        return $strCountry;
    }
}