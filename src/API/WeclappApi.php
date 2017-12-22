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
class WeclappApi extends DefaultApi
{
    /**
     * API Name
     * @var string
     */
    protected $apiName = 'weclapp';


    /**
     * Active Importer
     *
     * @var boolean
     */
    protected $activeImporter = true;


    /**
     * Color Codes
     *
     * @var array
     */
    protected $colorCodes = array
    (
        'AB'    => 'aqua_black',
        'BA'    => 'black_aqua',

        'BB'    => 'black_black',

        'BW'    => 'black_white',
        'WB'    => 'white_black',

        'BCB'   => 'black_cblue',
        'CBB'   => 'cblue_black',

        'BY'    => 'black_yellow',
        'YB'    => 'yellow_black',

        'MB'    => 'magenta_black',
        'BM'    => 'black_magenta',
    );


    /**
     * Flex Codes
     *
     * @var array
     */
    protected $flexCode = array
    (
        'XXX'   => 'Weich',
        'YYY'   => 'Normal',
        'ZZZ'   => 'Hart'
    );


    /**
     * API URL Version
     *
     * @var string
     */
    var $version = 'v1';


    /**
     * API Url
     *
     * @var string
     */
    protected $apiUrl;



    public function __construct()
    {
    }



    public function getProductList()
    {
        $arrProducts = array();
        $productCount   = $this->runApiUrl( 'article/count' );
        $apiProducts    = $this->runApiUrl( 'article/?pageSize=1000' );
echo "<pre>"; print_r( $productCount );
echo "<br>";
print_r( $apiProducts ); exit;
        foreach($apiProducts as $arrProduct)
        {
            if( $arrProduct['active'] === "1" || $arrProduct['active'] === 1 || $arrProduct['active'] )
            {
                $arrProducts[] = $arrProduct;
            }
        }

        return $arrProducts;
    }



    public function getColorCode( $colorKey )
    {
        return $this->colorCodes[ $colorKey ];
    }



    public function getFlexName( $flexKey )
    {
        return $this->flexCode[ $flexKey ];
    }



    public function runApiUrl( $urlParams, $returnVar = '' )
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

        $arrOutput = json_decode($out, TRUE);

        if( $arrOutput['error'] )
        {
            return $arrOutput['error'];
        }

        $arrReturn = $returnVar ? $arrOutput[ $returnVar ] : ((isset($arrOutput['result'])) ? $arrOutput['result'] : $arrOutput);

        if( count($arrReturn) === 1 && preg_match('/\-eq/', $urlParams) )
        {
            $arrReturn = $arrReturn[0];
        }

        return $arrReturn;
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