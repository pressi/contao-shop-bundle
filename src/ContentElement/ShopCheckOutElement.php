<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use Contao\Validator;
use IIDO\BasicBundle\Helper\ContentHelper;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\PaymentHelper;
use IIDO\ShopBundle\Helper\ShopCheckOutHelper;
use IIDO\ShopBundle\Model\IidoShopShippingModel;


class ShopCheckOutElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_checkout';



    /**
     * Generate configurator element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### SHOP: CHECK OUT (Formular) ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        $this->iidoShopShippings    = \StringUtil::deserialize($this->iidoShopShippings, TRUE);
        $this->iidoShopPayments     = \StringUtil::deserialize($this->iidoShopPayments, TRUE);

        return parent::generate();
    }



    /**
     * Compile the content element
     */
    protected function compile()
    {
        global $objPage;

        \Controller::loadLanguageFile("iido_shop_checkout");

        $error          = ShopCheckOutHelper::hasError();
        $errorMessage   = ShopCheckOutHelper::getErrorMessage();
        $arrValue       = ShopCheckOutHelper::getFormInputsFromSession(); //array();

        $this->Template->error          = $error;
        $this->Template->errorMessage   = $errorMessage;
        $this->Template->value          = $arrValue;
        $this->Template->cartNum        = ShopConfig::getCartNum();

        $this->Template->showErrorMessagesOnTop = $this->showErrorMessagesOnTop;

        $strLang        = $GLOBALS['TL_LANG']['iido_shop_checkout'];
        $cartLink       = 'javascript:history.back(-1);';
        $forwardLink    = ''; //'javascript:void(0);';

        if( $this->iidoShopCartJumpTo )
        {
            $cartLink = \PageModel::findByPk( $this->iidoShopCartJumpTo )->getFrontendUrl();
        }

        if( $this->iidoShopForwardJumpTo )
        {
            $forwardLink = \PageModel::findByPk( $this->iidoShopForwardJumpTo )->getFrontendUrl();
        }
        else
        {
            $forwardLink = $objPage->getFrontendUrl();
        }

        $arrPayments    = array();
        $arrShippings   = array();

        foreach($this->iidoShopShippings as $shippingID)
        {
            $objShipping = $this->Database->prepare("SELECT * FROM " . IidoShopShippingModel::getTable() . " WHERE id=?")->limit(1)->execute( $shippingID );

            if( $objShipping && $objShipping->published )
            {
                $objShipping->info = ContentHelper::renderText( $objShipping->info );

                $arrShippings[] = $objShipping;
            }
        }

        foreach($this->iidoShopPayments as $paymentID)
        {
            $objPayment = PaymentHelper::getObject( $paymentID );

            if( $objPayment && $objPayment->published )
            {
                $arrPayments[] = $objPayment;
            }
        }

        $this->Template->formFields     = ShopCheckOutHelper::getFormFields();
        $this->Template->label          = $strLang['label'];

        $this->Template->agbLink        = $this->iidoShopAGBLink;
        $this->Template->cartLink       = $cartLink;
        $this->Template->forwardLink    = $forwardLink;

        $this->Template->payments       = $arrPayments;
        $this->Template->shippings      = $arrShippings;
    }



//    protected function checkForm( $returnMessage = false )
//    {
//        $arrMessage = array('fields' => array(), 'message' => array());
//
//        $langError  = $GLOBALS['TL_LANG']['iido_shop_checkout']['error'];
//        $error      = false;
//
//        $arrFields  = ShopCheckOutHelper::getFormInputs( true );
//
//        foreach( $arrFields as $strField => $fieldValue )
//        {
//            if( !strlen( $fieldValue ) )
//            {
//                $error = true;
//
//                $arrMessage['fields'][]     = $strField;
//                $arrMessage['message'][]    = $langError[ $strField ];
//            }
//        }
//
//        return ($returnMessage ? array($error, $arrMessage) : $error);
//    }



