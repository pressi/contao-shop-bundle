/******************************************************/
/*                                                    */
/*  (c) 2017 IIDO, www.iido.at <development@iido.at>  */
/*                                                    */
/******************************************************/
var IIDO = IIDO || {};
IIDO.Shop = IIDO.Shop || {};

IIDO.Shop.Cart      = IIDO.Shop.Cart || {};
IIDO.Shop.Watchlist = IIDO.Shop.Watchlist || {};


(function(window, $, shop)
{
    var $langKey = 'de',

        $langMessage = {
            'de' : {
                'buy'               : 'Kaufen',
                'addToWatchlist'    : 'Auf die Merkliste',

                'design'            : 'Das Design gehört ausgewählt.',
                'binding'           : 'Wählen Sie eine Bindung aus.',
                'length'            : 'Geben Sie Ihre Skilänge an.',
                'flex'              : 'Wählen Sie den Härtebereich Ihres Skis aus.',
                'tuning'            : 'Wählen Sie Ihr gewünschtes Tuning aus.',

                'addToCartMessage'         : 'Der Artikel wurde in den Warenkorb gelegt.',
                'addToWatchlistMessage'    : 'Der Artikel wurde in die Merklsite eingetragen.',
                'updateProduct'            : 'Der Artikel wurde aktualisiert.',

                'updateProductError'       : 'Beim Speichern des Artikels ist ein Fehler aufgetreten.'
            }
        },

        $productContainer;


    shop.setLanguage = function( langKey )
    {
        $langKey = langKey;
    };



    shop.getLang = function( key )
    {
        return $langMessage[ $langKey ][ key ];
    };



    shop.updateProduct = function( mode )
    {
        // var productList = [];
        //
        // if( mode === "cart" )
        // {
        //     productList = IIDO.Shop.Cart.getList();
        // }
        // else if( mode === "watchlist" )
        // {
        //     productList = IIDO.Shop.Watchlist.getList();
        // }

        this.showMessage("confirm", "updateProduct", "center");
    };



    shop.updateProductPrice = function( product, url, mode )
    {
        var objData = [];

        objData.push( {'name' : 'itemNumber', 'value' : product.itemNumber} );
        objData.push( {'name' : 'productName', 'value' : encodeURIComponent(product.name)} );
        objData.push( {'name' : 'quantity', 'value' : product.quantity} );

        $.ajax({
            type: 'post',
            url: url,
            dataType: 'json',
            data: objData,
            success: function (response, textStatus, jqXHR)
            {
                if( response.result.html.price )
                {
                    var shopPriceTag    = document.getElementById("shopPriceNum"),
                        priceNum        = parseFloat( shopPriceTag.innerHTML );

                    if( mode === "remove")
                    {
                        priceNum = (priceNum - response.result.html.price);
                    }
                    else if( mode === "add" )
                    {
                        priceNum = (priceNum + response.result.html.price);
                    }

                    shopPriceTag.innerHTML = IIDO.Shop.renderPrice( priceNum );
                }
            }
        });
    };



    shop.renderPrice = function( price, useDecimals )
    {
        price = parseFloat( price );

        if( useDecimals === undefined || useDecimals === "undefined" || useDecimals === null )
        {
            useDecimals = false;
        }

        var strPrice = price.toString();

        if( !useDecimals && (strPrice.indexOf(/\./) === -1 || strPrice.indexOf(/\.00$/) === -1 ) )
        {
            strPrice = strPrice.replace(/\.00$/, '') + ',-';
        }

        if( useDecimals )
        {
            strPrice = $.number(price.toFixed(2), 2, ',', '.');
        }

        return strPrice;
    };



    shop.initDetails = function( contID )
    {
        $productContainer = document.getElementById("productCont_" + contID );

        var canvasDetail    = document.getElementById("canvasDetail"),
            color           = '#1b1b1b';

        if( canvasDetail )
        {
            var canvasWidth     = ((window.innerWidth * 0.96) + 10),
                canvasHeight    = window.innerHeight;

            if( window.innerWidth <= respWidth )
            {
                canvasWidth   = window.innerWidth;
                canvasHeight  = (window.innerHeight * 0.96);
            }

            var arrCanvas = runCanvasFactor(canvasDetail, canvasWidth, canvasHeight);

            this.generateCanvas( arrCanvas[0], color, arrCanvas[1], arrCanvas[2] );
        }
    };



    shop.generateCanvas = function( ctx, color, width, height )
    {
        var headerW2    = (width / 2),
            headerH2    = (height / 2),

            roundW      = 120,

            bgStart     = 0, //(roundW + 10),
            bgStartY    = 0,
            bgWidth     = (width - (roundW - 20)),
            bgHeight    = height,

            bgCircleX   = (height * 2),
            bgCircleY   = headerH2,
            bgCircleW   = (height * 2);

        if( window.innerWidth <= 700 )
        {
            bgWidth     = width;
            bgHeight    = (height - (roundW - 20));

            if( document.body.classList.contains("mobile") && document.body.classList.contains("sfari") )
            {
                bgHeight = bgHeight + 600;
            }

            bgCircleW   = height;
            bgCircleX   = (width / 2);
            bgCircleY   = (height - bgCircleW - 3);

            bgStart     = 0;
        }

        bgCircleX = bgCircleX + 20;

        ctx.globalCompositeOperation = "source-over";
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = color;
        ctx.fillRect(bgStart, bgStartY, bgWidth, bgHeight);

        ctx.beginPath();
        ctx.arc(bgCircleX + 0.5, bgCircleY + 0.5, bgCircleW, 0, Math.PI*2, false);
        ctx.closePath();
        ctx.shadowColor = ((color === "#1b1b1b") ? "rgba(0, 0, 0, 0.35)" : "rgba(100, 100, 100, .35)");
        ctx.shadowOffsetX = ctx.shadowOffsetY = 0;
        ctx.shadowBlur = 25;
        ctx.fill();
    };



    shop.openProductDetails = function( detailTag )
    {
        if( detailTag.classList.contains("open") )
        {
            detailTag.classList.remove("open");
        }
        else
        {
            detailTag.classList.add("open");
        }
    };



    shop.addProductToCart = function()
    {
        if( this.checkForm() )
        {
            var product = this.getProduct();

            IIDO.Shop.Cart.addProductToCart( product );
            this.showMessage("confirm", "addToCartMessage", "center");

            this.updateCartNum( $productContainer );
        }
    };



    shop.showMessage = function( messageType, messageName, messageParent, messageClass, messageOwnText )
    {
        if( messageClass === undefined || messageClass === "undefined" || messageClass === null )
        {
            messageClass = false;
        }

        if( messageOwnText === undefined || messageOwnText === "undefined" || messageOwnText === null )
        {
            messageOwnText = false;
        }

        var messageContainer = document.getElementById( "shopMessage" );

        if( !messageContainer )
        {
            messageContainer = document.createElement("div");
            messageContainer.classList.add("message-container");
            messageContainer.setAttribute("id", "shopMessage");

            var messageTag = document.createElement("div");

            messageTag.classList.add("message-inside");
            messageTag.innerHTML = (messageOwnText ? messageName : this.getMessageText( messageName ));

            messageContainer.append( messageTag );

            document.body.append( messageContainer );
        }
        else
        {
            messageContainer.querySelector(".message-inside").innerHTML = (messageOwnText ? messageName : this.getMessageText( messageName ));
        }


        messageContainer.classList.remove("error-message");
        messageContainer.classList.remove("confirm-message");
        messageContainer.classList.remove("pos-not-center");

        messageContainer.classList.add(messageType + "-message");
        messageContainer.classList.add("shown");

        if( messageClass )
        {
            messageContainer.classList.add( messageClass );
        }

        if( messageParent !== undefined && messageParent !== "undefined" && messageParent !== null)
        {
            var posTop = '50%', posLeft = '50%';

            if( messageParent !== "center" )
            {
                messageContainer.classList.add("pos-not-center");

                var position        = $(messageParent).offset(),
                    parentHeight    = messageParent.clientHeight,
                    parentWidth     = messageParent.clientWidth;

                posTop = (position.top + 10 - (parentHeight/2)) + 'px';
                posLeft = (position.left + parentWidth - 35) + 'px';
            }

            messageContainer.style.position = "absolute";
            messageContainer.style.top      = posTop;
            messageContainer.style.left     = posLeft;
        }

        document.addEventListener("click", IIDO.Shop.hideMessage, true);
    };



    shop.hideMessage = function()
    {
        var messageCont = document.getElementById( "shopMessage" );

        if( messageCont )
        {
            messageCont.classList.remove("shown");
        }

        document.removeEventListener("click", IIDO.Shop.hideMessage, true);
    };



    shop.getMessageText = function( messageKey )
    {
        return $langMessage[ $langKey ][ messageKey ];
    };



    shop.addProductToWatchlist = function()
    {
        if( this.checkForm() )
        {
            var product = this.getProduct();

            IIDO.Shop.Watchlist.addProductToWatchlist( product );
            this.showMessage("confirm", "addToWatchlistMessage", "center");

            this.updateWatchlistNum( $productContainer )
        }
    };



    shop.updateCartNum = function( container )
    {
        var numTag = container.querySelector(".price-cart .cart .num");

        if( numTag.classList.contains("has-link") )
        {
            numTag = numTag.querySelector("a");
        }

        var numValue = parseInt( numTag.innerHTML );

        numTag.innerHTML = (numValue + 1);
    };



    shop.updateWatchlistNum = function( container )
    {
        var numTag      = container.querySelector(".price-cart .cart .watchlist-num"),
            numValue    = parseInt(numTag.innerHTML);

        numTag.innerHTML = (numValue + 1);

        if( numTag.classList.contains("is-hidden") )
        {
            numTag.classList.remove("is-hidden");
        }
    };



    shop.getProduct = function()
    {
        var product = {};

        product.itemNumber  = document.querySelector('input[name="ARTICLE_NUMBER"]').value;
        product.name        = encodeURIComponent(document.querySelector('input[name="name"]').value);
        product.quantity    = 1;

        return product;
    };



    shop.checkForm = function()
    {
        return true;
    }


})(window, jQuery, IIDO.Shop);


