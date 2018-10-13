<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\ShopBundle\Model\IidoShopShippingModel;
use IIDO\ShopBundle\Model\IidoShopVoucherModel;


class ShopOrderHelper
{
    protected static $orderTable = 'tl_iido_shop_statistic_order';



    public static function addNewOrder( array $arrOrder )
    {
        $shopOrderClass = '\\' . \Model::getClassFromTable( self::$orderTable );

        $objShopOrder   = new $shopOrderClass();

        if( !$arrOrder['name'] )
        {
            $arrOrder['name'] = trim($arrOrder['firstname'] . ' ' . $arrOrder['lastname']);
        }

        if( !$arrOrder['firstname'] && $arrOrder['name'] )
        {
            $arrName = explode(" ", $arrOrder['name']);

            $arrOrder['firstname'] = $arrName[0];

            array_shift($arrName);

            $arrOrder['lastname'] = implode(" ", $arrName);
        }

        $objShopOrder->tstamp       = $arrOrder['tstamp']?:time();
        $objShopOrder->name         = $arrOrder['name'];
        $objShopOrder->firstname    = $arrOrder['firstname'];
        $objShopOrder->lastname     = $arrOrder['lastname'];

        $objShopOrder->phone        = $arrOrder['phone'];
        $objShopOrder->email        = $arrOrder['email'];

        $objShopOrder->street       = $arrOrder['street'];
        $objShopOrder->postal       = $arrOrder['postal'];
        $objShopOrder->city         = $arrOrder['city'];
        $objShopOrder->country      = $arrOrder['country'];

        $objShopOrder->otherShippingAddress = $arrOrder['otherShippingAddress'];

        if( $arrOrder['otherShippingAddress'] )
        {
            if( !$arrOrder['shipping_name'] )
            {
                $arrOrder['shipping_name'] = trim($arrOrder['shipping_firstname'] . ' ' . $arrOrder['shipping_lastname']);
            }

            if( !$arrOrder['shipping_firstname'] && $arrOrder['shipping_name'] )
            {
                $arrShippingName = explode(" ", $arrOrder['shipping_name']);

                $arrOrder['shipping_firstname'] = $arrShippingName[0];

                array_shift($arrShippingName);

                $arrOrder['shipping_lastname'] = implode(" ", $arrShippingName);
            }

            $objShopOrder->shipping_name         = $arrOrder['shipping_name'];
            $objShopOrder->shipping_firstname    = $arrOrder['shipping_firstname'];
            $objShopOrder->shipping_lastname     = $arrOrder['shipping_lastname'];

            $objShopOrder->shipping_street       = $arrOrder['shipping_street'];
            $objShopOrder->shipping_postal       = $arrOrder['shipping_postal'];
            $objShopOrder->shipping_city         = $arrOrder['shipping_city'];
            $objShopOrder->shipping_country      = $arrOrder['shipping_country'];
        }

        $objShopOrder->paymentMethod    = $arrOrder['paymentMethod'];
        $objShopOrder->shippingMethod   = $arrOrder['shippingMethod'];
        $objShopOrder->acceptAGB        = $arrOrder['acceptAGB'];

        $objShopOrder->items            = $arrOrder['items'];
        $objShopOrder->shippingItems    = $arrOrder['shippingItems'];

        if( key_exists('orderFields', $arrOrder) && is_array($arrOrder['orderFields']) && count($arrOrder['orderFields']) )
        {
            foreach($arrOrder['orderFields'] as $key => $value)
            {
//                if( is_numeric( $key ) )
//                {
//                    $key = $value;
//                }

                $objShopOrder->$key = $value;
            }
        }

        $objShopOrder->language = BasicHelper::getLanguage();

        return $objShopOrder->save();
    }



