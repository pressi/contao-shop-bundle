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
use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\PaymentHelper;
use IIDO\ShopBundle\Helper\ShippingHelper;
use IIDO\ShopBundle\Helper\ShopCheckOutHelper;
use IIDO\ShopBundle\Helper\ShopHelper;


class OrderOverviewElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_orderOverview';



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

            $objTemplate->wildcard  = '### SHOP: ORDER OVERVIEW ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }



    /**
     * Compile the content element
     */
    protected function compile()
    {
        global $objPage;

        \Controller::loadLanguageFile("iido_shop_checkout");

        $arrLang    = $GLOBALS['TL_LANG']['iido_shop_checkout'];
        $arrValue   = ShopCheckOutHelper::getFormInputs( true );

        ShopCheckOutHelper::setFormFields();

        list($hasError, $errorMessage) = ShopCheckOutHelper::checkForm( true );

        if( (!\Input::post("FORM_SUBMIT") || $hasError) && $this->iidoShopCartJumpTo )
        {
            ShopCheckOutHelper::setFormError( $hasError );
            ShopCheckOutHelper::setFormErrorMessage( $errorMessage );

            \Controller::redirect( \PageModel::findByPk( $this->iidoShopCartJumpTo )->getFrontendUrl() );
        }
        else
        {
            ShopCheckOutHelper::setFormError( false );
            ShopCheckOutHelper::setFormErrorMessage( array() );
        }

//        if( \Input::post("FORM_SUBMIT") === "checkout_form" )
//        {
//            list($hasError, $errorMessage) = ShopCheckOutHelper::checkForm( true );
//
//            if( (\Input::post("FORM_SUBMIT") || $hasError) && $this->iidoShopCartJumpTo )
//            {
//                ShopCheckOutHelper::setFormError( $hasError );
//                ShopCheckOutHelper::setFormErrorMessage( $errorMessage );
//
//                \Controller::redirect( \PageModel::findByPk( $this->iidoShopCartJumpTo )->getFrontendUrl() );
//            }
//        }
//        else
//        {
//            if( ShopCheckOutHelper::checkForm() )
//            {
//                if( $this->iidoShopCartJumpTo )
//                {
//                    \Controller::redirect( \PageModel::findByPk( $this->iidoShopCartJumpTo )->getFrontendUrl() );
//                }
//            }
//        }

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

        $arrProducts        = array();
        $intCartPrice       = 0;
        $intShippingPrice   = 0;

        foreach(ShopConfig::getCartList() as $item)
        {
            $arrShopProduct = ShopHelper::getProduct( $item );
            $intPrice       = ($arrShopProduct['intPrice'] * $item['quantity']);

            $arrProducts[] = $arrShopProduct;

            $intCartPrice = ($intCartPrice + $intPrice);
        }

        $objShipping = ShippingHelper::getObject( $arrValue['shipping']['value'] );

        if( $objShipping )
        {
            $arrOverview['shipping'] = ($objShipping->frontendTitle?:$objShipping->name);

//            $intShippingPrice = ($intShippingPrice + $objShipping->price);

            $countryCode = $arrValue['country']['value'];

            if( $arrValue['shipping_address'] === "other" )
            {
                $countryCode = $arrValue['shipping_country']['value'];
            }

            $intShippingPrice = ($intShippingPrice + ShippingHelper::getShippingPrice($countryCode, $objShipping, $intCartPrice));
//echo "<pre>";
//print_r( $intShippingPrice ); exit;
//            if( $objShipping->enablePricePerCountry )
//            {
//
//
//                $arrCountries = \StringUtil::deserialize( $objShipping->pricePerCountry, TRUE );
//
//                foreach( $arrCountries as $arrCountry )
//                {
//                    if( $arrCountry['country'] === $countryCode )
//                    {
//                        $intShippingPrice = $arrCountry['price'];
//                    }
//                }
//            }

//            if( $objShipping->freeOnPriceLimit )
//            {
//                $priceLimit     = $objShipping->freeOnCartPrice;
//                $freeCountries  =\StringUtil::deserialize( $objShipping->freeOnlyPerCountry, TRUE );
//
//                if( count($freeCountries) )
//                {
//                    foreach( $freeCountries as $freeCountry )
//                    {
//                        if( $freeCountry['country'] === $countryCode )
//                        {
//                            $priceLimit = $freeCountry['freeOnCartPrice']?:$priceLimit;
//                            break;
//                        }
//                    }
//                }
//
//                if( $intCartPrice >= $priceLimit )
//                {
//                    $intShippingPrice = 0;
//                }
//            }
        }

//        $intCartPrice = ($intCartPrice + $intShippingPrice);

        $objPayment = PaymentHelper::getObject( $arrValue['payment']['value'] );

        if( $objPayment )
        {
            $arrOverview['payment'] = PaymentHelper::getOverview( $objPayment );
        }

        $arrValue['fullName']['value']          = ShopCheckOutHelper::getFullName();
        $arrValue['shippingFullName']['value']  = ShopCheckOutHelper::getFullName( 'shipping_' );

        $arrValue['country']['key']             = $arrValue['country']['value'];
        $arrValue['country']['value']           = ShopCheckOutHelper::getCountry( $arrValue['country']['value'] );

        $arrValue['shipping_country']['key']    = $arrValue['shipping_country']['value'];
        $arrValue['shipping_country']['value']  = ShopCheckOutHelper::getCountry( $arrValue['shipping_country']['value'] );

        $this->Template->products       = $arrProducts;
        $this->Template->value          = $arrValue;
        $this->Template->overview       = $arrOverview;

        $this->Template->cartPrice      = $intCartPrice;
        $this->Template->shippingPrice  = $intShippingPrice;

        $this->Template->label          = $arrLang['label'];

        $this->Template->cartLink       = $cartLink;
        $this->Template->forwardLink    = $forwardLink;

        $this->Template->hiddenFields   = $arrValue;
    }
}