(function(window, $, shopCart)
{
    var $cookieNameCart = 'iido_shop_cart';



    shopCart.addProductToCart = function( product )
    {
        var added       = false,
            cartList    = IIDO.Shop.Cart.getList();

        if( cartList.length )
        {
            for(var num=0; num<cartList.length; num++)
            {
                var productInCart = cartList[ num ];

                if( productInCart.itemNumber === product.itemNumber )
                {
                    added = true;

                    cartList[ num ].quantity = (productInCart.quantity + product.quantity);
                }
            }
        }

        if( !added )
        {
            cartList.push( product );
        }

        IIDO.Shop.Cart.updateList( cartList );
    };



    shopCart.getList = function()
    {
        // $.cookie.json = false;
        // var cookie = $.cookie($cookieNameCart);
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
            // else
            // {
            //     cookie = JSON.parse(cookie);
            // }
        }
        else
        {
            cookie = [];
        }

        return cookie;
    };



    shopCart.updateList = function( cartList )
    {
        // $.cookie.json = true;
        // $.cookie( $cookieNameCart, cartList);
        Cookies.set($cookieNameCart, cartList);
    };



    shopCart.removeItem = function( cartItemLinkTag, cartItemNumber, cartItemName )
    {
        var cartList    = IIDO.Shop.Cart.getList(),
            product     = false;

        for(var i=0; i<cartList.length; i++)
        {
            var cartItem = cartList[ i ];

            if( cartItem.name === cartItemName && cartItem.itemNumber === cartItemNumber )
            {
                product = cartItem;

                cartList.splice(i, 1);
                break;
            }
        }

        IIDO.Shop.Cart.updateList( cartList );

        var itemTag = cartItemLinkTag.parentNode.parentNode.parentNode.parentNode;

        if( cartList.length === 0 )
        {
            itemTag.parentNode.querySelector(".empty-text").classList.remove("hidden");
        }

        itemTag.parentNode.removeChild( itemTag );

        if( product )
        {
            IIDO.Shop.Cart.updateShopPrice(product, 'remove');
        }
    };



    shopCart.moveItemToWatchlist = function(cartItemLinkTag, cartItemNumber, cartItemName, cartItemKey)
    {
        var product, cartList = IIDO.Shop.Cart.getList();

        for(var i=0; i<cartList.length; i++)
        {
            var cartItem = cartList[ i ];

            if( cartItem.name === cartItemName && cartItem.itemNumber === cartItemNumber )
            {
                var cartProduct = cartList.splice(i, 1);

                product = cartProduct[0];
                break;
            }
        }

        IIDO.Shop.Cart.updateList( cartList );
        IIDO.Shop.Watchlist.addProductToWatchlist( product );

        var watchlistCont   = document.querySelector(".ce_iido_shop_watchlist .col-left .watchlist-container-inside"),

            itemTag         = cartItemLinkTag.parentNode.parentNode.parentNode.parentNode,
            itemInsideTag   = itemTag.querySelector('.cart-item-inside'),
            emptyText       = watchlistCont.querySelector('.empty-text'),

            cartCont        = itemTag.parentNode,

            editLink        = itemTag.querySelector("a.edit-link"),
            watchlistLink   = itemTag.querySelector("a.watchlist-link"),
            removeLink      = itemTag.querySelector("a.remove-link");

        itemTag.classList.remove("cart-item");
        itemTag.classList.add("watchlist-item");

        itemInsideTag.classList.remove("cart-item-inside");
        itemInsideTag.classList.add("watchlist-item-inside");

        watchlistLink.classList.remove("watchlist-link");
        watchlistLink.classList.add("buy-link");

        var newClickEvent = watchlistLink.getAttribute("onclick");
        newClickEvent = newClickEvent.replace(/Shop\.Cart\.moveItemToWatchlist/, 'Shop.Watchlist.moveItemToCart');

        watchlistLink.innerHTML = IIDO.Shop.getLang( 'buy' );
        watchlistLink.setAttribute("onclick", newClickEvent);

        var newClickEventEdit = editLink.getAttribute("onclick");

        if( newClickEventEdit )
        {
            newClickEventEdit = newClickEventEdit.replace(/Shop\.Cart\.editItem/, 'Shop.Watchlist.editItem');
            editLink.setAttribute("onclick", newClickEventEdit);
        }

        var newClickEventRemove = removeLink.getAttribute("onclick");
        newClickEventRemove = newClickEventRemove.replace(/Shop\.Cart\.removeItem/, 'Shop.Watchlist.removeItem');
        removeLink.setAttribute("onclick", newClickEventRemove);

        if( !emptyText.classList.contains("hidden") )
        {
            emptyText.classList.add("hidden");
        }

        // watchlistCont.insertBefore( itemTag, watchlistCont.childNodes[0] );
        watchlistCont.append( itemTag );

        if( cartCont.childElementCount === 1 )
        {
            cartCont.querySelector(".empty-text").classList.remove("hidden");
        }

        var formTag = document.getElementById("cartItemEdit_" + cartItemKey);

        if( !formTag )
        {
            formTag = itemTag.querySelector("form.edit-form-container");
        }

        if( formTag )
        {
            var watchlistItemId  = cartItemKey.split("_"),
                watchlistList    = IIDO.Shop.Watchlist.getList(),
                watchlistItemKey = (watchlistList.length - 1);

            formTag.setAttribute('id', 'watchlistItemEdit_' + watchlistItemId[0] + '_' + watchlistItemKey);

            formTag.querySelector('input[name="SUBMODE"]').value = 'watchlist';
        }

        IIDO.Shop.Cart.updateShopPrice(product, 'remove');
    };



    shopCart.updateShopPrice = function(product, mode)
    {
        var cartUrl         = document.querySelector(".ce_iido_shop_cart .cart-columns").getAttribute("data-aurl");

        IIDO.Shop.updateProductPrice(product, cartUrl, mode)
    };



    shopCart.checkShipping = function( contTag )
    {
        contTag.querySelector("input").checked = true;
        contTag.classList.add("active");

        var siblings = IIDO.Base.getSiblings( contTag );

        for(var i=0; i<siblings.length; i++)
        {
            var sibling = siblings[ i ];

            if( sibling !== contTag )
            {
                sibling.classList.remove("active");
            }
        }

        contTag.parentNode.parentNode.classList.remove("error");
    };



    shopCart.checkPayment = function( contTag )
    {
        this.checkShipping( contTag );
    };



    shopCart.checkCheckOutForm = function( formTag )
    {
        return true;
    };



    shopCart.initCheckOutForm = function( formTag )
    {
        var widgets = formTag.querySelectorAll(".widget");

        if( widgets.length )
        {
            for(var i=0; i<widgets.length; i++)
            {
                var widget      = widgets[ i ],
                    inputTag    = widget.querySelector("input.text");

                if( inputTag )
                {
                    inputTag.addEventListener("focus", function()
                    {
                        IIDO.Shop.Cart.focusFormInput( this );
                    });

                    inputTag.addEventListener("blur", function()
                    {
                        IIDO.Shop.Cart.blurFormInput( this );
                    });
                }
            }
        }
    };



    shopCart.focusFormInput = function( inputTag )
    {
        var widgetCont = inputTag.parentNode;

        widgetCont.classList.remove("error");
    };



    shopCart.blurFormInput = function( inputTag )
    {
        var widgetCont  = inputTag.parentNode,
            error       = false;

        if( widgetCont.classList.contains("not-mandatory") )
        {
            return;
        }

        if( inputTag.value === "" )
        {
            error = true;
        }
        else
        {
            if( widgetCont.classList.contains("widget-phone") && !IIDO.Form.checkPhone( inputTag.value ) )
            {
                error = true;
            }
            else if( widgetCont.classList.contains("widget-email") && !IIDO.Form.checkEmail( inputTag.value ) )
            {
                error = true;
            }
        }

        if( error )
        {
            widgetCont.classList.add("error");
        }
    };



    shopCart.editItem = function( formID, formEditLink )
    {
        var formTag = document.getElementById("cartItemEdit_" + formID);

        if( !formTag )
        {
            formTag = formEditLink.parentNode.parentNode.querySelector("form.edit-form-container");
        }

        if( formTag )
        {
            formTag.submit();
        }
    };



    shopCart.checkRadioGroup = function( contTag )
    {
        var widgetTag   = contTag.parentNode.parentNode,
            widgetInput = contTag.querySelector("input");

        widgetInput.checked = true;
        contTag.classList.add("active");

        var siblings = IIDO.Base.getSiblings( contTag );

        for(var i=0; i<siblings.length; i++)
        {
            var sibling = siblings[ i ];

            if( sibling !== contTag )
            {
                sibling.classList.remove("active");
            }
        }

        widgetTag.classList.remove("error");

        var widgetForm = document.getElementById("shippingAddressFields");

        if( widgetInput.value === "other" || widgetInput.value === "other_address" )
        {
            if( widgetTag.classList.contains("shipping-address") )
            {
                if( widgetForm )
                {
                    widgetForm.classList.remove("hidden");
                }
            }
        }
        else
        {
            if( widgetForm )
            {
                widgetForm.classList.add("hidden");
            }
        }
    };

})(window, jQuery, IIDO.Shop.Cart);

