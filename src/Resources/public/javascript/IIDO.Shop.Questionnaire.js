/******************************************************/
/*                                                    */
/*  (c) 2017 IIDO, www.iido.at <development@iido.at>  */
/*                                                    */
/******************************************************/
var IIDO = IIDO || {};
IIDO.Shop = IIDO.Shop || {};
IIDO.Shop.Questionnaire = IIDO.Shop.Questionnaire || {};

(function (window, $, questionnaire)
{
    var $questionnaire, $activePage, $hasOverview = false, $maxPages = 0, $lang = 'de', $handleCalculate = false;

    questionnaire.init = function( questionnaireID, saveData, saveID )
    {
        if( document.body.classList.contains("lang-en") )
        {
            $lang = 'en';
        }

        if( saveData === undefined || saveData === "undefined" || saveData === null )
        {
            saveData = false;
        }

        $activePage     = 0;
        $questionnaire  = document.getElementById("shopQuestionnaire_" + questionnaireID);

        if( $questionnaire.classList.contains("has-overview-page") )
        {
            $hasOverview = true;
        }

        $maxPages = parseInt($questionnaire.getAttribute("data-max-pages"));

        if( saveData )
        {
            this.initSaveFormData( saveID, questionnaireID );
        }

        this.initProgressSteps();
        this.initImageMap();
        // this.initInputFields();
    };



    questionnaire.initInputFields = function()
    {
        var inputAnswers = $questionnaire.querySelectorAll(".answer-item.input-answer:not(.select-input-answer) input");

        if( inputAnswers.length )
        {
            for(var i=0; i<inputAnswers.length; i++)
            {
                var inputAnswer = inputAnswers[ i ];

                inputAnswer.addEventListener("blur", function()
                {
                    console.log( this.value );
                });
            }
        }
    };



    questionnaire.nextPage = function( nextButton )
    {
        var overview = $questionnaire.getAttribute("data-overview");

        if( overview === undefined || overview === "undefined" || overview === null )
        {
            overview = false;
        }
        else
        {
            overview = true;
        }

        if( overview )
        {
            if( this.validatePage(nextButton) )
            {
                var currentPageNum = $activePage;

                $activePage = ($maxPages - 1);

                this.checkPageStatus( "overview" );
                this.checkPageAnimation();
                this.updateOverviewPageAnswers( currentPageNum );
            }
        }
        else
        {
            if( this.validatePage(nextButton) )
            {
                $activePage = ($activePage + 1);

                this.checkPageStatus( "next" );
                this.checkPageAnimation();
                this.updateOverviewPageAnswers();
            }
        }
    };

    questionnaire.prevPage = function()
    {
        $activePage = ($activePage - 1);

        this.checkPageStatus( "prev" );
        this.checkPageAnimation();
        this.updateOverviewPageAnswers();
    };



    questionnaire.checkPageStatus = function( mode )
    {
        if( $activePage >= 1 )
        {
            $questionnaire.querySelector(".progress-steps").classList.add("shown");
        }
        else if( $activePage <= 0)
        {
            $questionnaire.querySelector(".progress-steps").classList.remove("shown");
        }

        if( $hasOverview && $activePage === $maxPages )
        {
            $questionnaire.querySelector(".progress-steps").classList.remove("shown");
        }

        var pageItem    = $questionnaire.querySelector(".page-item.is-active"),
            nextPage    = pageItem.nextElementSibling;

        if( mode === "prev" )
        {
            nextPage = pageItem.previousElementSibling;
        }
        else if( mode === "overview" )
        {
            nextPage = $questionnaire.querySelector(".page-item.overview-page");
        }

        if( nextPage )
        {
            pageItem.classList.remove("is-active");

            if( nextPage.classList.contains("page-" + $activePage) )
            {
                nextPage.classList.add("is-active");
            }
            else
            {
                $questionnaire.querySelector(".page-item.page-" + $activePage).classList.add("is-active");
            }

            var overviewPage = false;

            if( nextPage.classList.contains("overview-page") )
            {
                setTimeout(function()
                {
                    $(nextPage).animate({
                        scrollTop: $(nextPage).height()
                    }, "slow");
                }, 500);

                overviewPage = true;

                var overviewButtons = $questionnaire.querySelectorAll(".button-overview");

                if( overviewButtons.length )
                {
                    for( var obi=0; obi<overviewButtons.length; obi++ )
                    {
                        overviewButtons[ obi ].classList.remove("hidden");
                    }
                }

                $questionnaire.setAttribute("data-overview", "1");
            }

            var pageHeight  = 0,
                pageItems   = nextPage.querySelectorAll(".page-item-cont > *"),
                winHeight   = window.innerHeight;

            if( overviewPage )
            {
                pageItems = nextPage.querySelectorAll(".page-item-inside > *");
            }

            if( pageItems.length )
            {
                for( var i=0; i<pageItems.length; i++ )
                {
                    pageHeight = (pageHeight + pageItems[ i ].offsetHeight);
                }
            }

            if( pageHeight < winHeight )
            {
                pageHeight = winHeight;
            }

            if( pageHeight > 0 )
            {
                $questionnaire.style.height = pageHeight + 'px';
            }
        }

        // this.checkProgressBar();
        this.checkProgressSteps();
    };



    questionnaire.backToOverview = function( buttonTag )
    {
        if( this.validatePage(buttonTag) )
        {
            var currentPageNum = $activePage;

            $activePage = ($maxPages - 1 );

            this.checkPageStatus( "overview" );
            this.checkPageAnimation();
            this.updateOverviewPageAnswers( currentPageNum );
        }
    };



    questionnaire.initProgressSteps = function()
    {
        var progressSteps   = $questionnaire.querySelector(".progress-steps"),
            stepPages       = progressSteps.querySelectorAll(".progress-page"),

            stepWidth       = stepPages[0].offsetWidth,
            stepStyles      = stepPages[0].currentStyle || window.getComputedStyle(stepPages[0]),
            stepMargin      = parseInt( stepStyles.marginRight ),

            progressWidth   = ((stepMargin + stepWidth) * stepPages.length) - stepMargin;

        if( progressWidth > 0 )
        {
            progressSteps.style.width = progressWidth + 'px';
        }
    };



    questionnaire.checkProgressSteps = function()
    {
        var stepPages = $questionnaire.querySelectorAll(".progress-steps > .progress-page");

        if( stepPages.length )
        {
            for(var num = 0; num < stepPages.length; num++)
            {
                var stepPage = stepPages[ num ];

                if( $activePage >= (num + 1) )
                {
                    stepPage.classList.add("is-active");
                }
                else
                {
                    stepPage.classList.remove("is-active");
                }
            }
        }
    };



    // questionnaire.toggleAnswer__Old = function( answerContainer )
    // {
    //     var answerItem      = answerContainer.parentNode.parentNode,
    //         configContainer = answerItem.parentNode.parentNode;
    //
    //     if( answerItem.classList.contains("image-map-point") )
    //     {
    //         configContainer = answerItem.parentNode.parentNode.parentNode;
    //     }
    //
    //     var inputTag        = answerContainer.querySelector("input"),
    //
    //         minAnswers      = parseInt(configContainer.getAttribute("data-min-answers")),
    //         maxAnswers      = parseInt(configContainer.getAttribute("data-max-answers")),
    //         ovAnswerItem    = false;
    //
    //     if( $hasOverview )
    //     {
    //         ovAnswerItem = this.getOverviewAnswerItem( configContainer, answerItem );
    //     }
    //
    //     if( answerItem.classList.contains("is-checked") )
    //     {
    //         answerItem.classList.remove("is-checked");
    //         inputTag.checked = false;
    //
    //         configContainer.parentNode.querySelector(".error-msg").innerHTML = '';
    //
    //         if( $hasOverview && ovAnswerItem )
    //         {
    //             ovAnswerItem.classList.remove("is-checked");
    //         }
    //     }
    //     else
    //     {
    //         answerItem.classList.add("is-checked");
    //         inputTag.checked = true;
    //
    //         if( $hasOverview && ovAnswerItem )
    //         {
    //             ovAnswerItem.classList.add("is-checked");
    //         }
    //
    //         if( maxAnswers > 1)
    //         {
    //             if( !this.checkAnswerStatus(answerContainer, minAnswers, maxAnswers) )
    //             {
    //                 answerItem.classList.remove("is-checked");
    //                 inputTag.checked = false;
    //
    //                 if( $hasOverview && ovAnswerItem )
    //                 {
    //                     ovAnswerItem.classList.remove("is-checked");
    //                 }
    //             }
    //         }
    //
    //         if( maxAnswers === 1 )
    //         {
    //             $(answerItem).siblings().removeClass("is-checked");
    //             $(answerItem).siblings().find("input").checked = false;
    //
    //             if( $hasOverview && ovAnswerItem )
    //             {
    //                 $(ovAnswerItem).siblings().removeClass("is-checked");
    //                 // $(ovAnswerItem).siblings().find("input").checked = false;
    //             }
    //         }
    //     }
    //
    //     if( maxAnswers === 1 )
    //     {
    //         this.goToNextPage();
    //     }
    // };



    questionnaire.toggleAnswer = function( answerContainer )
    {
        var hasError        = false,

            answerItem      = answerContainer.parentNode.parentNode,
            configContainer = answerItem.parentNode.parentNode;

        if( answerItem.classList.contains("not-clickable") )
        {
            return;
        }

        if( answerItem.classList.contains("image-map-point") )
        {
            configContainer = answerItem.parentNode.parentNode.parentNode;
        }

        var inputTag        = answerContainer.querySelector("input"),

            minAnswers      = parseInt(configContainer.getAttribute("data-min-answers")),
            maxAnswers      = parseInt(configContainer.getAttribute("data-max-answers")),
            ovAnswerItem    = false;

        if( $hasOverview )
        {
            ovAnswerItem = this.getOverviewAnswerItem( configContainer, answerItem );
        }

        if( answerItem.classList.contains("is-checked") )
        {
            answerItem.classList.remove("is-checked");
            inputTag.checked = false;

            configContainer.parentNode.querySelector(".error-msg").innerHTML = '';

            if( $hasOverview && ovAnswerItem )
            {
                ovAnswerItem.classList.remove("is-checked");
            }

            hasError = true;
        }
        else
        {
            answerItem.classList.add("is-checked");
            inputTag.checked = true;

            if( $hasOverview && ovAnswerItem )
            {
                ovAnswerItem.classList.add("is-checked");
            }

            if( maxAnswers > 1 )
            {
                if( !this.validateQuestion( answerItem.parentNode.parentNode.parentNode.parentNode, minAnswers, maxAnswers ) )
                {
                    answerItem.classList.remove("is-checked");
                    inputTag.checked = false;

                    if( $hasOverview && ovAnswerItem )
                    {
                        ovAnswerItem.classList.remove("is-checked");
                    }

                    hasError = true;
                }
            }
            else if( maxAnswers === 1 )
            {
                $(answerItem).siblings().removeClass("is-checked");
                $(answerItem).siblings().find("input").checked = false;

                if( $hasOverview && ovAnswerItem )
                {
                    $(ovAnswerItem).siblings().removeClass("is-checked");
                    // $(ovAnswerItem).siblings().find("input").checked = false;
                }
            }
        }

        if( maxAnswers === 1 && !hasError )
        {
            this.removeMessageFromQuestion( answerItem.parentNode.parentNode.parentNode.parentNode );
            this.goToNextPage();
        }
    };



    questionnaire.validateQuestion = function( questionItem, minAnswers, maxAnswers )
    {
        var answersCont = questionItem.querySelector(".answers-container");

        if( minAnswers === undefined || minAnswers === "undefined" || minAnswers === null )
        {
            minAnswers = parseInt(answersCont.getAttribute("data-min-answers"));
        }

        if( maxAnswers === undefined || maxAnswers === "undefined" || maxAnswers === null )
        {
            maxAnswers = parseInt(answersCont.getAttribute("data-max-answers"));
        }

        var answersItems    = answersCont.querySelectorAll(".answer-item"),
            checkedItems    = answersCont.querySelectorAll(".answer-item.is-checked"),
            checkedCount    = checkedItems.length,

            noInput         = true;

        for( var i=0; i<answersItems.length; i++ )
        {
            var answerItem      = answersItems[ i ];

            if( answerItem.classList.contains("input-answer") )
            {
                noInput = false;
            }
        }

        if( (checkedCount < minAnswers || checkedCount > maxAnswers) && noInput )
        {
            if( checkedCount < minAnswers )
            {
                var muss        = ((minAnswers === 1) ? 'muss' : 'müssen'),
                    antwort     = ((minAnswers === 1) ? 'ne Antwort' : ' Antworten'),
                    msgText     = 'Es ' + muss + ' mindestens ' + minAnswers + antwort  + ' ausgewählt werden.';

                if( $lang === 'en' )
                {
                    msgText = ((minAnswers === 1) ? 'At least one answer must be selected.' : 'At least ' + minAnswers + ' answers must be selected!');
                }

                this.addMessageToQuestion( questionItem, msgText);
            }
            else
            {
                var darf        = ((maxAnswers === 1) ? 'darf' : 'dürfen'),
                    antwortMax  = ((maxAnswers === 1) ? 'ne Antwort' : ' Antworten'),
                    msgMaxText  = 'Es ' + darf + ' maximal nur ' + maxAnswers + antwortMax  + ' ausgewählt werden.';

                if( $lang === 'en' )
                {
                    msgMaxText = ((maxAnswers === 1) ? 'At most only one answer may be selected.' : 'Only a maximum of ' + maxAnswers + ' answers may be selected.');
                }

                this.addMessageToQuestion( questionItem, msgMaxText);
            }

            return false;
        }
        else
        {
            if( minAnswers === 1 && maxAnswers === 1 && noInput )
            {
                return true;
            }

            if( !noInput )
            {
                var answerItem  = answersCont.querySelector(".answer-item"),
                    answerInput = answerItem.querySelector("input");

                if( answerItem.classList.contains("is-textarea") )
                {
                    answerInput = answerItem.querySelector("textarea");
                }

                var varValue = answerInput.value;

                if( answerItem.classList.contains("select-input-answer") )
                {
                    var selectTag = answerItem.querySelector(".select-tag-container");

                    if( selectTag )
                    {
                        var checkedSelectItem = selectTag.querySelector(".is-active");

                        if( checkedSelectItem )
                        {
                            varValue = checkedSelectItem.querySelector("input").value;
                        }
                    }
                    else
                    {
                        selectTag = answerItem.querySelector("select");

                        varValue = selectTag.value;
                    }
                }

                var fieldName = answerItem.querySelector("label") ? ' "' + answerItem.querySelector("label").innerHTML + '"' : '';

                if( minAnswers === 1 && varValue.length === 0 && varValue === "" )
                {
                    var fieldMsgText = 'Das Feld' + fieldName + ' muss ausgefüllt werden!';

                    if( $lang === 'en' )
                    {
                        fieldMsgText = 'The field' + fieldName + ' must be filled out!'
                    }

                    this.addMessageToQuestion( questionItem, fieldMsgText );

                    return false;
                }
                else
                {
                    if( varValue.length )
                    {
                        var validateMode    = answerItem.getAttribute("data-validate");

                        if( !this.validateField( validateMode, varValue, answerItem, fieldName ) )
                        {
                            // this.addErrorToAnswer(answerItem);
                            return false;
                        }
                    }
                }
            }
        }

        this.removeMessageFromQuestion( questionItem );

        return true;
    };




    questionnaire.validatePage = function( buttonTag )
    {
        if( $activePage === 0 )
        {
            return true;
        }

        var hasError = false,
            pageContainer;

        if( buttonTag.nodeName === "BUTTON" || (buttonTag.nodeName === "A" && buttonTag.classList.contains("button-overview")) )
        {
            pageContainer = buttonTag.parentNode.parentNode.parentNode;
        }
        else
        {
            pageContainer = buttonTag.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
        }

        var questionItems = pageContainer.querySelectorAll(".question-item");

        if( questionItems.length )
        {
            for(var i=0; i<questionItems.length; i++)
            {
                var questionItem = questionItems[ i ];

                if( !this.validateQuestion( questionItem ) )
                {
                    hasError = true;
                }
            }
        }

        return !hasError;
    };



    questionnaire.addMessageToQuestion = function(questionItem, strMessage)
    {
        var messageTag = questionItem.querySelector(".error-msg");

        messageTag.innerHTML = strMessage;
    };



    questionnaire.removeMessageFromQuestion = function(questionItem, strMessage)
    {
        var messageTag = questionItem.querySelector(".error-msg");

        messageTag.innerHTML = "";
    };



    questionnaire.toggleAnswerSelect = function( answerSelectTagOption )
    {
        var answerItem      = answerSelectTagOption.parentNode.parentNode.parentNode.parentNode,
            configContainer = answerItem.parentNode.parentNode,
            inputTag        = answerSelectTagOption.querySelector("input");

        inputTag.checked = true;
        answerSelectTagOption.classList.add("is-active");

        $(answerSelectTagOption).siblings().removeClass("is-active");
        $(answerSelectTagOption).siblings().find("input").checked = false;

        var ovAnswerItem    = false;

        if( $hasOverview )
        {
            ovAnswerItem = this.getOverviewAnswerItem( configContainer, answerItem );
        }

        if( $hasOverview && ovAnswerItem )
        {
            var inputCont       = ovAnswerItem.querySelector(".input-container"),
                strInputValue   = inputTag.value,
                contAddon       = inputCont.getAttribute("data-addon");

            if( contAddon !== "undefned" && contAddon !== undefined && contAddon !== null )
            {
                strInputValue = strInputValue + ' ' + contAddon;
            }

            inputCont.innerHTML = strInputValue;
        }
    };



    // questionnaire.checkAnswerStatus = function( buttonTag, minAnswers, maxAnswers )
    // {
    //     var hasError = false;
    //
    //     if( $activePage === 0 )
    //     {
    //         return true;
    //     }
    //     else
    //     {
    //         var pageContainer;
    //
    //         if( buttonTag.nodeName === "BUTTON" )
    //         {
    //             pageContainer = buttonTag.parentNode.parentNode.parentNode;
    //         }
    //         else
    //         {
    //             pageContainer = buttonTag.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
    //         }
    //
    //         var questionItems = pageContainer.querySelectorAll(".question-item");
    //
    //         if( questionItems.length )
    //         {
    //             for(var i=0; i<questionItems.length; i++)
    //             {
    //                 var questionItem    = questionItems[ i ],
    //                     answersCont     = questionItem.querySelector(".answers-container"),
    //                     answersItems    = answersCont.querySelectorAll(".answer-item"),
    //                     checkedItems    = answersCont.querySelectorAll(".answer-item.is-checked"),
    //                     message         = questionItem.querySelector(".error-msg"),
    //
    //                     questMinAns     = parseInt(answersCont.getAttribute("data-min-answers")),
    //                     questMaxAns     = parseInt(answersCont.getAttribute("data-max-answers"));
    //
    //                 if( minAnswers !== undefined && minAnswers !== "undefined" && minAnswers !== null && minAnswers )
    //                 {
    //                     questMinAns = minAnswers;
    //                 }
    //
    //                 if( maxAnswers !== undefined && maxAnswers !== "undefined" && maxAnswers === null && maxAnswers )
    //                 {
    //                     questMaxAns = maxAnswers;
    //                 }
    //
    //                 var hasChecked = false, strMessage = '',
    //                     animateImage = $questionnaire.querySelector(".image.is-animated");
    //
    //                 if( checkedItems.length === 0 )
    //                 {
    //                     for(var num=0; num<answersItems.length;num++)
    //                     {
    //                         var answerItem = answersItems[ num ],
    //
    //                             validateMode    = answerItem.getAttribute("data-validate");
    //
    //                         if( answerItem.classList.contains("input-answer") )
    //                         {
    //                             hasChecked = true;
    //
    //                             var varValue = answerItem.querySelector("input").value;
    //
    //                             if( answerItem.classList.contains("select-input-answer") )
    //                             {
    //                                 var selectTag = answerItem.querySelector(".select-tag-container");
    //
    //                                 if( selectTag )
    //                                 {
    //                                     var checkedSelectItem = selectTag.querySelector(".is-active");
    //
    //                                     if( checkedSelectItem )
    //                                     {
    //                                         varValue = checkedSelectItem.querySelector("input").value;
    //                                     }
    //                                 }
    //                                 else
    //                                 {
    //                                     selectTag = answerItem.querySelector("select");
    //
    //                                     varValue = selectTag.value;
    //                                 }
    //                             }
    //
    //                             var fieldName = '', arrValidate;
    //
    //                             if( questionItems.length > 1 )
    //                             {
    //                                 fieldName = ' "' + answerItem.querySelector("label").innerHTML + '"';
    //                             }
    //
    //                             if( questMinAns > 0 )
    //                             {
    //                                 if( !varValue.length )
    //                                 {
    //                                     strMessage += 'Das Feld' + fieldName + ' muss ausgefüllt werden!';
    //                                 }
    //                                 else if( validateMode !== undefined && validateMode !== "undefined" && validateMode !== null )
    //                                 {
    //                                     arrValidate = this.validateField( validateMode, varValue, answerItem, fieldName, hasError, strMessage );
    //
    //                                     hasError        = arrValidate[0];
    //                                     strMessage      = arrValidate[1];
    //                                 }
    //                             }
    //                             else if( questMinAns === 0 && varValue.length )
    //                             {
    //                                 arrValidate = this.validateField( validateMode, varValue, answerItem, fieldName, hasError, strMessage );
    //
    //                                 hasError        = arrValidate[0];
    //                                 strMessage      = arrValidate[1];
    //                             }
    //                         }
    //                     }
    //                 }
    //
    //                 if( questMinAns > 0 && checkedItems.length < questMinAns && !hasChecked )
    //                 {
    //                     var muss        = ((questMinAns === 1) ? 'muss' : 'müssen'),
    //                         antwort     = ((questMinAns === 1) ? 'ne Antwort' : ' Antworten');
    //
    //                     message.innerHTML = 'Es ' + muss + ' mindestens ' + questMinAns + antwort  + ' ausgewählt werden.';
    //
    //                     if( animateImage )
    //                     {
    //                         $(animateImage).effect("shake");
    //                     }
    //
    //                     questionItem.classList.add("error");
    //                 }
    //                 else if( questMaxAns > 0 && checkedItems.length > questMaxAns && !hasChecked )
    //                 {
    //                     if( questMaxAns > 1 )
    //                     {
    //                         var darf        = ((questMaxAns === 1) ? 'darf' : 'dürfen'),
    //                             antwortMax  = ((questMaxAns === 1) ? 'ne Antwort' : ' Antworten');
    //
    //
    //
    //
    //                         message.innerHTML = 'Es ' + darf + ' maximal nur ' + questMaxAns + antwortMax  + ' ausgewählt werden.';
    //
    //                         if( animateImage )
    //                         {
    //                             $(animateImage).effect("shake");
    //                         }
    //
    //                         questionItem.classList.add("error");
    //                     }
    //                 }
    //                 else
    //                 {
    //                     if( !hasChecked )
    //                     {
    //                         if( questionItems.length > 1 )
    //                         {
    //                             message.innerHTML = "";
    //                             // hasError = hasError;
    //                             questionItem.classList.remove("error");
    //                         }
    //                         else
    //                         {
    //                             message.innerHTML = '';
    //                             hasError = false;
    //
    //                             questionItem.classList.remove("error");
    //                         }
    //                     }
    //                     else
    //                     {
    //                         message.innerHTML = strMessage;
    //
    //                         if( !hasError )
    //                         {
    //                             hasError = !!strMessage.length;
    //                         }
    //
    //                         if( animateImage && hasError)
    //                         {
    //                             $(animateImage).effect("shake");
    //                         }
    //
    //                         if( hasError )
    //                         {
    //                             questionItem.classList.add("error");
    //                         }
    //                         else
    //                         {
    //                             questionItem.classList.remove("error");
    //                         }
    //
    //                     }
    //                 }
    //             }
    //
    //             if( !hasError)
    //             {
    //                 questionItem.classList.remove("error");
    //             }
    //
    //         }
    //     }
    //
    //     return !hasError;
    // };



    questionnaire.validateField = function( validateMode, varValue, answerItem, fieldName )
    {
        var questionItem    = answerItem.parentNode.parentNode.parentNode.parentNode,
            errorMessage    = answerItem.getAttribute("data-error-message");
        
        if( errorMessage === undefined || errorMessage === "undefined" || errorMessage === null )
        {
            errorMessage = false;
        }

        if( validateMode === "digit" )
        {
            var valRangeFrom    = parseInt(answerItem.getAttribute("data-range-from")),
                valRangeTo      = parseInt(answerItem.getAttribute("data-range-to"));

            var checkDigit = false;

            if( isNaN( varValue ) )
            {
                var intMsgText = 'Das Feld' + fieldName + ' muss eine Zahl sein!';

                if( $lang === 'en' )
                {
                    intMsgText = 'The field' + fieldName + ' must be a number!'
                }

                this.addMessageToQuestion(questionItem, intMsgText);

                return false;
            }

            varValue = parseInt(varValue);

            if( valRangeFrom > 0 && valRangeFrom > varValue)
            {
                if( valRangeTo > 0 && valRangeTo < varValue )
                {
                    var rangeFromToMsgText = 'Das Feld' + fieldName + ' muss zwischen ' + valRangeFrom + ' und ' + valRangeTo + ' sein!';

                    if( $lang === 'en' )
                    {
                        rangeFromToMsgText = 'The field' + fieldName + ' must be between ' + valRangeFrom + ' and ' + valRangeTo + '!';
                    }

                    this.addMessageToQuestion(questionItem, rangeFromToMsgText);

                    return false;
                }

                var rangeToMsgText = 'Das Feld' + fieldName + ' muss größer sein als ' + valRangeFrom + '!';

                if( $lang === 'en' )
                {
                    rangeToMsgText = 'The field' + fieldName + ' must be greater than ' + valRangeFrom + '!';
                }

                this.addMessageToQuestion(questionItem, rangeToMsgText);

                return false;
            }
            else if( valRangeTo > 0 && valRangeTo < varValue )
            {
                var rangeFromMsgText = 'Das Feld' + fieldName + ' muss kleiner sein als ' + valRangeTo + '!';

                if( $lang === 'en' )
                {
                    rangeFromMsgText = 'The field' + fieldName + ' must be less than ' + valRangeTo + '!';
                }

                if( errorMessage )
                {
                    rangeFromMsgText = errorMessage;
                }

                this.addMessageToQuestion(questionItem, rangeFromMsgText);

                return false;
            }
        }

        return true;
    };



    questionnaire.checkPageAnimation = function()
    {
        var pageContainer = $questionnaire.querySelector(".page-item.page-" + $activePage);

        if( pageContainer )
        {
            var posAttr = pageContainer.getAttribute("data-animation-position");

            if( posAttr )
            {
                var arrPosition = posAttr.split(","),
                    newPosition = {};

                if( arrPosition.length )
                {
                    for(var i=0; i<arrPosition.length;i++)
                    {
                        var arrPosParts = arrPosition[ i ].split(':');

                        newPosition[ arrPosParts[ 0 ] ] = arrPosParts[ 1 ];
                    }

                    if( $activePage === $maxPages )
                    {
                        newPosition['width'] = 120;
                        newPosition['transform'] = 'rotate(18deg)';
                    }

                    var animateImage = $questionnaire.querySelector(".image.is-animated");

                    if( animateImage )
                    {
                        $(animateImage).animate(newPosition, 500);
                    }
                }
            }
        }
    };



    questionnaire.getOverviewAnswerItem = function(configContainer, answerItem)
    {
        var dataAlias   = configContainer.getAttribute("data-question-alias"),
            ovQuestion  = $questionnaire.querySelector('.question-overview-item[data-alias="' + dataAlias + '"]');

        if( ovQuestion )
        {
            var answerAlias = answerItem.getAttribute("data-alias"),
                ovAnswer    = ovQuestion.querySelector('.overview-answer-item[data-alias="' + answerAlias + '"]');

            if( ovAnswer )
            {
                return ovAnswer;
            }
        }

        return false;
    };



    questionnaire.updateOverviewPageAnswers = function( pageNum )
    {
        if( pageNum === undefined || pageNum === "undefined" || pageNum === null )
        {
            pageNum = false;
        }

        if( pageNum )
        {
            var pageContainer = $questionnaire.querySelector(".page-item.page-" + pageNum);
        }
        else
        {
            var pageContainer = $questionnaire.querySelector(".page-item.page-" + ($activePage - 1));
        }

        if( pageContainer )
        {
            var questions = pageContainer.querySelectorAll(".question-item");

            if( questions.length )
            {
                for(var questNum=0; questNum<questions.length;questNum++)
                {
                    var question    = questions[ questNum ],
                        answersCont = question.querySelector(".answers-container"),
                        answers     = question.querySelectorAll(".answer-item");

                    for(var answerNum=0; answerNum<answers.length; answerNum++)
                    {
                        var answer      = answers[ answerNum ],
                            ovAnswer    = this.getOverviewAnswerItem(answersCont, answer),
                            ovText      = true;

                        if( ovAnswer )
                        {
                            var ovAnswerTitle   = ovAnswer.parentNode.parentNode.querySelector(".overview-title"),
                                ovAnswerText    = ovAnswerTitle.innerHTML,
                                ovAnswerTextLabel = '';

                            if( answer.classList.contains("is-textarea") )
                            {
                                ovAnswerTextLabel = answer.querySelector("textarea").value;
                            }
                            else
                            {
                                if( !answer.classList.contains('not-clickable') )
                                {
                                    ovAnswerTextLabel = answer.querySelector("input").value;
                                }
                            }

                            if( ovAnswerText === "##answer##" || ovAnswerText === ovAnswerTextLabel )
                            {
                                ovText = false;
                                ovAnswerTitle.innerHTML = ovAnswerTextLabel;

                                ovAnswerTitle.classList.add("normal-text");
                            }
                        }

                        if( ovAnswer && answer.classList.contains("input-answer") && ovText)
                        {
                            if( answer.classList.contains("is-textarea") )
                            {
                                ovAnswer.querySelector(".input-container").innerHTML = answer.querySelector("textarea").value;
                            }
                            else if( answer.classList.contains("select-input-answer") )
                            {
                                ovAnswer.querySelector(".input-container").innerHTML = answer.querySelector(".select-option.is-active input").value;
                            }
                            else
                            {
                                var inputCont       = ovAnswer.querySelector(".input-container"),
                                    strInputValue   = answer.querySelector("input").value,
                                    contAddon       = inputCont.getAttribute("data-addon");

                                if( contAddon !== "undefned" && contAddon !== undefined && contAddon !== null && strInputValue)
                                {
                                    strInputValue = strInputValue + ' ' + contAddon;
                                }

                                inputCont.innerHTML = strInputValue;
                            }
                        }
                    }
                }
            }
        }
    };



    questionnaire.initImageMap = function()
    {
        var imageMaps       = $questionnaire.querySelectorAll(".has-image-map"),
            imagePointHover = document.getElementById("mhit");

        if( imageMaps.length )
        {
            for(var i=0; i<imageMaps.length; i++)
            {
                var imageMap        = imageMaps[ i ],
                    imagePoints     = imageMap.querySelectorAll(".answer-item.image-map-point");

                if( imagePoints.length )
                {
                    for(var num=0; num<imagePoints.length; num++)
                    {
                        var imagePoint = imagePoints[ num ];

                        imagePoint.addEventListener("mouseenter", function()
                        {
                            imagePointHover.innerHTML = this.querySelector(".answer-inside").innerHTML;
                        });

                        imagePoint.addEventListener("mouseleave", function()
                        {
                            imagePointHover.innerHTML = '';
                        });
                    }
                }
            }
        }
    };



    questionnaire.changeOverviewItem = function( questionAlias )
    {
    };



    questionnaire.goToNextPage = function()
    {
        var nextButton = document.querySelector(".page-item.is-active button.next-page");

        nextButton.click();
    };
    
    
    
    questionnaire.initSaveFormData = function( saveID, questionnaireID )
    {
        var cookie = Cookies.get("iido_shopQuestionnaire_" + questionnaireID);

        if( cookie === undefined || cookie === "undefined" || cookie === null )
        {
            Cookies.set("iido_shopQuestionnaire_" + questionnaireID, saveID);
        }
    };



    questionnaire.submitORIGO = function()
    {
        $handleCalculate = true;
        document.getElementById("fakeLoader").style.display = "block";

        return true;
    };



    questionnaire.goToPage = function( pageIndex )
    {
        pageIndex = (pageIndex + 1);

        $activePage = pageIndex;

        $questionnaire.querySelector(".page-item.overview-page").classList.remove("is-active");
        $questionnaire.querySelector(".page-item.page-" + pageIndex).classList.add("is-active");
    };



    questionnaire.checkIfSetData = function()
    {
        if( $activePage > 0 )
        {
            if( !$handleCalculate )
            {
                return true;
            }
        }

        return false;
    }


})(window, jQuery, IIDO.Shop.Questionnaire);