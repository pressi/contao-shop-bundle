<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\ShopBundle\API;
use IIDO\ShopBundle\Config\BundleConfig;


/**
 * Weclapp API
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class WeclappApi
{
    /**
     * Version
     *
     * @var string
     */
    var $version = 'v1';


    protected $apiUrl;



    public function __construct()
    {
    }



    public function getProductList()
    {
        $arrProducts = array();
        $productCount   = json_decode($this->runApiUrl( 'article/count' ), TRUE);
        $apiProducts    = json_decode($this->runApiUrl( 'article/?pageSize=1000' ), TRUE);
echo "<pre>"; print_r( $productCount );
echo "<br>";
print_r( $apiProducts ); exit;
        foreach($apiProducts['result'] as $arrProduct)
        {
            if( $arrProduct['active'] === "1" || $arrProduct['active'] === 1 || $arrProduct['active'] )
            {
                $arrProducts[] = $arrProduct;
            }
        }

        return $arrProducts;
    }



    public function runApiUrl( $urlParams )
    {
        $auth = [
            'Content-Type: application/json',
            'AuthenticationToken: '. $this->getToken()
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $this->getURL() . $urlParams);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $out = curl_exec($ch);
        curl_close($ch);
//echo "<pre>"; print_r( $out ); exit;
        return $out;
    }



    protected function getURL()
    {
        $prefix = BundleConfig::getTableFieldPrefix();
        $tenant = \Config::get( $prefix . 'weclappTenant' );

        return 'https://' . $tenant . '.weclapp.com/webapp/api/' . $this->version . '/';
    }



    protected function getToken()
    {
        $prefix = BundleConfig::getTableFieldPrefix();
        return \Config::get( $prefix . 'weclappToken' );
    }

}