(function(window, $, shopWatchlist)
{
    var $cookieNameList = 'iido_shop_watchlist';



    shopWatchlist.addProductToList = function( product )
    {
        IIDO.Shop.Watchlist.addProductToWatchlist( product );
    };



    shopWatchlist.addProductToWatchlist = function( product )
    {
        var added           = false,
            watchlistList   = IIDO.Shop.Watchlist.getList();

        if( watchlistList.length )
        {
            for(var num=0; num<watchlistList.length; num++)
            {
                var productInList = watchlistList[ num ];

                if( productInList.itemNumber === product.itemNumber )
                {
                    added = true;

                    watchlistList[ num ].quantity = (productInList.quantity + product.quantity);
                }
            }
        }

        if( !added )
        {
            watchlistList.push( product );
        }

        IIDO.Shop.Watchlist.updateList( watchlistList );
    };



    shopWatchlist.getList = function()
    {
        // $.cookie.json = false;
        // var cookie = $.cookie($cookieNameList);
        var cookie = Cookies.getJSON( $cookieNameList );

        if( cookie )
        {
            if( cookie === '[null]' )
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
            // else
            // {
            //     cookie = JSON.parse(cookie);
            // }
        }
        else
        {
            cookie = [];
        }

        return cookie;
    };



    shopWatchlist.updateList = function( watchlistList )
    {
        // $.cookie.json = true;
        // $.cookie( $cookieNameList, watchlistList);
        Cookies.set( $cookieNameList, watchlistList );
    };



    shopWatchlist.removeItem = function( watchlistItemTag, watchlistItemNumber, watchlistItemName )
    {
        var watchlistList = IIDO.Shop.Watchlist.getList();

        for(var i=0; i<watchlistList.length; i++)
        {
            var watchlistItem = watchlistList[ i ];

            if( watchlistItem.name === watchlistItemName && watchlistItem.itemNumber === watchlistItemNumber )
            {
                watchlistList.splice(i, 1);
                break;
            }
        }

        IIDO.Shop.Watchlist.updateList( watchlistList );

        var itemTag = watchlistItemTag.parentNode.parentNode.parentNode.parentNode;

        if( watchlistList.length === 0 )
        {
            itemTag.parentNode.querySelector(".empty-text").classList.remove("hidden");
        }

        itemTag.parentNode.removeChild( itemTag );
    };



    shopWatchlist.moveItemToCart = function( watchlistItemTag, watchlistItemNumber, watchlistItemName, watchlistItemKey )
    {
        var product, watchlistList = IIDO.Shop.Watchlist.getList();

        for(var i=0; i<watchlistList.length; i++)
        {
            var watchlistItem = watchlistList[ i ];

            if( watchlistItem.name === watchlistItemName && watchlistItem.itemNumber === watchlistItemNumber )
            {
                var watchlistProduct = watchlistList.splice(i, 1);

                product = watchlistProduct[0];
                break;
            }
        }

        IIDO.Shop.Watchlist.updateList( watchlistList );
        IIDO.Shop.Cart.addProductToCart( product );

        var cartCont   = document.querySelector(".ce_iido_shop_cart .col-left .cart-container-inside"),

            itemTag         = watchlistItemTag.parentNode.parentNode.parentNode.parentNode,
            itemInsideTag   = itemTag.querySelector('.watchlist-item-inside'),
            emptyText       = cartCont.querySelector('.empty-text'),

            watchlistCont   = itemTag.parentNode,

            editLink        = itemTag.querySelector("a.edit-link"),
            buyLink         = itemTag.querySelector("a.buy-link"),
            removeLink      = itemTag.querySelector("a.remove-link");

        itemTag.classList.remove("watchlist-item");
        itemTag.classList.add("cart-item");

        itemInsideTag.classList.remove("watchlist-item-inside");
        itemInsideTag.classList.add("cart-item-inside");

        buyLink.classList.remove("buy-link");
        buyLink.classList.add("watchlist-link");

        var newClickEvent = buyLink.getAttribute("onclick");
        newClickEvent = newClickEvent.replace(/Shop\.Watchlist\.moveItemToCart/, 'Shop.Cart.moveItemToWatchlist');

        var newClickEventEdit = editLink.getAttribute("onclick");

        if( newClickEventEdit )
        {
            newClickEventEdit = newClickEventEdit.replace(/Shop\.Watchlist\.editItem/, 'Shop.Cart.editItem');
            editLink.setAttribute("onclick", newClickEventEdit);
        }

        var newClickEventRemove = removeLink.getAttribute("onclick");
        newClickEventRemove = newClickEventRemove.replace(/Shop\.Watchlist\.removeItem/, 'Shop.Cart.removeItem');
        removeLink.setAttribute("onclick", newClickEventRemove);

        buyLink.innerHTML = IIDO.Shop.getLang( 'addToWatchlist' );
        buyLink.setAttribute("onclick", newClickEvent);

        if( !emptyText.classList.contains("hidden") )
        {
            emptyText.classList.add("hidden");
        }

        // cartCont.insertBefore( itemTag, cartCont.childNodes[0] );
        cartCont.append( itemTag );

        if( watchlistCont.childElementCount === 1 )
        {
            watchlistCont.querySelector(".empty-text").classList.remove("hidden");
        }

        var formTag = document.getElementById("watchlistItemEdit_" + watchlistItemKey);

        if( !formTag )
        {
            formTag = itemTag.querySelector("form.edit-form-container");
        }

        if( formTag )
        {
            var cartItemId  = watchlistItemKey.split("_"),
                cartList    = IIDO.Shop.Cart.getList(),
                cartItemKey = (cartList.length - 1);

            formTag.setAttribute('id', 'cartItemEdit_' + cartItemId[0] + '_' + cartItemKey);

            formTag.querySelector('input[name="SUBMODE"]').value = 'cart';
        }

        IIDO.Shop.Cart.updateShopPrice(product, 'add');
    };



    shopWatchlist.editItem = function( formID, formEditLink )
    {
        var formTag = document.getElementById("watchlistItemEdit_" + formID);

        if( !formTag )
        {
            formTag = formEditLink.parentNode.parentNode.querySelector("form.edit-form-container");
        }

        if( formTag )
        {
            formTag.submit();
        }
    };

})(window, jQuery, IIDO.Shop.Watchlist);