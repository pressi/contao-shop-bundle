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



use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\PaymentHelper;

//use PayPal\v1\Payments\PaymentCreateRequest;
//use PayPal\Core\PayPalHttpClient;
//use PayPal\Core\SandboxEnvironment;
//use PayPal\Core\ProductionEnvironment;


class PayPal
{
    protected $isDev        = true;

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


        global $objPage;
        /* @var $objPage \PageModel */

        $totalPrice = 0;

        if( $this->isDev )
        {
            $environment = new SandboxEnvironment($clientID, $clientSecret);
        }
        else
        {
            $environment = new ProductionEnvironment($clientID, $clientSecret);
        }

        $client = new PayPalHttpClient($environment);

        $body = array
        (
            'intent' => 'sale',

            'transactions' => array
            (
                'amount' => array
                (
                    'total'         => $totalPrice,
                    'currency'      => ShopConfig::getCurrency( true )
                )
            ),

            'redirect_urls' => array
            (
                'cancel_url'    => $objPage->getFrontendUrl('mode/error'),
                'return_url'    => $objPage->getFrontendUrl()
            ),

            'payer' => array
            (
                'payment_method' => 'paypal'
            )
        );
echo "<pre>"; print_r( $body ); exit;
        $request = new PaymentCreateRequest();
        $request->body = $body;

        try
        {
            return $client->execute($request);
        }
        catch( HttpException $ex )
        {
            echo $ex->statusCode;
            print_r( $ex->getMessage() );
        }
    }



    protected function runVersion1Payment()
    {
        global $objPage;

        $totalPrice     = 0;
        $clientID       = $this->getClientID();
        $clientSecret   = $this->getClientSecret();

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $clientID,
                $clientSecret
            )
        );

        $apiContext->setConfig( array('mode' => 'live') );

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal( $totalPrice );
        $amount->setCurrency( ShopConfig::getCurrency( true ) );

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount( $amount );

        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl( $objPage->getFrontendUrl() )
            ->setCancelUrl( $objPage->getFrontendUrl('mode/error') );

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer( $payer )
            ->setTransactions( array($transaction) )
            ->setRedirectUrls( $redirectUrls );

        try
        {
            $payment->create($apiContext);
            echo $payment;
        }
        catch (\PayPal\Exception\PayPalConnectionException $ex)
        {
            echo $ex->getData();
        }
    }
}