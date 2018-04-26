<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\ShopBundle\API\DefaultApi;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Config\BundleConfig;


class ApiHelper
{

    public static function isApiEnabled( $apiName )
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $arrApis            = $GLOBALS['IIDO']['SHOP']['API'];

        if( key_exists($apiName, $arrApis) )
        {
            $enabled = \Config::get($tableFieldPrefix . 'enable' . ucfirst($apiName) . 'Api');

            if( $enabled )
            {
                return true;
            }
        }

        return false;
    }



    public static function enableApis( $returnName = false )
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $arrApis            = $GLOBALS['IIDO']['SHOP']['API'];

        foreach($arrApis as $apiName => $apiFields)
        {
            $API = \Config::get($tableFieldPrefix . 'enable' . ucfirst($apiName) . 'Api');

            if( $API )
            {
                return $returnName ? $apiName : true;
            }
        }

        return false;
    }



    public static function getApiObject( $apiName = '' )
    {
        if( $apiName )
        {
            if( self::isApiEnabled( $apiName ) )
            {
                $apiClass = ApiConfig::getClass( $apiName );

                return new $apiClass;
            }
        }

        $arrApis = $GLOBALS['IIDO']['SHOP']['API'];

        foreach($arrApis as $api => $apiFields)
        {
            if( self::isApiEnabled( $api) )
            {
                $apiClass = ApiConfig::getClass( $api );

                return new $apiClass();
            }
        }

        return null;
    }



    public static function getUrlPath()
    {
        $objApi = new DefaultApi();

        return $objApi->getUrlPath();
    }
}