    public static function renderOrderItems()
    {
        $arrProducts        = array();
        $arrCartProducts    = json_decode( $_COOKIE['iido_shop_cart'], TRUE );

        if( count($arrCartProducts) )
        {
            $objApi = ApiHelper::getApiObject();

            foreach($arrCartProducts as $arrCartProduct)
            {
                if( !$arrCartProduct['price'] && $objApi )
                {
                    $arrCartProduct['price'] = (float) preg_replace("/,/", '.', $objApi->getProductPrice( $arrCartProduct['itemNumber']) );
                }

                $arrProduct = ShopHelper::getProduct($arrCartProduct, array(), false, true);

                $arrProducts[] = array
                (
                    'id'            => '', // TODO: intern product ID
                    'apiID'         => ($objApi ? $objApi->getProductID($arrCartProduct['realItemNumber']?:$arrCartProduct['itemNumber']) : '' ),
                    'articleNumber' => $arrCartProduct['itemNumber'],
                    'realArticleNumber' => $arrCartProduct['realItemNumber'],
                    'name'          => $arrCartProduct['name'],
                    'quantity'      => $arrCartProduct['quantity'],
                    'singlePrice'   => (float) $arrProduct['price'],
                    'totalPrice'    => ((float) $arrProduct['price'] * $arrCartProduct['quantity']),
                    'infos'         => ''
                );

                if( $arrCartProduct['tuning'] )
                {
                    $objTuning      = false;
                    $tuningPrice    = 0;

                    if( $objApi )
                    {
                        $objTuning      = $objApi->getProductArray( $arrCartProduct['tuning'] );
                        $tuningPrice    = ShopHelper::getCurrentPrice( $objTuning );
                    }

                    $arrProducts[] = array
                    (
                        'id'                => '',
                        'apiID'             => ($objTuning ? $objTuning['id'] : '' ),
                        'articleNumber'     => $arrCartProduct['tuning'],
                        'realArticleNumber' => '',
                        'name'              => $objTuning['name'],
                        'quantity'          => 1,
                        'singlePrice'       => (float) $tuningPrice,
                        'totalPrice'        => (float) $tuningPrice,
                        'infos'             => 'Tuning für ' . ($arrCartProduct['realItemNumber']?:$arrCartProduct['itemNumber'])
                    );
                }
            }
        }

        if( !count($arrProducts) )
        {
            $arrProducts = array('id'=>'','apiID'=>'','articleNumber'=>'','name'=>'','singlePrice'=>'','totalPrice'=>'','quantity'=>'','infos'=>'');
        }

        return $arrProducts;
    }



    public static function getShippingItems( $shippingAlias, $countryCode, $cartPrice = 0 )
    {
        $arrShippings   = array();
        $objShipping    = IidoShopShippingModel::findByIdOrAlias( $shippingAlias );

        if( $objShipping )
        {
            list($shippingPrice, $shippingArticle) = ShippingHelper::getShippingPrice($countryCode, $objShipping, $cartPrice, true);

            if( $shippingPrice )
            {
                $arrShippings[] = array
                (
                    'id'            => $objShipping->id,
                    'apiID'         => $shippingArticle,
                    'price'         => $shippingPrice,
                    'articleNumber' => '',
                    'infos'         => ''
                );
            }
        }

//        if( $countryCode === "ch" || $countryCode === "no" )
//        {
//            switch($countryCode)
//            {
//                case "no":
//                    $countryPrice = 39.95;
//                    break;
//
//                default:
//                    $countryPrice = 49.95;
//                    break;
//            }
//
//            $arrShippings[] = array
//            (
//                'method'            => 'country_add',
//                'price'             => $countryPrice,
//                'apiArticleNumber'  => ''
//            );
//        }

//        if( !count($arrShippings) )
//        {
//            $arrShippings = array('id'=>'','apiID'=>'','articleNumber'=>'','price'=>'','infos'=>'');
//        }

        return $arrShippings;
    }



    public static function getOrder( $orderID )
    {
        $shopOrderClass = '\\' . \Model::getClassFromTable( self::$orderTable );

        return $shopOrderClass::findByPk( $orderID );
    }



    public static function getFirstname( $objOrder )
    {
        $firstname = trim($objOrder->firstname);

        if( !strlen($firstname) )
        {
            $arrName = explode(" ", $objOrder->name);

//            array_pop($arrName);

            $firstname = $arrName[0];
        }

        return $firstname;
    }



    public static function getLastname( $objOrder )
    {
        $lastname = $objOrder->lastname;

        if( !strlen($lastname) )
        {
            $arrName = explode(" ", $objOrder->name);

            array_shift($arrName);

            $lastname = implode(" ", $arrName);
        }

        return $lastname;
    }



