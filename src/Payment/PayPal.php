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

//TODO: REMOVE UNUSED CODE!!!!

use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\PaymentHelper;
use IIDO\ShopBundle\Helper\ShippingHelper;
use IIDO\ShopBundle\Helper\ShopOrderHelper;


//use PayPal\v1\Payments\PaymentCreateRequest;
//use PayPal\Core\PayPalHttpClient;
//use PayPal\Core\SandboxEnvironment;
//use PayPal\Core\ProductionEnvironment;


class PayPal extends DefaultPaymentMethod
{
    protected $isDev        = false;

    protected $apiUrl       = 'https://api.paypal.com/';
    protected $devApiUrl    = 'https://api.sandbox.paypal.com/';

    protected $version      = 'v1/';

    protected $authPath     = 'oauth2/token';

    protected $startConnection  = 0;
    protected $expireConnection = 32398;

    protected $accessToken;

//    protected $username;
//    protected $password;
//    protected $signature;
//    protected $app_id;

    protected $clientID;
    protected $clientSecret;



    public function __construct($clientID, $clientSecret)
    {
        if( $clientID )
        {
            $this->setClientID( $clientID );
        }

        if( $clientSecret )
        {
            $this->setClientSecret( $clientSecret );
        }
    }



    public function success()
    {
        $objApi = ApiHelper::getApiObject();

//        $objPDF = $objApi->runApiUrl("/unit");
//        $objPDF = $objApi->runApiUrl("/salesOrder?orderNumber-eq=1102");
//        $objPDF = $objApi->runApiUrl("/salesOrder/id/124258/downloadLatestOrderConfirmationPdf");
//        echo "<pre>"; print_r( $objPDF ); exit;

        if( \Input::get("mode") === "success" )
        {
            $orderID    = \Input::get('order');
            $objOrder   = ShopOrderHelper::getOrder( $orderID );

            $objApi     = ApiHelper::getApiObject();

            if( $objApi && !$objOrder->orderComplete )
            {
                $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        $this->getClientID(),
                        $this->getClientSecret()
                    )
                );

                $apiContext->setConfig(
                    array
                    (
                        'mode' => $this->isDev ? 'sandbox' : 'live',

                        'log.LogEnabled' => true,
                        'log.FileName' => '../PayPal-2.log',
                        'log.LogLevel' => $this->isDev ? 'DEBUG' : 'INFO'
                    )
                );

                $payment = \PayPal\Api\Payment::get( \Input::get("paymentId"), $apiContext );
                $execution = new \PayPal\Api\PaymentExecution();
                $execution->setPayerId( \Input::get("PayerID") );

                $runPayment = false;

                try
                {
                    $payment->execute($execution, $apiContext);
                    $runPayment = true;
                }
                catch (\PayPal\Exception\PayPalConnectionException $ex )
                {
                    \Input::setGet("mode", "error");
                    $runPayment = false;

                    echo $ex->getData();
                    exit;
                }

                $paymentState = $payment->getState();

                if( $runPayment && $paymentState === "approved" )
                {
                    $objApiOrder = $objApi->addNewOrder( $objOrder );

                    if( $objApiOrder )
                    {
                        $paymentInfo = array
                        (
                            'paymentID' => \Input::get("paymentId"),
                            'payerID'   => \Input::get("PayerID"),
                            'token'     => \Input::get("token"),

                            'transactionID' => $payment->getId(),
                            'paymentAmount' => $payment->transactions[0]->amount->total,
                            'paymentStatus' => $paymentState,
                            'invoiceID'     => $payment->transactions[0]->invoice_number
                        );

                        $objOrder->hasPayed         = true;
                        $objOrder->orderComplete    = true;
                        $objOrder->paymentInfo      = json_encode($paymentInfo);

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
//            echo "<pre>E<br>";
//            print_r( $_GET );
//            exit;
            return true;
        }

        return false;
    }


    public function setClientID( $clientID )
    {
        $this->clientID = $clientID;
    }



    public function getClientID()
    {
        return $this->clientID;
    }



    public function setClientSecret( $clientSecret )
    {
        $this->clientSecret = $clientSecret;
    }



    public function getClientSecret()
    {
        return $this->clientSecret;
    }



//    public function setUsername( $strUsername )
//    {
//        $this->username = $strUsername;
//    }



//    public function setPassword( $strPassword )
//    {
//        $this->password = $strPassword;
//    }



//    public function setSignature( $strSignature )
//    {
//        $this->signature = $strSignature;
//    }



    public function runUrl( $path, $method = 'GET', $isAuth = false )
    {
//        $auth = [
//            'Content-Type: application/json'
//        ];
//
//        if( $isAuth )
//        {
//            $auth[] = 'Accept-Language: de_DE';
//            $auth[] = $this->username . ':' . $this->password;
//            $auth[] = 'grant_type=client_credentials';
//        }
//        else
//        {
//            $auth[] = 'Authorization: Basic ' . $this->getAccessToken();
//        }

        $auth =
            [
                'Content-Type: application/json',
                'Accept-Language: de_DE',
                'Authorization: Basic ' . self::getAuthCode()
            ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $this->getUrl() . $path);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $out = curl_exec($ch);
        curl_close($ch);
echo "<pre>"; print_r( $out ); exit;
        return 'token';
    }



    protected function getUrl()
    {
        return ($this->isDev ? $this->devApiUrl : $this->apiUrl) . $this->version;
    }



//    protected function auth()
//    {
//        $this->runUrl($this->authPath, 'POST', true);
//    }



//    protected function getAccessToken()
//    {
//        if( $this->startConnection === 0 || ($this->startConnection > 0 && time() > ($this->startConnection + $this->expireConnection)) )
//        {
//            $this->startConnection  = time();
//            $this->accessToken = $this->auth();
//        }
//
//        return $this->accessToken;
//    }




    protected static function getAuthCode()
    {
        $objPayment = PaymentHelper::getObject( "paypal" );

        if( $objPayment )
        {
            return base64_encode( $objPayment->username . ':' . $objPayment->password );
        }

        return '';
    }



    public function newPayment()
    {
        $this->runVersion1Payment();
        return;


//        global $objPage;
//        /* @var $objPage \PageModel */
//
//        $totalPrice = 0;
//
//        if( $this->isDev )
//        {
//            $environment = new SandboxEnvironment($clientID, $clientSecret);
//        }
//        else
//        {
//            $environment = new ProductionEnvironment($clientID, $clientSecret);
//        }
//
//        $client = new PayPalHttpClient($environment);
//
//        $body = array
//        (
//            'intent' => 'sale',
//
//            'transactions' => array
//            (
//                'amount' => array
//                (
//                    'total'         => $totalPrice,
//                    'currency'      => ShopConfig::getCurrency( true )
//                )
//            ),
//
//            'redirect_urls' => array
//            (
//                'cancel_url'    => $objPage->getFrontendUrl('mode/error'),
//                'return_url'    => $objPage->getFrontendUrl()
//            ),
//
//            'payer' => array
//            (
//                'payment_method' => 'paypal'
//            )
//        );
//echo "<pre>"; print_r( $body ); exit;
//        $request = new PaymentCreateRequest();
//        $request->body = $body;
//
//        try
//        {
//            return $client->execute($request);
//        }
//        catch( HttpException $ex )
//        {
//            echo $ex->statusCode;
//            print_r( $ex->getMessage() );
//        }
    }



    protected function runVersion1Payment()
    {
        if( \Input::get("mode") === "success" || \Input::get("mode") === "error" )
        {
            return;
        }

        global $objPage;
        /* @var $objPage \PageModel */

        list($arrOrder, $totalPrice) = ShopOrderHelper::getOrderArray(true);
        $objOrder       = ShopOrderHelper::addNewOrder( $arrOrder );

        $clientID       = $this->getClientID();
        $clientSecret   = $this->getClientSecret();
        $baseUrl        =  \Environment::get("base");

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $clientID,
                $clientSecret
            )
        );

