<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\ShopBundle\Payment;


use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\ShopOrderHelper;


class CashOnDelivery extends DefaultPaymentMethod
{

    public function __construct()
    {
    }



    public function success()
    {
        if( \Input::get("mode") === "success" )
        {
            $orderID    = \Input::get('order');
            $objOrder   = ShopOrderHelper::getOrder( $orderID );

            $objApi     = ApiHelper::getApiObject();

            if( $objApi && !$objOrder->orderComplete )
            {

                $objApiOrder = $objApi->addNewOrder( $objOrder );
//                echo "<pre>"; print_r( $objApiOrder); exit;
                if( $objApiOrder )
                {
//                    $objOrder->hasPayed         = true;
                    $objOrder->orderComplete    = true;
//                    $objOrder->paymentInfo      = json_encode($paymentInfo);

                    $objOrder->apiOrderNumber   = $objApiOrder['orderNumber'];
                    $objOrder->apiCustomerNumber = $objApiOrder['customerNumber'];

                    $objOrder->save();

                    ShopConfig::removeCartList();
                    ShopOrderHelper::sendEmails( $objOrder, $objApiOrder );

                    return true;
                }
                else
                {
                    \Input::setGet("mode", "error");
                }
            }

            if( $objOrder->orderComplete )
            {
                \Input::setGet("mode", "error");
            }
        }

        return false;
    }



    public function error()
    {
        if( \Input::get("mode") === "error" )
        {
            return true;
        }
        return false;
    }



    public function newPayment()
    {
        if( \Input::get("mode") === "success" || \Input::get("mode") === "error" )
        {
            return;
        }

        global $objPage;
        /* @var $objPage \PageModel */

        $arrOrder       = ShopOrderHelper::getOrderArray();
        $objOrder       = ShopOrderHelper::addNewOrder( $arrOrder );

        $baseUrl        =  \Environment::get("base");
        $redirectUrl    = $baseUrl . $objPage->getFrontendUrl('/mode/success/payment/'. $objOrder->paymentMethod . '/order/' . $objOrder->id);

        \Controller::redirect( $redirectUrl );
    }
}