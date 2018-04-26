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

        $objShipping = ShippingHelper::getObject( $arrValue['shipping'] );

        if( $objShipping )
        {
            $arrOverview['shipping'] = ($objShipping->frontendTitle?:$objShipping->name);

            $intShippingPrice = ($intShippingPrice + $objShipping->price);
        }

        $objPayment = PaymentHelper::getObject( $arrValue['payment'] );

        if( $objPayment )
        {
            $arrOverview['payment'] = PaymentHelper::getOverview( $objPayment );
        }

        $arrValue['fullName']           = ShopCheckOutHelper::getFullName();
        $arrValue['shippingFullName']   = ShopCheckOutHelper::getFullName( 'shipping_' );

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