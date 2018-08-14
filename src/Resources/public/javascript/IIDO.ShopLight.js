/******************************************************/
/*                                                    */
/*  (c) 2017 IIDO, www.iido.at <development@iido.at>  */
/*                                                    */
/******************************************************/
var IIDO = IIDO || {};
IIDO.ShopLight = IIDO.ShopLight || {};

IIDO.ShopLight.Cart      = IIDO.ShopLight.Cart || {};
IIDO.ShopLight.Watchlist = IIDO.ShopLight.Watchlist || {};


(function(window, $, shopLight)
{
    var $products = [];

    shopLight.init = function()
    {
        var shopItems = document.querySelectorAll(".shoplight-product-item");

        if( shopItems.length )
        {
            for(var i=0; i<shopItems.length; i++)
            {
                var shopItem = shopItems[ i ];

                if( shopItem.classList.contains("toggle-item") )
                {
                    shopItem.querySelector(".top-container").addEventListener("click", function()
                    {
                        if( this.parentNode.parentNode.classList.contains("has-variants") )
                        {
                            IIDO.ShopLight.toggleProductItemVariants( this.parentNode.parentNode );
                        }
                    });
                }
            }
        }
    };



    shopLight.addProduct = function( product )
    {
        $products.push( product );
    };



    shopLight.getProducts = function()
    {
        return $products;
    };



    shopLight.toggleProductItemVariants = function( shopItem )
    {
        shopItem.classList.toggle("open");
    };



    shopLight.addVariantProductToCart = function(parentID, variantID, variantPrice)
    {
        IIDO.ShopLight.Cart.addVariantProductToCart( parentID, variantID, variantPrice);
    };

})(window, jQuery, IIDO.ShopLight);



(function(window, $, shopLightCart)
{
    var $cookieNameCart = 'iido_shoplight_cart';



    shopLightCart.addProductToCart = function( product )
    {
        var products = this.getCartProducts();

        console.log( products );
    };



    shopLightCart.addVariantProductToCart = function( parentID, variantID, variantPrice )
    {
        var shopProducts    = IIDO.ShopLight.getProducts(),
            product         = [];

        for(var i=0; i<=shopProducts.length; i++)
        {
            var shopProduct = shopProducts[ i ];

            if( shopProduct.id === parentID || shopProduct.alias === parentID || shopProduct.itemNumber === parentID )
            {
                product = shopProduct;

                for(var ii=0; ii<=shopProduct.variants; ii++)
                {
                    var variant = shopProduct.variants[ ii ];

                    if( (variant.itemNumber === variantID || ii === variantID) )
                    {

                    }
                }
            }
        }
    };



    shopLightCart.getCartProducts = function()
    {
        var cookie = Cookies.getJSON( $cookieNameCart );

        if( cookie )
        {
            if( cookie === '[null]')
            {
                cookie = [];
            }
            else if( typeof cookie === "object" )
            {
                if( cookie[0] === null )
                {
                    cookie = [];
                }
            }
        }
        else
        {
            cookie = [];
        }

        return cookie;
    };



    shopLightCart.updateCartProducts = function( cartList )
    {
        Cookies.set($cookieNameCart, cartList);
    };


})(window, jQuery, IIDO.ShopLight.Cart);



(function(window, $, shopLightWatchlist)
{
})(window, jQuery, IIDO.ShopLight.Watchlist);

document.addEventListener("DOMContentLoaded", function()
{
   IIDO.ShopLight.init();
});