//    protected function checkFormOld( $returnMessage = false )
//    {
//        $arrMessage = array('fields' => array(), 'message' => array());
//        $langError  = $GLOBALS['TL_LANG']['iido_shop_checkout']['error'];
//
//        $name       = \Input::post("name");
//        $name_fn    = \Input::post("name_firstname");
//        $fn_name    = \Input::post("firstname_name");
//
//        $address    = ((is_array(\Input::post("address")) ) ? $this->getAddress( \Input::post("address") ) : \Input::post("address"));
//
//        $phone      = \Input::post("phone");
//        $email      = \Input::post("email");
//
//        $shipping   = \Input::post("shipping");
//        $payment    = \Input::post("payment");
//        $agb        = \Input::post("agb");
//
//        $city       = \Input::post("city");
//        $postal     = \Input::post("postal");
//        $street     = \Input::post("street");
//        $country    = \Input::post("country");
//
//        $shippingAddress = \Input::post("shipping_address");
//
//        $error      = false;
//
//        if( !strlen($name) || !strlen($address) )
//        {
//            $error = true;
//
//            if( !strlen($name) )
//            {
//                $arrMessage['fields'][]     = 'name';
//                $arrMessage['message'][]    = $langError['name'];
//            }
//
//            if( !strlen($address) )
//            {
//                $arrMessage['fields'][]     = 'address';
//                $arrMessage['message'][]    = $langError['address'];
//            }
//
////            return ($returnMessage ? array(false, $arrMessage) : false);
//        }
//
//        if( !strlen($name_fn) )
//        {
//            $arrMessage['fields'][]     = 'name_firstname';
//            $arrMessage['message'][]    = $langError['name'];
//        }
//
//        if( !strlen($fn_name) )
//        {
//            $arrMessage['fields'][]     = 'firstname_name';
//            $arrMessage['message'][]    = $langError['name'];
//        }
//
//        if( !strlen($city) )
//        {
//            $arrMessage['fields'][]     = 'city';
//            $arrMessage['message'][]    = $langError['city'];
//        }
//
//        if( !strlen($postal) )
//        {
//            $arrMessage['fields'][]     = 'postal';
//            $arrMessage['message'][]    = $langError['postal'];
//        }
//
//        if( !strlen($street) )
//        {
//            $arrMessage['fields'][]     = 'street';
//            $arrMessage['message'][]    = $langError['street'];
//        }
//
//        if( !strlen($country) )
//        {
//            $arrMessage['fields'][]     = 'country';
//            $arrMessage['message'][]    = $langError['country'];
//        }
//
//        if( !strlen($phone) || !Validator::isPhone($phone) )
//        {
//            $error = true;
//
//            if( !strlen($phone) )
//            {
//                $arrMessage['fields'][]     = 'phone';
//                $arrMessage['message'][]    = $langError['phone'];
//            }
//            else
//            {
//                $arrMessage['fields'][]     = 'phone';
//                $arrMessage['message'][]    = $langError['phone_valid'];
//            }
//
////            return ($returnMessage ? array(false, $arrMessage) : false);
//        }
//
//        if( !strlen($email) || !Validator::isEmail($email) )
//        {
//            $error = true;
//
//            if( !strlen($email) )
//            {
//                $arrMessage['fields'][]     = 'email';
//                $arrMessage['message'][]    = $langError['email'];
//            }
//            else
//            {
//                $arrMessage['fields'][]     = 'email';
//                $arrMessage['message'][]    = $langError['email_valid'];
//            }
//
////            return ($returnMessage ? array(false, $arrMessage) : false);
//        }
//
//        if( !strlen($shipping) || !strlen($payment) )
//        {
//            $error = true;
//
//            if( !strlen($shipping) )
//            {
//                $arrMessage['fields'][]     = 'shipping';
//                $arrMessage['message'][]    = $langError['shipping'];
//            }
//
//            if( !strlen($payment) )
//            {
//                $arrMessage['fields'][]     = 'payment';
//                $arrMessage['message'][]    = $langError['payment'];
//            }
//
////            return ($returnMessage ? array(false, $arrMessage) : false);
//        }
//
//        if( !strlen($shippingAddress) )
//        {
//            $arrMessage['fields'][]     = 'shipping_address';
//            $arrMessage['message'][]    = $langError['shipping_address'];
//        }
//
//        if( $shippingAddress === "other" || $shippingAddress === "other_address" )
//        {
//            $sh_name       = \Input::post("shipping_name");
//            $sh_name_fn    = \Input::post("shipping_name_firstname");
//            $sh_fn_name    = \Input::post("shipping_firstname_name");
//
//            $sh_city       = \Input::post("shipping_city");
//            $sh_postal     = \Input::post("shipping_postal");
//            $sh_street     = \Input::post("shipping_street");
//
//            $sh_country    = \Input::post("shipping_country");
//
//            if( !strlen($sh_name) )
//            {
//                $arrMessage['fields'][]     = 'shipping_name';
//                $arrMessage['message'][]    = $langError['name'];
//            }
//
//            if( !strlen($sh_name_fn) )
//            {
//                $arrMessage['fields'][]     = 'shipping_name_firstname';
//                $arrMessage['message'][]    = $langError['name'];
//            }
//
//            if( !strlen($sh_fn_name) )
//            {
//                $arrMessage['fields'][]     = 'shipping_firstname_name';
//                $arrMessage['message'][]    = $langError['name'];
//            }
//
//            if( !strlen($sh_city) )
//            {
//                $arrMessage['fields'][]     = 'shipping_city';
//                $arrMessage['message'][]    = $langError['city'];
//            }
//
//            if( !strlen($sh_postal) )
//            {
//                $arrMessage['fields'][]     = 'shipping_postal';
//                $arrMessage['message'][]    = $langError['postal'];
//            }
//
//            if( !strlen($sh_street) )
//            {
//                $arrMessage['fields'][]     = 'shipping_street';
//                $arrMessage['message'][]    = $langError['street'];
//            }
//
//            if( !strlen($sh_country) )
//            {
//                $arrMessage['fields'][]     = 'shipping_country';
//                $arrMessage['message'][]    = $langError['country'];
//            }
//        }
//
//        if( !strlen($agb) )
//        {
//            $arrMessage['fields'][]     = 'agb';
//            $arrMessage['message'][]    = $langError['agb'];
//        }
//
//        return ($returnMessage ? array($error, $arrMessage) : $error);
//    }



    protected function getAddress( $arrAddress )
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



    protected function getFormName()
    {
        $name = \Input::post("name");

        if( !strlen($name) )
        {
            $name = \Input::post("firstname_name");
        }

        if( !strlen($name) )
        {
            $name = \Input::post("name_firstname");
        }

        return $name;
    }



