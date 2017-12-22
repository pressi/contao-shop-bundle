<?php
/*******************************************************************
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Config;


class ShopConfig
{
    static $cartCookie = 'iido_shop_cart';
    static $watchlistCookie = 'iido_shop_watchlist';


    public static function getCartList()
    {
        return json_decode($_COOKIE[ self::$cartCookie ], TRUE);
    }



    public static function getWatchlistList()
    {
        return json_decode($_COOKIE[ self::$watchlistCookie ], TRUE);
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
}