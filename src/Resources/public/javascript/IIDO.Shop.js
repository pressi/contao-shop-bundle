/******************************************************/
/*                                                    */
/*  (c) 2017 IIDO, www.iido.at <development@iido.at>  */
/*                                                    */
/******************************************************/
var IIDO = IIDO || {};
IIDO.Shop = IIDO.Shop || {};

IIDO.Shop.Cart      = IIDO.Shop.Cart || {};
IIDO.Shop.Watchlist = IIDO.Shop.Watchlist || {};

(function(window, $, shopCart)
{
    var $cookieName = 'iido_shop_cart';



    shopCart.addProductToCart = function( product )
    {
        var added       = false,
            cartList    = this.getList();

        for(var num=0; num<cartList.length; num++)
        {
            var productInCart = cartList[ num ];

            if( productInCart.itemNumber === product.itemNumber )
            {
                added = true;

                cartList[ num ].quantity = (productInCart.quantity + product.quantity);
            }
        }

        if( !added )
        {
            cartList.push( product );
        }

        this.updateList( cartList );
    };



    shopCart.getList = function()
    {
        var cookie = $.cookie($cookieName);

        if( cookie )
        {
            cookie = JSON.parse(cookie);
        }
        else
        {
            cookie = [];
        }

        return cookie;
    };



    shopCart.updateList = function( cartList )
    {
        $.cookie.json = true;
        $.cookie( $cookieName, cartList);
    };

})(window, jQuery, IIDO.Shop.Cart);

(function(window, $, shopWatchlist)
{
    var $cookieName = 'iido_shop_watchlist';



    shopWatchlist.addProductToList = function( product )
    {
        this.addProductToWatchlist( product );
    };



    shopWatchlist.addProductToWatchlist = function( product )
    {
        var added           = false,
            watchlistList   = this.getList();

        for(var num=0; num<watchlistList.length; num++)
        {
            var productInList = watchlistList[ num ];

            if( productInList.itemNumber === product.itemNumber )
            {
                added = true;

                watchlistList[ num ].quantity = (productInList.quantity + product.quantity);
            }
        }

        if( !added )
        {
            watchlistList.push( product );
        }

        this.updateList( watchlistList );
    };



    shopWatchlist.getList = function()
    {
        var cookie = $.cookie($cookieName);

        if( cookie )
        {
            cookie = JSON.parse(cookie);
        }
        else
        {
            cookie = [];
        }

        return cookie;
    };



    shopWatchlist.updateList = function( watchlistList )
    {
        $.cookie.json = true;
        $.cookie( $cookieName, watchlistList);
    };

})(window, jQuery, IIDO.Shop.Watchlist);