    public static function sendEmails( $objOrder, $objApiOrder )
    {
        $strSubject = 'Deine ORIGINAL+ Bestellung ist angekommen!';
        $strLang    = BasicHelper::getLanguage();

        if( $strLang === "en" )
        {
            $strSubject = 'Your order with ORIGINAL+ has been received!';
        }

        $objCustomerEmail = new \Email();

        $objCustomerEmail->from         = 'support@typs.gmbh';
        $objCustomerEmail->fromName     = 'ORIGINAL+ // TYPS (Take Your Pleasure Seriously) GmbH';

        $objCustomerEmail->subject      = $strSubject;

        $objCustomerEmail->html         = self::renderEmailOrder( $objOrder, $objApiOrder, $strLang );

        $objCustomerEmail->replyTo('support@typs.gmbh');
        $objCustomerEmail->sendTo( $objOrder->email );



        $objShopEmail = new \Email();

        $objShopEmail->from         = 'support@typs.gmbh';
        $objShopEmail->fromName     = 'ORIGINAL+ // TYPS (Take Your Pleasure Seriously) GmbH';

        $objShopEmail->subject      = 'Neue Shop Bestellung';

        $objShopEmail->html         = self::renderEmailOrder( $objOrder, $objApiOrder, $strLang );

        $objShopEmail->replyTo( $objOrder->email );
        $objShopEmail->sendTo( 'support@typs.gmbh' );
    }



    protected static function renderEmailOrder( $objOrder, $objApiOrder, $strLang = '' )
    {
//        if( !$strLang )
//        {
//            $strLang = BasicHelper::getLanguage();
//        }

        \Controller::loadLanguageFile( "iido_shop_emails" );
        \Controller::loadLanguageFile( "countries" );

        $arrLabel   = $GLOBALS['TL_LANG']['iido_shop_emails']['label'];
        $arrDummy   = $GLOBALS['TL_LANG']['iido_shop_emails']['dummy'];

        $objTemplate    = new \FrontendTemplate('iido_shop_orderComplete_email_customer');

        $objTemplate->label     = $arrLabel;
        $objTemplate->dummy     = $arrDummy;


        $objTemplate->orderNumber   = $objApiOrder['orderNumber'];
        $objTemplate->orderDate     = date("d.m.Y", $objOrder->tstamp);


        $objShipping    = ShippingHelper::getObject( $objOrder->shippingMethod );
        $objPayment     = PaymentHelper::getObject( $objOrder->paymentMethod );

        $objTemplate->payment           = $objOrder->paymentMethod;
        $objTemplate->shipping          = $objOrder->paymentMethod;

        $objTemplate->paymentMethod     = PaymentHelper::getOverview( $objPayment );
        $objTemplate->shippingMethod    = ($objShipping->frontendTitle?:$objShipping->name);


        $objTemplate->logo              = '<img src="/files/original-plus/images/shop/original-plus_email_logo.jpg" width="345" height="45" alt="ORIGINAL+ Logo">';
        $objTemplate->slogan            = 'MY SPIRIT. MY SKI.';


        $objTemplate->firstname     = $objOrder->firstname;
        $objTemplate->lastname      = $objOrder->lastname;
        $objTemplate->street        = $objOrder->street;
        $objTemplate->postal        = $objOrder->postal;
        $objTemplate->city          = $objOrder->city;
        $objTemplate->country       = $objOrder->country?$GLOBALS['TL_LANG']['CNT'][ $objOrder->country ]:'';

        $objTemplate->shipping_firstname    = $objOrder->shipping_firstname ? : $objOrder->firstname;
        $objTemplate->shipping_lastname     = $objOrder->shipping_lastname  ? : $objOrder->lastname;
        $objTemplate->shipping_street       = $objOrder->shipping_street    ? : $objOrder->street;
        $objTemplate->shipping_postal       = $objOrder->shipping_postal    ? : $objOrder->postal;
        $objTemplate->shipping_city         = $objOrder->shipping_city      ? : $objOrder->city;
        $objTemplate->shipping_country      = $objOrder->shipping_country   ? : $objOrder->country;

        if( $objTemplate->shipping_country )
        {
            $objTemplate->shipping_country = $GLOBALS['TL_LANG']['CNT'][ $objTemplate->shipping_country ];
        }

        $objTemplate->phone = $objOrder->phone;
        $objTemplate->email = $objOrder->email;

        $objTemplate->items         = \StringUtil::deserialize($objOrder->items, TRUE);
        $objTemplate->shippingItems = \StringUtil::deserialize($objOrder->shippingItems, TRUE);

        $strContent = \Controller::replaceInsertTags( $objTemplate->parse() );
        $strContent = str_replace('###SALUTATION###', $objOrder->firstname . ' ' . $objOrder->lastname, $strContent);

        return $strContent;
    }



