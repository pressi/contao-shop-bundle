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

namespace IIDO\ShopBundle\Helper;


use IIDO\ShopBundle\Config\BundleConfig;


class ApiHelper
{

    public static function isApiEnabled( $returnName = false)
    {
        $tableFieldPrefix   = BundleConfig::getTableFieldPrefix();
        $arrApis            = $GLOBALS['IIDO']['SHOP']['API'];

        foreach($arrApis as $apiName)
        {
            $API = \Config::get($tableFieldPrefix . 'enable' . ucfirst($apiName) . 'Api');

            if( $API )
            {
                return $returnName ? $apiName : true;
            }
        }

        return false;
    }
}