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
use IIDO\ShopBundle\Helper\PaymentHelper;


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



    public function __construct()
    {
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
        global $objPage;
        /* @var $objPage \PageModel */

        $environment = new SandboxEnvironment();

        $body = array
        (
            'intent' => 'sale',

            'transactions' => array
            (
                'amount' => array
                (
                    'total'         => $totalPrice,
                    'currency'      => $shopCurrency
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

        echo "<pre>";
        print_r( $environment );
        exit;
    }
}