    public static function getOrderArray( $returnPrice = false)
    {
        $orderItems     = self::renderOrderItems();
        $totalPrice     = 0;
        $countryCode    = ((\Input::post("shipping_address") === 'other') ? \Input::post("shipping_country") : \Input::post("country") );

        foreach($orderItems as $orderItem)
        {
            $totalPrice = ($totalPrice + $orderItem['totalPrice']);
        }

        $shippingItems  = ShopOrderHelper::getShippingItems( \Input::post("shipping"), $countryCode, $totalPrice );

        if( count($shippingItems) )
        {
            foreach($shippingItems as $shippingItem)
            {
                $totalPrice = ($totalPrice + $shippingItem['price']);
            }
        }

        $arrOrder = array
        (
            'tstamp'    => time(),
            'name'      => \Input::post("firstname_name"), //TODO: name field names?!

            'phone'     => \Input::post("phone"),
            'email'     => \Input::post("email"),

            'street'    => \Input::post("street"),
            'postal'    => \Input::post("postal"),
            'city'      => \Input::post("city"),
            'country'   => \Input::post("country"),

            'otherShippingAddress'  => (\Input::post("shipping_address") === 'other'),

            'shipping_name'         => \Input::post("shipping_firstname_name"), // TODO: name field names?!!
            'shipping_street'       => \Input::post("shipping_street"),
            'shipping_postal'       => \Input::post("shipping_postal"),
            'shipping_city'         => \Input::post("shipping_city"),
            'shipping_country'      => \Input::post("shipping_country"),

            'paymentMethod'     => \Input::post("payment"),
            'shippingMethod'    => \Input::post("shipping"),

            'acceptAGB'         => (\Input::post('agb') === 'accept'),

            'items'             => $orderItems,
            'shippingItems'     => $shippingItems
        );

        return $returnPrice ? array($arrOrder, $totalPrice) : $arrOrder;
    }



    public static function checkDeliveryAddress($arrCustomer, $objOrder)
    {
        $shippingCity       = $objOrder->shipping_city     ?   : $objOrder->city;
        $shippingStreet     = $objOrder->shipping_street   ?   : $objOrder->street;
        $shippingPostal     = $objOrder->shipping_postal   ?   : $objOrder->postal;
        $shippingCountry    = strtoupper($objOrder->shipping_country  ?   : $objOrder->country);

        $arrDeliveryAddress = array
        (
            'firstName'     => $objOrder->firstname,
            'lastName'      => $objOrder->lastname,
            'city'          => $shippingCity,
            'street1'       => $shippingStreet,
            'zipcode'       => $shippingPostal,
            'countryCode'   => strtoupper($shippingCountry),
        );

        $customerCity = $customerStreet = $customerPostal = $customerCountry = '';

        if( isset($arrCustomer['addresses']) && is_array($arrCustomer['addresses']) && count($arrCustomer['addresses']) )
        {
            $noDeliveryAddress = true;

            foreach($arrCustomer['addresses'] as $arrAddress)
            {
                if( $arrAddress['deliveryAddress'] )
                {
                    $noDeliveryAddress = false;

                    $customerCity   = $arrAddress['city'];
                    $customerStreet = $arrAddress['street1'];
                    $customerPostal = $arrAddress['zipcode'];
                    $customerCountry = strtoupper($arrAddress['countryCode']);

                    break;
                }
            }

            if( $noDeliveryAddress )
            {
                foreach($arrCustomer['addresses'] as $arrAddress)
                {
                    if( $arrAddress['primeAddress'] )
                    {
                        $customerCity   = $arrAddress['city'];
                        $customerStreet = $arrAddress['street1'];
                        $customerPostal = $arrAddress['zipcode'];
                        $customerCountry = strtoupper($arrAddress['countryCode']);

                        break;
                    }
                }
            }
        }

        if( $customerStreet !== $shippingStreet || $customerCity !== $shippingCity || $customerPostal !== $shippingPostal || $customerCountry !== $shippingCountry )
        {
            return $arrDeliveryAddress;
        }

        return false;
    }