//    protected function getFormValues()
//    {
//        $arrValue = array();
//
//        $arrValue['name']               = \Input::post("name");
//        $arrValue['firstname']          = \Input::post("firstname");
//        $arrValue['lastname']           = \Input::post("lastname");
//        $arrValue['name_firstname']     = \Input::post("name_firstname");
//        $arrValue['firstname_name']     = \Input::post("firstname_name");
//
//        $arrValue['city']               = \Input::post("city");
//        $arrValue['postal']             = \Input::post("postal");
//        $arrValue['street']             = \Input::post("street");
//        $arrValue['country']            = \Input::post("country");
//
//        $arrValue['address']            = ((is_array(\Input::post("address")) ) ? $this->getAddress( \Input::post("address") ) : \Input::post("address"));
//
//        $arrValue['phone']              = \Input::post("phone");
//        $arrValue['email']              = \Input::post("email");
//
//        $arrValue['shipping']           = \Input::post("shipping");
//        $arrValue['payment']            = \Input::post("payment");
//
//        $arrValue['shipping_address']   = \Input::post("shipping_address");
//
//        $arrValue['agb']                = \Input::post("agb");
//
//
//        //Shipping Address
//        $arrValue['shipping_name']               = \Input::post("shipping_name");
//        $arrValue['shipping_firstname']          = \Input::post("shipping_firstname");
//        $arrValue['shipping_lastname']           = \Input::post("shipping_lastname");
//        $arrValue['shipping_name_firstname']     = \Input::post("shipping_name_firstname");
//        $arrValue['shipping_firstname_name']     = \Input::post("shipping_firstname_name");
//
//        $arrValue['shipping_city']               = \Input::post("shipping_city");
//        $arrValue['shipping_postal']             = \Input::post("shipping_postal");
//        $arrValue['shipping_street']             = \Input::post("shipping_street");
//
////        $arrValue['shipping_address']            = ((is_array(\Input::post("shipping_address")) ) ? $this->getAddress( \Input::post("shipping_address") ) : \Input::post("shipping_address"));
//
//        return $arrValue;
//    }
}