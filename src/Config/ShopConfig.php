<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Config;


use IIDO\ShopBundle\Helper\ApiHelper;


class ShopConfig
{
    /**
     * Cartlist Cookie name
     *
     * @var string
     */
    static $cartCookie      = 'iido_shop_cart';


    /**
     * Watchlist Cookie name
     *
     * @var string
     */
    static $watchlistCookie = 'iido_shop_watchlist';



    public static function getCartList()
    {
        $strCookie = $_COOKIE[ self::$cartCookie ];
        $arrCookie = json_decode($strCookie, TRUE);

        if( count($arrCookie) === 1 && !is_array($arrCookie[0]) )
        {
            $arrCookie = array();
        }

        return $arrCookie;
    }



    public static function getWatchlistList()
    {
        $arrCookie  = array();
        $strCookie  = $_COOKIE[ self::$watchlistCookie ];

        if( $strCookie !== "[null]" )
        {
            $arrCookie = json_decode($strCookie, TRUE);
        }

        return $arrCookie;
    }



    /**
     * Get products num in cart
     *
     * @return int
     */
    public static function getCartNum()
    {
        $arrCookie      = self::getCartList();
        $num            = 0;

        if( count($arrCookie) )
        {
            foreach($arrCookie as $cartProduct)
            {
                $num = ($num + (int) $cartProduct['quantity']);
            }
        }

        return $num;
    }



    /**
     * Get products num in watchlist
     *
     * @return int
     */
    public static function getWatchlistNum()
    {
        $arrCookie      = self::getWatchlistList();
        $num            = 0;

        if( count($arrCookie) )
        {
            foreach($arrCookie as $cartProduct)
            {
                $num = ($num + (int) $cartProduct['quantity']);
            }
        }

        return $num;
    }



    public static function getProduct( $itemNumber )
    {
        $objProduct = false;
        $objApi     = ApiHelper::getApiObject();

        if( $objApi )
        {
            $arrProduct = $objApi->runApiUrl('article/?articleNumber-eq=' . $itemNumber);
            $objProduct = new \stdClass();

            $objProduct->itemNumber     = $arrProduct['articleNumber'];
            $objProduct->price          = $arrProduct['articlePrices'][0]['price'];
            $objProduct->name           = $arrProduct['name'];
        }

        return $objProduct;
    }



    public static function getCurrency( $getAsText = false )
    {
        \Controller::loadLanguageFile("iido_shop");
        $prefix = BundleConfig::getTableFieldPrefix();
        return $GLOBALS['TL_LANG']['iido_shop']['currency' . ($getAsText ? '_text' : '')][ \Config::get( $prefix . 'currency' ) ];
    }
}