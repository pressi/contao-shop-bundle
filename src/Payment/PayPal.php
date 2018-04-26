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

    protected $username;
    protected $password;
    protected $signature;
    protected $app_id;



    public function __construct()
    {
    }



    public function setUsername( $strUsername )
    {
        $this->username = $strUsername;
    }



    public function setPassword( $strPassword )
    {
        $this->password = $strPassword;
    }



    public function setSignature( $strSignature )
    {
        $this->signature = $strSignature;
    }



    public function runUrl( $path, $method = 'GET', $isAuth = false )
    {
        $auth = [
            'Content-Type: application/json'
        ];

        if( $isAuth )
        {
            $auth[] = 'Accept-Language: de_DE';
            $auth[] = $this->username . ':' . $this->password;
            $auth[] = 'grant_type=client_credentials';
        }
        else
        {
            $auth[] = 'Authorization: Bearer ' . $this->getAccessToken();
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $this->getUrl() . $path);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $out = curl_exec($ch);
        curl_close($ch);

        return 'token';
    }



    protected function getUrl()
    {
        return ($this->isDev ? $this->devApiUrl : $this->apiUrl) . $this->version;
    }



    protected function auth()
    {
        $this->runUrl($this->authPath, 'POST', true);
    }



    protected function getAccessToken()
    {
        if( $this->startConnection === 0 || ($this->startConnection > 0 && time() > ($this->startConnection + $this->expireConnection)) )
        {
            $this->startConnection  = time();
            $this->accessToken = $this->auth();
        }

        return $this->accessToken;
    }
}