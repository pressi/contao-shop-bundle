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
    var $configurator, $config = {};



    configurator.init = function( contID )
    {
        $configurator = document.getElementById('configuratorCont_' + contID);

        var canvasDetail    = document.getElementById("canvasDetail"),
            // ctx             = canvasDetail.getContext("2d"),

            color           = $configurator.getAttribute("data-color"),

            canvasWidth     = ((window.innerWidth * 0.86) + 10),
            canvasHeight    = window.innerHeight;

        // $configurator.style.background = color;

        if( window.innerWidth <= respWidth )
        {
            canvasWidth   = window.innerWidth;
            canvasHeight  = (window.innerHeight * 0.86);
        }

        var arrCanvas = runCanvasFactor(canvasDetail, canvasWidth, canvasHeight);

        this.generateCanvas( arrCanvas[0], color, arrCanvas[1], arrCanvas[2] );
    };



    configurator.generateCanvas = function( ctx, color, width, height )
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
        ctx.shadowColor = "rgba(100, 100, 100, .35)";
        ctx.shadowOffsetX = ctx.shadowOffsetY = 0;
        ctx.shadowBlur = 25;
        ctx.fill();
    };



    configurator.checkItem = function( itemTag )
    {
        this.uncheckItems( itemTag.parentNode.childNodes );

        itemTag.classList.add("is-checked");
        itemTag.querySelector("input").checked = true;

        if( itemTag.classList.contains("color-picker") )
        {
            var colorAlias = itemTag.getAttribute("data-alias");

            itemTag.parentNode.nextElementSibling.innerHTML = '<div class="color_circle cc-' + colorAlias + '"></div>';
        }
        else
        {
            itemTag.parentNode.nextElementSibling.innerHTML = itemTag.querySelector(".name").innerHTML;
        }

        this.calculateNewPrice();
    };



    configurator.toggleChooser = function( contTag )
    {
        if( contTag.classList.contains("open") )
        {
            contTag.classList.remove("open");
        }
        else
        {
            contTag.classList.add("open");
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
            }
        }
    };



    configurator.calculateNewPrice = function()
    {
        var price       = 0,
            itemNumber  = '##RANGE##.##DESIGN##.##LENGTH##.##FLEX##.##BINDING##',
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
                    useDesign = true;

                    itemNumber = itemNumber.replace('##DESIGN##', inputTag.value);
                }
                else if( inputName === "binding" )
                {
                    useBinding = true;

                    itemNumber = itemNumber.replace('##BINDING##', inputTag.value);
                }
                else if( inputName === "length" )
                {
                    useLength = true;

                    itemNumber = itemNumber.replace('##LENGTH##', inputTag.value);
                }
                else if( inputName === "flex" )
                {
                    useFlex = true;

                    itemNumber = itemNumber.replace('##FLEX##', inputTag.value);
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
        }

        for(var intConfig in $config.products)
        {
            var configLine = $config.products[ intConfig ];

            if( configLine.articleNumber === itemNumber )
            {
                price = configLine.price;
            }
        }

        document.querySelector(".configurator-container .price-cart .price .num > .price-num").innerHTML = price;
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


})(window, jQuery, IIDO.Shop.Configurator);