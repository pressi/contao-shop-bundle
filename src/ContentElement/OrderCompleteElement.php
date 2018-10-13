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
        $payment            = \Input::post("payment")?:\Input::get("payment");
        $objPayment         = PaymentHelper::getObject( $payment );
        $arrVars            = array();

        if( $payment === "paypal" )
        {
            $arrVars = array($objPayment->clientID, $objPayment->clientSecret);
        }

        $objPaymentMethod   = PaymentHelper::getMethod( $objPayment, $arrVars );

        $this->Template->orderComplete = FALSE;
        $this->Template->noOrder = FALSE;

        if( $objPaymentMethod )
        {
            if( $objPaymentMethod->success() )
            {
//            $objPaymentMethod->updateOrder();
                $this->Template->orderComplete = TRUE;
            }
            elseif( $objPaymentMethod->error() )
            {
                if( $this->iidoShopCartJumpTo )
                {
                    $objBackPage = \PageModel::findByPk( $this->iidoShopCartJumpTo );

                    if( $objBackPage )
                    {
                        \Controller::redirect( $objBackPage->getFrontendUrl() );
                    }
                }
            }
            else
            {
                $objPaymentMethod->newPayment();
            }
        }
        else
        {
            $this->Template->noOrder = TRUE;
        }
    }
}