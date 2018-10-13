/******************************************************/
/*                                                    */
/*  (c) 2017 IIDO, www.iido.at <development@iido.at>  */
/*                                                    */
/******************************************************/
var IIDO = IIDO || {};
IIDO.Shop = IIDO.Shop || {};
IIDO.Shop.Configurator = IIDO.Shop.Configurator || {};

(function(window, $, configurator)
{
    var $configurator, $shopConfigurator, $configID, $config = {},
        $langKey = 'de',

        // $errorMessage = {
        //     'de' : {
        //         'design'    : 'Das Design gehört ausgewählt.',
        //         'binding'   : 'Wählen Sie eine Bindung aus.',
        //         'length'    : 'Geben Sie Ihre Skilänge an.',
        //         'flex'      : 'Wählen Sie den Härtebereich Ihres Skis aus.',
        //         'tuning'    : 'Wählen Sie Ihr gewünschtes Tuning aus.',
        //
        //         'addToCart'         : 'Der Artikel wurde in den Warenkorb gelegt.',
        //         'addToWatchlist'    : 'Der Artikel wurde in die Merklsite eingetragen.'
        //     }
        // },

    $fieldNames = ['design', 'binding', 'length', 'flex'],
    $formFields = ['name', 'ARTICLE_NUMBER[range]', 'ARTICLE_NUMBER[design]', 'ARTICLE_NUMBER[binding]', 'ARTICLE_NUMBER[length]', 'ARTICLE_NUMBER[flex]', 'tuning'],

    $itemNumber = '##RANGE##.##DESIGN##.##LENGTH##.##WOODCORE##.##FLEX##.##KEIL##.##BINDING##',

        $flexSoft = 35, $flexStiff = 75,

        $woodCoreDefault    = ['PP', 'EP'],

    $version = 'v1';


    configurator.initContainer = function( contID )
    {
        $configID           = contID;
        $shopConfigurator   = document.getElementById('shopConfigurator_' + contID);

        var shopCont = document.getElementById('shopConfigurator_' + contID + '_1');

        if( shopCont )
        {
            $shopConfigurator = shopCont;
        }
    };



    configurator.init = function( contID )
    {
        // this.initContainer( contID );

        $configID       = contID;
        $configurator   = document.getElementById('configuratorCont_' + contID);

        var canvasDetail    = document.getElementById("canvasDetail"),
            // ctx             = canvasDetail.getContext("2d"),

            color           = $configurator.getAttribute("data-color");

        // $configurator.style.background = color;

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

            IIDO.Shop.generateCanvas( arrCanvas[0], color, arrCanvas[1], arrCanvas[2] );
        }
    };



    configurator.initCatHover = function()
    {
        if( $shopConfigurator )
        {
            var catCont     = $shopConfigurator.querySelector(".category-container"),
                catItems    = catCont.querySelectorAll(".category-item");

            if( catItems.length )
            {
                for(var i=0; i<catItems.length; i++)
                {
                    catItems[ i ].addEventListener("mouseenter", function()
                    {
                        var catID           = this.getAttribute("data-id"),
                            catHoverCont    = document.getElementById("catHoverCont_" + catID),
                            catHoverConts   = IIDO.Base.getSiblings( catHoverCont );

                        for(var num=0; num<catHoverConts.length; num++)
                        {
                            catHoverConts[ num ].classList.remove("shown");
                        }

                        catHoverCont.classList.add("shown");
                        catCont.classList.add("is-shown");
                    });

                    catItems[ i ].addEventListener("mouseleave", function()
                    {
                        var catID           = this.getAttribute("data-id"),
                            catHoverCont    = document.getElementById("catHoverCont_" + catID);

                        catHoverCont.classList.remove("shown");
                        catCont.classList.remove("is-shown");
                    })
                }
            }
        }
    };



    configurator.setLanguage = function( langKey )
    {
        $langKey = langKey;
    };



    // configurator.generateCanvas = function( ctx, color, width, height )
    // {
    //     var headerW2    = (width / 2),
    //         headerH2    = (height / 2),
    //
    //         roundW      = 120,
    //
    //         bgStart     = 0, //(roundW + 10),
    //         bgStartY    = 0,
    //         bgWidth     = (width - (roundW - 20)),
    //         bgHeight    = height,
    //
    //         bgCircleX   = (height * 2),
    //         bgCircleY   = headerH2,
    //         bgCircleW   = (height * 2);
    //
    //     if( window.innerWidth <= 700 )
    //     {
    //         bgWidth     = width;
    //         bgHeight    = (height - (roundW - 20));
    //
    //         if( document.body.classList.contains("mobile") && document.body.classList.contains("sfari") )
    //         {
    //             bgHeight = bgHeight + 600;
    //         }
    //
    //         bgCircleW   = height;
    //         bgCircleX   = (width / 2);
    //         bgCircleY   = (height - bgCircleW - 3);
    //
    //         bgStart     = 0;
    //     }
    //
    //     bgCircleX = bgCircleX + 20;
    //
    //     ctx.globalCompositeOperation = "source-over";
    //     ctx.clearRect(0, 0, width, height);
    //     ctx.fillStyle = color;
    //     ctx.fillRect(bgStart, bgStartY, bgWidth, bgHeight);
    //
    //     ctx.beginPath();
    //     ctx.arc(bgCircleX + 0.5, bgCircleY + 0.5, bgCircleW, 0, Math.PI*2, false);
    //     ctx.closePath();
    //     ctx.shadowColor = "rgba(100, 100, 100, .35)";
    //     ctx.shadowOffsetX = ctx.shadowOffsetY = 0;
    //     ctx.shadowBlur = 25;
    //     ctx.fill();
    // };



    configurator.checkItem = function( itemTag, mode )
    {
        if( mode === undefined || mode === "undefined" || mode === null )
        {
            mode = 'default';
        }
        // var calculatePrice = true;

        this.uncheckItems( itemTag.parentNode.childNodes );

        itemTag.classList.add("is-checked");
        itemTag.querySelector("input").checked = true;
        itemTag.querySelector("input").setAttribute("checked", "checked");

        if( itemTag.classList.contains("color-picker") )
        {
            var colorAlias = itemTag.getAttribute("data-alias");

            itemTag.parentNode.nextElementSibling.innerHTML = '<div class="color_circle cc-' + colorAlias + '"></div>';
        }
        else
        {
            itemTag.parentNode.nextElementSibling.innerHTML = itemTag.querySelector(".name").innerHTML;
        }

        var imageCont   = document.getElementById("productImage_" + $configID),
            dataImage   = itemTag.getAttribute( "data-image" ),

            skiImage    = imageCont.querySelector(".image_container > img").getAttribute("data-default");

        if( dataImage !== null && dataImage !== undefined && dataImage !== "undefined" && !dataImage.length )
        {
            dataImage = '';
        }

        if( mode === "color" || mode === "design" )
        {
            var bindingInput = document.querySelector('input[name="ARTICLE_NUMBER[binding]"]:checked'),
                bindingValue = $config.default.binding;

            if( bindingInput )
            {
                bindingValue = bindingInput.value;
            }

            if( bindingValue !== "none" )
            {
                var bindingItem = document.querySelector(".choose-item.binding-picker.is-checked");

                if( bindingItem )
                {
                    var bindingDataImage    = bindingItem.getAttribute("data-image"),
                        currentDesign       = itemTag.getAttribute("data-number");

                    if( currentDesign === undefined || currentDesign === "undefined" || currentDesign === null )
                    {
                        currentDesign = $config.default.design;
                    }

                    if( bindingDataImage === null || bindingDataImage === undefined && bindingDataImage === "undefined" )
                    {
                        bindingDataImage = '';
                    }

                    if( currentDesign && bindingDataImage )
                    {
                        var arrBindingDataImages    = JSON.parse(bindingDataImage),
                            imageSRC                = arrBindingDataImages[ currentDesign ];

                        if( imageSRC )
                        {
                            skiImage = imageSRC;
                        }
                    }
                }
            }
            else
            {
                if( dataImage )
                {
                    skiImage = dataImage;
                }
            }
        }
        else if( mode === "binding" )
        {
            if( dataImage )
            {
                var designInput = document.querySelector('input[name="ARTICLE_NUMBER[design]"]:checked'),
                    designValue = $config.default.design;

                if( designInput )
                {
                    designValue = designInput.value;
                }

                if( designValue )
                {
                    var arrDataImages   = JSON.parse(dataImage),
                        imageSRC        = arrDataImages[ designValue ];

                    if( imageSRC )
                    {
                        skiImage = imageSRC;
                    }
                }
            }
            else
            {
                var designItem = document.querySelector(".choose-item.color-picker.is-checked");

                if( designItem )
                {
                    imageSRC = designItem.getAttribute("data-image");

                    if( imageSRC )
                    {
                        skiImage = imageSRC;
                    }
                }
            }
        }

        if( mode === "length" || mode === "flex" || mode === "tuning" || mode === "default" )
        {
            skiImage = '';
        }

        if( skiImage )
        {
            var imageTag = imageCont.querySelector(".image_container > img");

            imageTag.src = skiImage;
        }

        this.calculateNewPrice( mode );

        return;




        // var dataImage   = itemTag.getAttribute( "data-image" ),
        //     imageCont   = document.getElementById("productImage_" + $configID),
        //
        //     useStartImage = false,
        //     useDefaultDesignImage = false,
        //
        //     changedImage = false;
        //
        // if( dataImage !== null && dataImage !== undefined && dataImage !== "undefined" && !dataImage.length )
        // {
        //     dataImage = '';
        // }
        //
        // if( mode === "binding" )
        // {
        //     useStartImage = true;
        //     useDefaultDesignImage = true;
        //
        //     if( dataImage )
        //     {
        //         var designInput = document.querySelector('input[name="ARTICLE_NUMBER[design]"]:checked'),
        //             designValue = $config.default.design;
        //
        //         if( designInput )
        //         {
        //             designValue = designInput.value;
        //         }
        //
        //         var dataImages  = JSON.parse(dataImage),
        //             imageSRC    = '';
        //
        //         if( designValue )
        //         {
        //             imageSRC = dataImages[ designValue ];
        //         }
        //
        //         if( imageSRC )
        //         {
        //             useStartImage = false;
        //             useDefaultDesignImage = false;
        //
        //             var imageTag = imageCont.querySelector(".image_container > img");
        //
        //             imageTag.src = imageSRC;
        //         }
        //     }
        // }
        // else if( mode === "color" )
        // {
        //     useStartImage = true;
        //     useDefaultDesignImage = true;
        //
        //     var bindingInput = document.querySelector('input[name="ARTICLE_NUMBER[binding]"]:checked'),
        //         bindingValue = 'none';
        //
        //     if( bindingInput )
        //     {
        //         bindingValue = bindingInput.value;
        //     }
        //
        //     var imageTagColor = imageCont.querySelector(".image_container > img");
        //
        //     if( bindingValue && bindingValue !== 'none' )
        //     {
        //         var designCode  = itemTag.getAttribute("data-number"),
        //             bindingTag  = bindingInput.parentNode.parentNode;
        //
        //         if( bindingTag )
        //         {
        //             var bindingImages = bindingTag.getAttribute("data-image");
        //
        //             if( bindingImages )
        //             {
        //                 var dataImages  = JSON.parse(bindingImages),
        //                     imageSRC    = dataImages[ designCode ];
        //
        //                 if( imageSRC )
        //                 {
        //                     useStartImage = false;
        //
        //                     imageTagColor.src = imageSRC;
        //                 }
        //                 else if( dataImage )
        //                 {
        //                     useStartImage = false;
        //
        //                     imageTagColor.src = dataImage;
        //                 }
        //             }
        //         }
        //     }
        //     else
        //     {
        //         if( dataImage )
        //         {
        //             useStartImage = false;
        //             useDefaultDesignImage = false;
        //
        //             imageTagColor.src = dataImage;
        //         }
        //     }
        // }
        //
        // if( useStartImage )
        // {
        //     var designImage = '';
        //
        //     if( useDefaultDesignImage )
        //     {
        //         var designInput = document.querySelector('input[name="ARTICLE_NUMBER[design]"]:checked');
        //
        //         if( designInput )
        //         {
        //             var designChooser = document.querySelector(".choose-item.color-picker.is-checked");
        //
        //             if( designChooser )
        //             {
        //                 designImage = designChooser.getAttribute("data-image");
        //             }
        //         }
        //     }
        //
        //     if( !designImage )
        //     {
        //         designImage = imageCont.querySelector(".image_container > img").getAttribute("data-default");
        //     }
        //
        //     imageCont.querySelector(".image_container > img").src = designImage;
        // }






        // if( dataImage !== null && dataImage !== undefined && dataImage !== "undefined" && dataImage.length )
        // {
        //     if( mode === "binding" )
        //     {
        //         // var imageTag = document.createElement("img");
        //
        //         // imageTag.src = dataImage;
        //
        //         // imageCont.querySelector(".binding-image").innerHTML = "";
        //         // imageCont.querySelector(".binding-image").append( imageTag );
        //
        //         if( dataImage )
        //         {
        //             var designInput = document.querySelector('input[name="ARTICLE_NUMBER[design]"]:checked'),
        //                 designValue = $config.default.design;
        //
        //             if( designInput )
        //             {
        //                 designValue = designInput.value;
        //             }
        //
        //             var dataImages  = JSON.parse(dataImage),
        //                 imageSRC    = '';
        //
        //             if( designValue )
        //             {
        //                 imageSRC = dataImages[ designValue ];
        //             }
        //             // console.log( designValue );
        //             // console.log( imageSRC );
        //             if( imageSRC )
        //             {
        //                 changedImage = true;
        //
        //                 var imageTag = imageCont.querySelector(".image_container > img");
        //
        //                 imageTag.src = imageSRC;
        //             }
        //         }
        //     }
        //     else if( mode === "color" )
        //     {
        //         var bindingInput = document.querySelector('input[name="ARTICLE_NUMBER[binding]"]:checked'),
        //             bindingValue = 'none';
        //
        //         if( bindingInput )
        //         {
        //             bindingValue = bindingInput.value;
        //         }
        //
        //         var imageTagColor = imageCont.querySelector(".image_container > img");
        //
        //         if( bindingValue && bindingValue !== 'none' )
        //         {
        //             var designCode  = itemTag.getAttribute("data-number"),
        //                 bindingTag  = bindingInput.parentNode.parentNode;
        //
        //             if( bindingTag )
        //             {
        //                 var bindingImages = bindingTag.getAttribute("data-image");
        //
        //                 if( bindingImages )
        //                 {
        //                     var dataImages  = JSON.parse(bindingImages),
        //                         imageSRC    = dataImages[ designCode ];
        //
        //                     if( imageSRC )
        //                     {
        //                         changedImage = true;
        //
        //                         imageTagColor.src = imageSRC;
        //                     }
        //                     else if( dataImage )
        //                     {
        //                         changedImage = true;
        //
        //                         imageTagColor.src = dataImage;
        //                     }
        //                 }
        //             }
        //         }
        //         else
        //         {
        //             if( dataImage )
        //             {
        //                 changedImage = true;
        //
        //                 imageTagColor.src = dataImage;
        //             }
        //         }
        //     }
        // }
        // else
        // {

        // if( !changedImage )
        // {
        //     if( mode === "color" || mode === "binding" )
        //     {
        //         imageCont.querySelector(".image_container > img").src = imageCont.querySelector(".image_container > img").getAttribute("data-default");
        //     }
        //
        //     // if( mode === "binding" )
        //     // {
        //         // imageCont.querySelector(".binding-image").innerHTML = "";
        //         // imageCont.querySelector(".image_container > img").src = imageCont.querySelector(".image_container > img").getAttribute("data-default");
        //     // }
        //     // else if( mode === "color" )
        //     // {
        //     //     imageCont.querySelector(".image_container > img").src = imageCont.querySelector(".image_container > img").getAttribute("data-default");
        //     // }
        //     // else if( mode === "tuning" )
        //     // {
        //     //     calculatePrice = false;
        //     // }
        // }

        // if( calculatePrice )
        // {
        //     this.calculateNewPrice( mode );
        // }
    };



    configurator.toggleChooser = function( contTag )
    {
        if( !contTag.classList.contains("saved") )
        {
            if( contTag.classList.contains("open") )
            {
                contTag.classList.remove("open");
            }
            else
            {
                contTag.classList.add("open");

                var siblings = IIDO.Base.getSiblings( contTag );

                if( siblings.length )
                {
                    for(var i=0; i<siblings.length; i++)
                    {
                        var sibling = siblings[ i ];

                        sibling.classList.remove("open");
                    }
                }
            }
        }
    };



    configurator.uncheckItems = function( items )
    {
        for(var i=0; i<items.length; i++)
        {
            var item = items[ i ];

            if( item.nodeType === 1 && item.classList.contains("choose-item") )
            {
                item.classList.remove("is-checked");
                item.querySelector("input").checked = false;
                item.querySelector("input").removeAttribute("checked");
            }
        }
    };



    configurator.calculateNewPrice = function( mode )
    {
        var priceNumTag = document.querySelector(".configurator-container .price-cart .price .num > .price-num"),
            price       = 0, /*parseFloat( priceNumTag.innerHTML ),*/
            itemNumber  = $itemNumber,
            inputs      = document.querySelector(".configurator-container").querySelectorAll("input"),

            useRange    = false, useDesign = false, useBinding = false, useLength = false, useFlex = false;

        for(var i=0; i<inputs.length; i++)
        {
            var inputTag    = inputs[ i ],
                inputName   = inputTag.name.replace(/^ARTICLE_NUMBER\[/, "").replace(/\]/, '');

            if( inputTag.checked || inputName === "range")
            {
                if( inputName === "range" )
                {
                    useRange = true;

                    itemNumber = itemNumber.replace('##RANGE##', inputTag.value);
                }
                else if( inputName === "design" )
                {
                    if( inputTag.value )
                    {
                        useDesign = true;

                        itemNumber = itemNumber.replace('##DESIGN##', inputTag.value);
                    }
                }
                else if( inputName === "binding" )
                {
                    useBinding = true;

                    itemNumber = itemNumber.replace('##BINDING##', inputTag.value);

                    if( inputTag.value === "none" )
                    {
                        itemNumber = itemNumber.replace(/^C./, '');
                    }
                }
                else if( inputName === "length" )
                {
                    useLength = true;

                    itemNumber = itemNumber.replace('##LENGTH##', inputTag.value);
                }
                else if( inputName === "flex" )
                {
                    useFlex = true;

                    var flexConfig      = JSON.parse( inputTag.parentNode.parentNode.getAttribute("data-config") ),
                        flexInputValue  = parseInt( inputTag.value );
                        // flexValue       = 'YYY';

                    // if( flexInputValue < $flexSoft )
                    // {
                    //     flexValue = IIDO.Shop.Configurator.getFlex('XXX', flexConfig).articleNumber;
                    // }
                    // else if( flexInputValue > $flexStiff )
                    // {
                    //     flexValue = IIDO.Shop.Configurator.getFlex('ZZZ', flexConfig).articleNumber;
                    // }
                    // else
                    // {
                    //     flexValue = IIDO.Shop.Configurator.getFlex('YYY', flexConfig).articleNumber;
                    // }

                    itemNumber = itemNumber.replace('##FLEX##', flexInputValue);
                }
                else if( inputName === "tuning" )
                {
                    var tuningNumber = inputTag.parentNode.parentNode.getAttribute("data-number");

                    if( tuningNumber !== 0 && tuningNumber !== "0" )
                    {
                        for(var intNum in $config.products)
                        {
                            var tuningProduct = $config.products[ intNum ];

                            if( tuningProduct.articleNumber === itemNumber )
                            {
                                price = (price + tuningProduct.price);
                            }
                        }
                    }
                }
            }
        }

        if( !useDesign )
        {
            itemNumber = itemNumber.replace('##DESIGN##', $config.default.design);
        }

        if( !useLength )
        {
            itemNumber = itemNumber.replace('##LENGTH##', $config.default.length);
        }

        if( !useFlex )
        {
            itemNumber = itemNumber.replace('##FLEX##', $config.default.flex);
        }

        if( !useBinding )
        {
            itemNumber = itemNumber.replace('##BINDING##', $config.default.binding);

            if( $config.default.binding === "none" )
            {
                itemNumber = itemNumber.replace(/^C./, '');
            }
        }

        itemNumber = itemNumber.replace('##WOODCORE##', $config.default.woodCore);
        itemNumber = itemNumber.replace('##KEIL##', $config.default.keil);
        // console.log( $config );
// console.log( itemNumber );
// console.log( $config.products );
// console.log( $config.default );
// console.log("........");
        var skiPrice = 0;
        for(var intConfig in $config.products)
        {
            var configLine      = $config.products[ intConfig ],
                woodCore        = (($version === 'v3') ? $config.config.woodCores[0] : $config.woodCores[0] ),
                checkItemNumber = itemNumber.replace('.' + $config.default.woodCore + '.', '.' + woodCore + '.');
            // console.log( $config.config );
            // console.log( configLine );
            // console.log( ( configLine.articleNumber === checkItemNumber || configLine.articleNumber === itemNumber ) );
            // console.log("--------------");
            var itemArticleNumber = configLine.articleNumber;

            if( itemArticleNumber )
            {
                if( itemArticleNumber.match(/^C./) && itemArticleNumber.match(/.none$/) === null )
                {
                    itemArticleNumber = itemArticleNumber + '.none';
                    // console.log( itemArticleNumber );
                }

                if( itemArticleNumber === checkItemNumber || itemArticleNumber === itemNumber )
                {
                    skiPrice = parseFloat(configLine.price);
                    // console.log( skiPrice );
                    price = (price + skiPrice);
                    break;
                }
            }
        }
// console.log( price );

        if( isNaN(price) )
        {
            price = 0;
        }

        if( skiPrice === 0 )
        {
            // var mode = inputName;

            // if( inputName === "flex" )
            // {
            //     mode = 'flex';
            // }

            price = this.getPriceFromProductNumber(itemNumber, price, mode);
        }
        else
        {
            document.getElementById("realItemNumber").value = itemNumber;
        }

        var tuningInput = document.querySelector('input[name="tuning"]:checked');

        if( tuningInput )
        {
            if( tuningInput.value !== "Standardtuning" )
            {
                var tuningPrice = parseFloat( $config.tunings[ tuningInput.value ].price );

                if( tuningPrice && tuningPrice > 0)
                {
                    price = (price + tuningPrice);
                }
            }
        }

        priceNumTag.innerHTML = IIDO.Shop.renderPrice(price, true);
    };



    configurator.getPriceFromProductNumber = function( itemNumber, price, mode )
    {
        if( mode === undefined || mode === "undefined" || mode === null )
        {
            mode = 'default';
        }
// console.log( $config.products );
        var skiPrice = 0, defaultItemNumber = itemNumber,
            arrKeils = (($version === "v3") ? $config.config.keils : $config.keils);
// console.log( defaultItemNumber );
        for(var keilKey=0; keilKey<arrKeils.length; keilKey++)
        {
            var keil = arrKeils[ keilKey ];

            itemNumber = defaultItemNumber.replace('.' + $config.default.keil + '.', '.' + keil + '.');
// console.log( itemNumber );
//             console.log( $config.products );
            for(var intConfig=0; intConfig<$config.products.length; intConfig++)
            {
                var configLine          = $config.products[ intConfig ],
                    itemArticleNumber   = configLine.articleNumber;

                if( itemArticleNumber.match(/^C./) === null && itemArticleNumber.match(/.none$/) === null )
                {
                    itemArticleNumber = itemArticleNumber + '.none';
                    // console.log( itemArticleNumber );
                }

                if( itemArticleNumber === itemNumber )
                {
                    skiPrice = parseInt(configLine.price);

                    price = (price + skiPrice);

                    if( skiPrice > 0 )
                    {
                        document.getElementById("realItemNumber").value = configLine.articleNumber;
                    }
                    break;
                }
            }

            if( skiPrice > 0 )
            {
                break;
            }
        }
// console.log( skiPrice );
//         console.log( mode );
//         if( mode === 'flex' && skiPrice === 0 )
        if( skiPrice === 0 )
        {
            var flexNum = this.getFlexNumFromArticleNumber( defaultItemNumber ),
                flexMax = this.getDefaultFlexMax(),
                flexMin = this.getDefaultFlexMin(),

                flexRangeTop    = (flexMax - flexNum),
                flexRangeBottom = (flexNum - flexMin);

            if( flexRangeTop < flexRangeBottom )
            {
                if( flexRangeTop > 0 )
                {
                    for(var numTop=1;numTop<flexRangeTop; numTop++)
                    {
                        var newFlexNum      = (flexNum + numTop),
                            newItemNumber   = defaultItemNumber.replace('.' + flexNum + '.', '.' + newFlexNum + '.');

                        skiPrice = this.getSkiPrice( newItemNumber );

                        if( skiPrice > 0 )
                        {
                            document.getElementById("realItemNumber").value = newItemNumber;
                            break;
                        }
                    }
                }

                if( skiPrice === 0 && flexRangeBottom > 0 )
                {
                    for(var numBottom=1;numBottom<flexRangeBottom; numBottom++)
                    {
                        var newFlexNum      = (flexNum - numBottom),
                            newItemNumber   = defaultItemNumber.replace('.' + flexNum + '.', '.' + newFlexNum + '.');

                        skiPrice = this.getSkiPrice( newItemNumber );

                        if( skiPrice > 0 )
                        {
                            document.getElementById("realItemNumber").value = newItemNumber;
                            break;
                        }
                    }
                }
            }
            else
            {
                if( flexRangeBottom > 0 )
                {
                    for(var numBottom=1;numBottom<flexRangeBottom; numBottom++)
                    {
                        var newFlexNum      = (flexNum - numBottom),
                            newItemNumber   = defaultItemNumber.replace('.' + flexNum + '.', '.' + newFlexNum + '.');

                        skiPrice = this.getSkiPrice( newItemNumber );

                        if( skiPrice > 0 )
                        {
                            document.getElementById("realItemNumber").value = newItemNumber;
                            break;
                        }
                    }
                }

                if( skiPrice === 0 && flexRangeTop > 0 )
                {
                    for(var numTop=1;numTop<flexRangeTop; numTop++)
                    {
                        var newFlexNum      = (flexNum + numTop),
                            newItemNumber   = defaultItemNumber.replace('.' + flexNum + '.', '.' + newFlexNum + '.');

                        skiPrice = this.getSkiPrice( newItemNumber );

                        if( skiPrice > 0 )
                        {
                            document.getElementById("realItemNumber").value = newItemNumber;
                            break;
                        }
                    }
                }
            }

            if( skiPrice > 0 )
            {
                price = (skiPrice + price);
            }
        }

        return price;
    };



    configurator.getSkiPrice = function( defaultItemNumber )
    {
        var skiPrice = 0,
            arrKeils = (($version === "v3") ? $config.config.keils : $config.keils);

        for(var keilKey=0; keilKey<arrKeils.length; keilKey++)
        {
            var keil = arrKeils[ keilKey ],

                itemNumber = defaultItemNumber.replace( '.' + $config.default.keil + '.', '.' + keil + '.' );

            for(var intConfig=0; intConfig<$config.products.length; intConfig++)
            {
                var configLine = $config.products[ intConfig ];

                if( configLine.articleNumber === itemNumber )
                {
                    skiPrice = parseInt(configLine.price);
                    break;
                }
            }

            if( skiPrice > 0 )
            {
                break;
            }
        }

        return skiPrice;
    };



    configurator.getFlexNumFromArticleNumber = function( itemNumber )
    {
        var flexIndex           = 4,
            arrItemNumerParts   = itemNumber.split(".");

        if( arrItemNumerParts[0] === 'C' )
        {
            flexIndex = 5;
        }

        return arrItemNumerParts[ flexIndex ];
    };



    configurator.getDefaultFlexMin = function()
    {
        var minFlex = 60,
            arrFlexs = (($version === "v3") ? $config.config.flexs : $config.flexs);

        for(var flexKey=0; flexKey<arrFlexs.length; flexKey++)
        {
            var flex = parseInt( arrFlexs[ flexKey ] );

            if( flex < minFlex )
            {
                minFlex = flex;
            }
        }

        return minFlex;
    };



    configurator.getDefaultFlexMax = function()
    {
        var maxFlex = 50,
            arrFlexs = (($version === "v3") ? $config.config.flexs : $config.flexs);

        for(var flexKey=0; flexKey<arrFlexs.length; flexKey++)
        {
            var flex = parseInt( arrFlexs[ flexKey ] );

            if( flex > maxFlex )
            {
                maxFlex = flex;
            }
        }

        return maxFlex;
    };



    configurator.checkAndSubmitForm = function( itemTag, formID )
    {
        var inputs = itemTag.querySelectorAll("input");

        if( inputs.length )
        {
            for(var i=0; i<inputs.length; i++)
            {
                inputs[ i ].checked = true;
            }
        }

        document.getElementById("fakeLoader").style.display = "block";

        this.submitForm( formID );
    };



    configurator.submitForm = function( formID )
    {
        var formContainer = document.getElementById("shopConfigurator_" + formID);

        if( formContainer )
        {
            formContainer.submit();
        }
    };



    configurator.initConfig = function( arrConfig )
    {
        $config = arrConfig;
    };



    configurator.initNewConfig = function( skiNumber )
    {
        jQuery.getJSON('assets/shop_tmp/v3-shop-configurator-config-' + skiNumber + '.json', function(responseText)
        {
            $config = responseText;
        });

        $version = 'v3';
    };



    configurator.getProduct = function()
    {
        var itemNumber = $itemNumber, product = {}, tuningNumber = $config.default.tuning;

        for(var i=0; i<$formFields.length; i++)
        {
            var fieldName   = $formFields[ i ],
                fieldTag    = document.querySelector(".configurator-container").querySelector( 'input[name="' + fieldName + '"]' );

            if( fieldName.indexOf("ARTICLE_NUMBER") !== -1 )
            {
                var upperFieldName  = (fieldName.replace(/^ARTICLE_NUMBER\[/, '').replace(/\]$/, '')).toUpperCase();

                if( fieldName !== 'ARTICLE_NUMBER[range]' )
                {
                    fieldTag = document.querySelector(".configurator-container").querySelector( 'input[name="' + fieldName + '"]:checked' );

                    if( fieldName === "ARTICLE_NUMBER[flex]" )
                    {
                        fieldTag = document.querySelector(".configurator-container").querySelector( 'input[name="' + fieldName + '"]' );
                    }
                }

                itemNumber = itemNumber.replace("##" + upperFieldName + '##', fieldTag.value);
            }
            else
            {
                product[ fieldName ] = fieldTag.value;
            }
        }

        itemNumber = itemNumber.replace("##WOODCORE##", $woodCoreDefault[0]);
        itemNumber = itemNumber.replace("##KEIL##", '__');

        var tuningInput = document.querySelector('input[name="tuning"]:checked');

        if( tuningInput )
        {
            tuningNumber = tuningInput.value;
        }

        product.itemNumber  = itemNumber;
        product.realItemNumber = document.getElementById("realItemNumber").value;
        product.quantity    = 1;
        product.tuning      = tuningNumber;

        return product;
    };



    configurator.addProductToCart = function()
    {
        if( this.checkForm() )
        {
            var product = this.getProduct(),
                objData = [],
                url     = location.href + '?as=ajax&ag=iidoShop&aa=getConfiguratorAddToCartMessage';

            objData.push( {'name' : 'productName', 'value' : product.name} );

            $.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                data: objData,
                success: function (response, textStatus, jqXHR)
                {
                    if( response.result.html.content )
                    {
                        IIDO.Shop.showMessage("confirm", response.result.html.content, "center", "own-text", true);
                    }
                    else
                    {
                        IIDO.Shop.showMessage("confirm", "addToCartMessage", "center");
                    }

                    IIDO.Shop.Cart.addProductToCart( product );
                    IIDO.Shop.Configurator.updateCartNum();
                }
            });
        }
    };



    configurator.addProductToWatchlist = function()
    {
        if( this.checkForm() )
        {
            var product = this.getProduct(),
                objData = [],
                url     = location.href + '?as=ajax&ag=iidoShop&aa=getConfiguratorAddToWatchlistMessage';

            objData.push( {'name' : 'productName', 'value' : product.name} );

            $.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                data: objData,
                success: function (response, textStatus, jqXHR)
                {
                    if( response.result.html.content )
                    {
                        IIDO.Shop.showMessage("confirm", response.result.html.content, "center", "own-text", true);
                    }
                    else
                    {
                        IIDO.Shop.showMessage("confirm", "addToWatchlistMessage", "center");
                    }

                    IIDO.Shop.Watchlist.addProductToWatchlist( product );
                    IIDO.Shop.Configurator.updateWatchlistNum();
                }
            });
        }
    };



    configurator.updateCartNum = function()
    {
        var numTag = $configurator.querySelector(".price-cart .cart .num");

        if( numTag.classList.contains("has-link") )
        {
            numTag = numTag.querySelector("a");
        }

        var numValue = parseInt( numTag.innerHTML );

        numTag.innerHTML = (numValue + 1);
    };



    configurator.updateWatchlistNum = function()
    {
        var numTag = $configurator.querySelector(".price-cart .cart .watchlist-num");

        if( numTag.classList.contains("has-link") )
        {
            numTag = numTag.querySelector("a");
        }

        var numValue = parseInt(numTag.innerHTML);

        numTag.innerHTML = (numValue + 1);

        if( numTag.classList.contains("is-hidden") )
        {
            numTag.classList.remove("is-hidden");
        }
    };



    configurator.checkForm = function()
    {
        for(var num=0; num < $fieldNames.length; num++)
        {
            var fieldTagName    = $fieldNames[ num ],
                fieldTag        = document.querySelector( 'input[name="ARTICLE_NUMBER[' + fieldTagName + ']"]:checked' );

            if( fieldTag === null && fieldTagName !== "flex" )
            {
                IIDO.Shop.showMessage( "error", fieldTagName, $configurator.querySelector(".configurator-infos > ." + fieldTagName) );
                return false;
            }
        }

        return true;
    };



    configurator.showMessage = function( messageType, messageName, messageParent )
    {
        var messageContainer = document.getElementById( "shopMessage" );

        if( !messageContainer )
        {
            messageContainer = document.createElement("div");
            messageContainer.classList.add("message-container");
            messageContainer.setAttribute("id", "shopMessage");

            var messageTag = document.createElement("div");

            messageTag.classList.add("message-inside");
            messageTag.innerHTML = this.getMessageText( messageName );

            messageContainer.append( messageTag );

            $configurator.append( messageContainer );
        }
        else
        {
            messageContainer.querySelector(".message-inside").innerHTML = this.getMessageText( messageName );
        }


        messageContainer.classList.remove("error-message");
        messageContainer.classList.remove("confirm-message");
        messageContainer.classList.remove("pos-not-center");

        messageContainer.classList.add(messageType + "-message");
        messageContainer.classList.add("shown");

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

        document.addEventListener("click", IIDO.Shop.Configurator.hideMessage, true);
    };



    configurator.hideMessage = function()
    {
        var messageCont = document.getElementById( "shopMessage" );

        if( messageCont )
        {
            messageCont.classList.remove("shown");
        }

        document.removeEventListener("click", IIDO.Shop.Configurator.hideMessage, true);
    };



    configurator.getMessageText = function( messageKey )
    {
        return $errorMessage[ $langKey ][ messageKey ];
    };



    configurator.updateFlex = function( flexInput )
    {
        var flexValue   = parseInt( flexInput.value ),
            flexConfig  = JSON.parse( flexInput.parentNode.parentNode.getAttribute("data-config") ),
            flexChooser = flexInput.parentNode.parentNode.nextElementSibling;

        if( flexValue < $flexSoft )
        {
            flexValue = IIDO.Shop.Configurator.getFlex('XXX', flexConfig).title;
        }
        else if( flexValue > $flexStiff )
        {
            flexValue = IIDO.Shop.Configurator.getFlex('ZZZ', flexConfig).title;
        }
        else
        {
            flexValue = IIDO.Shop.Configurator.getFlex('YYY', flexConfig).title;
        }

        flexChooser.innerHTML = flexValue;
    };



    configurator.updateFlexDesc = function( flexRange, flexDescTag )
    {
        var flexInput = flexDescTag.parentNode.previousElementSibling.querySelector("input");

        flexInput.value = flexRange;

        this.updateFlex( flexInput );
    };



    configurator.openProductDetails = function( detailTag )
    {
        IIDO.Shop.openProductDetails( detailTag );
    };



    configurator.getFlex = function( flexNum, flexConfig )
    {
        var flex = {};
// console.log( flexConfig );
        for(var i=0; i<flexConfig.length; i++)
        {
            if( flexConfig[ i ].articleNumber === flexNum )
            {
                flex = flexConfig[ i ];
            }
        }

        return flex;
    };



    configurator.updateProduct = function( mode )
    {
        var currentItemNumber   = document.querySelector('input[name="CURRENT_ARTICLE_NUMBER"]').value,
            itemName            = document.querySelector('input[name="name"]').value,
            error               = false,
            product             = this.getProduct();

        if( currentItemNumber )
        {
            if( mode === "cart" )
            {
                var cartlist = IIDO.Shop.Cart.getList();

                for(var ci=0; ci<cartlist.length; ci++)
                {
                    var cartlistItem = cartlist[ ci ];

                    if( cartlistItem.itemNumber === currentItemNumber && cartlistItem.name === itemName)
                    {
                        cartlist[ ci ].itemNumber  = product.itemNumber;
                        cartlist[ ci ].tuning      = product.tuning;

                        cartlist[ ci ].realItemNumber = product.realItemNumber;
                    }
                }

                IIDO.Shop.Cart.updateList( cartlist );
            }
            else
            {
                var watchlist = IIDO.Shop.Watchlist.getList();

                for(var wi=0; wi<watchlist.length; wi++)
                {
                    var watchlistItem = watchlist[ wi ];

                    if( watchlistItem.itemNumber === currentItemNumber && watchlistItem.name === itemName)
                    {
                        watchlist[ wi ].itemNumber  = product.itemNumber;
                        watchlist[ wi ].tuning      = product.tuning;

                        watchlist[ wi ].realItemNumber = product.realItemNumber;
                    }
                }

                IIDO.Shop.Watchlist.updateList( watchlist );
            }
        }

        IIDO.Shop.showMessage((error ? "error" : "confirm"), "updateProduct" + (error ? 'Error' : ''), "center");
    };


})(window, jQuery, IIDO.Shop.Configurator);

document.addEventListener("DOMContentLoaded", function()
{
    IIDO.Shop.Configurator.initCatHover();
});