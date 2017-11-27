<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Config;


class ApiConfig
{

    public static function isActive( $apiName )
    {
        $objClass = self::getApi( $apiName );

        return $objClass->isActive();
    }



    public static function getApi( $apiName )
    {
        $strClass   = self::getClass( $apiName );
        return new $strClass();
    }



    public static function getImporter( $apiName )
    {
        if( self::isActive($apiName) )
        {
            $objClass       = self::getApi( $apiName );

            if( $objClass->hasImporter() )
            {
                $arrImporter = $objClass->getImporter();
                return new $arrImporter['classPath']();
            }
        }

        return false;
    }



    public static function getClass( $apiName )
    {
        return '\\IIDO\\ShopBundle\\API\\' . ucfirst($apiName) . 'Api';
    }
}