    public static function checkBillingAddress($arrCustomer, $objOrder)
    {
        $shippingCity       = $objOrder->city;
        $shippingStreet     = $objOrder->street;
        $shippingPostal     = $objOrder->postal;
        $shippingCountry    = strtoupper($objOrder->country);

        $arrBillingAddress = array
        (
            'firstName'     => $objOrder->firstname,
            'lastName'      => $objOrder->lastname,
            'city'          => $shippingCity,
            'street1'       => $shippingStreet,
            'zipcode'       => $shippingPostal,
            'countryCode'   => strtoupper($shippingCountry),
        );

        $customerCity = $customerStreet = $customerPostal = $customerCountry = '';

        if( isset($arrCustomer['addresses']) && is_array($arrCustomer['addresses']) && count($arrCustomer['addresses']) )
        {
            $noInvoiceAddress = true;

            foreach($arrCustomer['addresses'] as $arrAddress)
            {
                if( $arrAddress['invoiceAddress'] )
                {
                    $noInvoiceAddress = false;

                    $customerCity   = $arrAddress['city'];
                    $customerStreet = $arrAddress['street1'];
                    $customerPostal = $arrAddress['zipcode'];
                    $customerCountry = strtoupper($arrAddress['countryCode']);

                    break;
                }
            }

            if( $noInvoiceAddress )
            {
                foreach($arrCustomer['addresses'] as $arrAddress)
                {
                    if( $arrAddress['primeAddress'] )
                    {
                        $customerCity   = $arrAddress['city'];
                        $customerStreet = $arrAddress['street1'];
                        $customerPostal = $arrAddress['zipcode'];
                        $customerCountry = strtoupper($arrAddress['countryCode']);

                        break;
                    }
                }
            }
        }

        if( $customerStreet !== $shippingStreet || $customerCity !== $shippingCity || $customerPostal !== $shippingPostal || $customerCountry !== $shippingCountry )
        {
            return $arrBillingAddress;
        }

        return false;
    }



    public static function isWorldCountry( $countryCode, $shippingMethod )
    {
        $isWorldCountry     = false;

        $arrUsedCountries   = array();
        $arrWorldCountries  = array();
        $objShipping        = ShippingHelper::getObject( $shippingMethod );

        if( $objShipping->enablePricePerCountry )
        {
            $arrShippCountries = \StringUtil::deserialize($objShipping->pricePerCountry, TRUE);

            foreach($arrShippCountries as $arrShippCountry)
            {
                $country    = $arrShippCountry['country'];
                $strLabel   = $arrShippCountry['label'] ?:$GLOBALS['TL_LANG']['CNT'][ $country ];

                if( $country === "eu" )
                {
                    foreach($GLOBALS['TL_LANG']['SHOP']['countries']['eu'] as $key => $countryName)
                    {
                        $arrUsedCountries[ $key ] = $key;
                    }
                }
                elseif( $country === "world" )
                {
                    foreach($GLOBALS['TL_LANG']['CNT'] as $key => $countryName)
                    {
                        if( !array_key_exists($key, $arrUsedCountries) && !array_key_exists($key, $arrWorldCountries) )
                        {
                            if( $countryCode === $key )
                            {
                                $isWorldCountry = true;
                                break;
                            }
                            else
                            {
                                $arrWorldCountries[ $key ] = $key;
                            }
                        }
                    }
                }
                else
                {
                    $arrUsedCountries[ $country ] = $country;
                }

                if( $isWorldCountry )
                {
                    break;
                }
            }
        }

        return $isWorldCountry;
    }



    public static function getVoucherDiscount( $voucherCode, $cartPrice )
    {
        $discount   = 0;
        $objVoucher = IidoShopVoucherModel::findOneBy("code", $voucherCode);

        if( $objVoucher )
        {
            if( $objVoucher->mode === "price" )
            {
                $discount = ($objVoucher->priceDiscount * 100 / $cartPrice);
            }
            else
            {
                $discount = $objVoucher->percentDiscount;
            }
        }

        return $discount;
    }
}