        $apiContext->setConfig(
            array
            (
                'mode' => $this->isDev ? 'sandbox' : 'live',

                'log.LogEnabled' => true,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => $this->isDev ? 'DEBUG' : 'INFO'
            )
        );

//        $payerAddress = new \PayPal\Api\Address();
//        $payerAddress->setLine1( $arrOrder['street'] );
//        $payerAddress->setPostalCode( $arrOrder['postal'] );
//        $payerAddress->setCity( $arrOrder['city'] );
//        $payerAddress->setCountryCode('AT');

//        $payerInfo = new \PayPal\Api\PayerInfo();
//        $payerInfo->setFirstName( $arrOrder['name'] );
//        if( $arrOrder['phone'] ) { $payerInfo->setPhone( $arrOrder['phone'] ); }
//        $payerInfo->setEmail( $arrOrder['email'] );
//        $payerInfo->setBillingAddress( $payerAddress );

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');
//        $payer->setPayerInfo( $payerInfo );

//        $details = new \PayPal\Api\Details();
//        $details->setShipping( $shippingPrice );
//        $details->setTax(0.2);

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal( $totalPrice );
        $amount->setCurrency( ShopConfig::getCurrency( true ) );
//        $amount->setDetails( $details );

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount( $amount );

        $redirectUrls = new \PayPal\Api\RedirectUrls();
//        $redirectUrls->setReturnUrl( $baseUrl . $objPage->getFrontendUrl() . '/mode/success/order/' . $objOrder->id )
//        $redirectUrls->setReturnUrl( $baseUrl . $objPage->getFrontendUrl('/mode/success/payment/'. $arrOrder['paymentMethod'] . '/order/5') )
        $redirectUrls->setReturnUrl( $baseUrl . $objPage->getFrontendUrl('/mode/success/payment/'. $arrOrder['paymentMethod'] . '/order/' . $objOrder->id) )
            ->setCancelUrl( $baseUrl . $objPage->getFrontendUrl('/mode/error/payment/' . $arrOrder['paymentMethod']) );

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer( $payer )
            ->setTransactions( array($transaction) )
            ->setRedirectUrls( $redirectUrls );

        try
        {
            $payment->create($apiContext);
//            echo $payment;
            \Controller::redirect( $payment->getApprovalLink() );
        }
        catch (\PayPal\Exception\PayPalConnectionException $ex)
        {
            echo $ex->getData();
            exit;
        }
    }
}