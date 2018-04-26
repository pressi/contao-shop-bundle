<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\ShopBundle\Config\BundleConfig;

use IIDO\ShopBundle\Model\IidoShopShippingModel;


class ShippingHelper
{

    public static function get( $shipping, $varName )
    {
        $objShipping = IidoShopShippingModel::findByIdOrAlias( $shipping );

        return $objShipping->$varName;
    }



    public static function getObject( $shipping )
    {
        $objShipping = IidoShopShippingModel::findByIdOrAlias( $shipping );

        return $objShipping;
    }



    public static function getAllTypes()
    {
        $arrShippings   = array();
        $objShippings   = self::getAllFromFile();

        if( is_array($objShippings) && count($objShippings) )
        {
            foreach( $objShippings as $shippingAlias => $shipping )
            {
                if( $shipping['active'] || $shipping['active'] === "1" || $shipping['active'] === 1 )
                {
                    $arrShippings[ $shippingAlias ] = $shipping['name'];
                }
            }
        }

        return $arrShippings;
    }



    public static function getAllFromFile()
    {
        return json_decode( file_get_contents(BasicHelper::getRootDir() . '/' . BundleConfig::getBundlePath() . '/src/Resources/config/shippings.json'), TRUE );
    }



    public static function getAll()
    {
        $strTable       = IidoShopShippingModel::getTable();
        $arrShippings   = array();
        $objShippings   = self::getAllFromFile();

        if( is_array($objShippings) && count($objShippings) )
        {
            foreach( $objShippings as $shippingAlias => $shipping )
            {
                if( $shipping['active'] || $shipping['active'] === "1" || $shipping['active'] === 1 )
                {
                    $objShipping = IidoShopShippingModel::findByIdOrAlias( $shippingAlias );

                    if( !$objShipping )
                    {
                        $objShipping = \Database::getInstance()->prepare("SELECT * FROM " . $strTable . " WHERE type=?")->limit(1)->execute( $shippingAlias );

                        if( $objShipping && $objShipping->count() )
                        {
                            $objShipping = $objShipping->first();
                        }
                    }

                    if( $objShipping && $objShipping->published )
                    {
                        $arrShippings[] = array
                        (
                            'name'      => $objShipping->name ?: $shipping['name'],
                            'alias'     => $shippingAlias,
                            'active'    => $shipping['active'],
                            'info'      => $objShipping->info
                        );
                    }
                }
            }
        }

        return $arrShippings;
    }
}