<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;



use IIDO\ShopBundle\Helper\PaymentHelper;


class OrderCompleteElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_orderComplete';



    /**
     * Generate order complete element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### SHOP: ORDER COMPLETE ###';
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
        $objPayment         = PaymentHelper::getObject( \Input::post("payment") );
        $objPaymentMethod   = PaymentHelper::getMethod( $objPayment );

        $objPaymentMethod->newPayment();
//        echo "<pre>"; print_r( $objPaymentMethod ); exit;
    }
}