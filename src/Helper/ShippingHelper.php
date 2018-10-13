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
use IIDO\ShopBundle\Config\ShopConfig;
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



    public static function getShippingPrice( $countryCode, $id, $intCartPrice = 0, $returnApiArticle = false, $toNotCheckFreePrice = false )
    {
        \Controller::loadLanguageFile("countries");

        $price      = 0;
        $objItem    = is_numeric($id) ? IidoShopShippingModel::findByPk( $id ) : $id;
        $apiArticle = false;

        if( $objItem )
        {
            if( $objItem->enablePricePerCountry )
            {
                $arrPrices      = \StringUtil::deserialize($objItem->pricePerCountry, TRUE);

                $arrCountries           = array();
                $arrCheckedCountries    = array();

                foreach($arrPrices as $arrPrice)
                {
                    $arrCountries[ $arrPrice['country'] ] = $arrPrice['country'];
                }

                foreach($arrPrices as $arrCountryPrice)
                {
                    if( $arrCountryPrice['country'] == "eu" )
                    {
                        foreach($GLOBALS['TL_LANG']['SHOP']['countries']['eu'] as $key => $countryName)
                        {
                            if( !array_key_exists($key, $arrCheckedCountries) && !array_key_exists($key, $arrCountries) )
                            {
                                if( $key === $countryCode )
                                {
                                    $apiArticle = $arrCountryPrice['apiArticle'];
                                    $price      = $arrCountryPrice['price'];

                                    $arrCheckedCountries[ $key ] = $key;
                                    break;
                                }
                            }
                        }

                        if( $price > 0 )
                        {
                            break;
                        }
                    }
                    elseif( $arrCountryPrice['country'] == "world" )
                    {
                        foreach($GLOBALS['TL_LANG']['CNT'] as $key => $countryName)
                        {
                            if( !array_key_exists($key, $arrCheckedCountries) && !array_key_exists($key, $arrCountries) )
                            {
                                if( $key === $countryCode )
                                {
                                    $apiArticle = $arrCountryPrice['apiArticle'];
                                    $price      = $arrCountryPrice['price'];

                                    $arrCheckedCountries[ $key ] = $key;
                                    break;
                                }
                            }
                        }

                        if( $price > 0 )
                        {
                            break;
                        }
                    }
                    else
                    {
                        if( $arrCountryPrice['country'] === $countryCode )
                        {
                            $apiArticle = $arrCountryPrice['apiArticle'];
                            $price      = $arrCountryPrice['price'];
                            break;
                        }
                    }

                    $arrCheckedCountries[ $arrCountryPrice['country'] ] = $arrCountryPrice['country'];
                }

                if( $price > 0 && $objItem->freeOnPriceLimit && !$toNotCheckFreePrice )
                {
                    $freePrice  = $objItem->freeOnCartPrice;
                    $cartPrice  = $intCartPrice ? : ShopHelper::getCartPrice();

                    $arrFreeCountries   = \StringUtil::deserialize($objItem->freeOnlyPerCountry, TRUE);

                    if( count($arrFreeCountries) )
                    {
                        $usedCountry = false;
                        foreach( $arrFreeCountries as $arrFreeCountry)
                        {
                            if( $arrFreeCountry['country'] === $countryCode )
                            {
                                $usedCountry    = true;
                                $freePrice      = $arrFreeCountry['freeOnCartPrice']?:$freePrice;
                                break;
                            }
                        }

                        if( $freePrice <= $cartPrice && $usedCountry )
                        {
                            $price = 0;
                        }
                    }
                    else
                    {
                        if( $freePrice <= $cartPrice )
                        {
                            $price = 0;
                        }
                    }

                }
            }
        }

        return $returnApiArticle ? array($price, $apiArticle) : $price;
    }
}