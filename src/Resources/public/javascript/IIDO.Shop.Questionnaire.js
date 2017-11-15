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
    var $questionnaire, $activePage, $hasOverview = false, $maxPages = 0;

    questionnaire.init = function( questionnaireID )
    {
        $activePage     = 0;
        $questionnaire  = document.getElementById("shopQuestionnaire_" + questionnaireID);

        if( $questionnaire.classList.contains("has-overview-page") )
        {
            $hasOverview = true;
        }

        $maxPages = parseInt($questionnaire.getAttribute("data-max-pages"));

        this.initProgressSteps();
    };



    questionnaire.nextPage = function( nextButton )
    {
        if( this.checkAnswerStatus(nextButton) )
        {
            $activePage = ($activePage + 1);

            this.checkPageStatus();
            this.checkPageAnimation();
            this.updateOverviewPageAnswers();
        }
    };



    questionnaire.checkPageStatus = function()
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

        pageItem.classList.remove("is-active");

        if( nextPage.classList.contains("page-" + $activePage) )
        {
            nextPage.classList.add("is-active");
        }
        else
        {
            $questionnaire.querySelector("page-item.page-" + $activePage).classList.add("is-active");
        }

        // this.checkProgressBar();
        this.checkProgressSteps();
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



    questionnaire.toggleAnswer = function( answerContainer )
    {
        var answerItem      = answerContainer.parentNode.parentNode,
            configContainer = answerItem.parentNode.parentNode,
            inputTag        = answerContainer.querySelector("input"),

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
        }
        else
        {
            answerItem.classList.add("is-checked");
            inputTag.checked = true;

            if( $hasOverview && ovAnswerItem )
            {
                ovAnswerItem.classList.add("is-checked");
            }

            if( maxAnswers > 1)
            {
                if( !this.checkAnswerStatus(answerContainer, minAnswers, maxAnswers) )
                {
                    answerItem.classList.remove("is-checked");
                    inputTag.checked = false;

                    if( $hasOverview && ovAnswerItem )
                    {
                        ovAnswerItem.classList.remove("is-checked");
                    }
                }
            }

            if( maxAnswers === 1 )
            {
                $(answerItem).siblings().removeClass("is-checked");
                $(answerItem).siblings().find("input").checked = false;
            }

        }
    };



    questionnaire.checkAnswerStatus = function( buttonTag, minAnswers, maxAnswers )
    {
        var hasError = true;

        if( $activePage === 0 )
        {
            return true;
        }
        else
        {
            var pageContainer;

            if( buttonTag.nodeName === "BUTTON" )
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
                    var questionItem    = questionItems[ i ],
                        answersCont     = questionItem.querySelector(".answers-container"),
                        answersItems    = answersCont.querySelectorAll(".answer-item"),
                        checkedItems    = answersCont.querySelectorAll(".answer-item.is-checked"),
                        message         = questionItem.querySelector(".error-msg");

                    if( minAnswers === undefined || minAnswers === "undefined" || minAnswers === null )
                    {
                        minAnswers = parseInt(answersCont.getAttribute("data-min-answers"));
                    }

                    if( maxAnswers === undefined || maxAnswers === "undefined" || maxAnswers === null )
                    {
                        maxAnswers = parseInt(answersCont.getAttribute("data-max-answers"));
                    }

                    var hasChecked = false,strMessage = '',
                        animateImage = $questionnaire.querySelector(".image.is-animated");

                    if( checkedItems.length === 0 )
                    {
                        for(var num=0; num<answersItems.length;num++)
                        {
                            var answerItem = answersItems[ num ];

                            if( answerItem.classList.contains("input-answer") && minAnswers > 0 )
                            {
                                hasChecked = true;

                                var varValue = answerItem.querySelector("input").value;

                                if( !varValue.length )
                                {
                                    strMessage += 'Das Feld muss ausgefüllt werden!';
                                }
                            }
                        }
                    }

                    if( minAnswers > 0 && checkedItems.length < minAnswers && !hasChecked )
                    {
                        var muss        = ((minAnswers === 1) ? 'muss' : 'müssen'),
                            antwort     = ((minAnswers === 1) ? 'ne Antwort' : ' Antworten');

                        message.innerHTML = 'Es ' + muss + ' mindestens ' + minAnswers + antwort  + ' ausgewählt werden.';

                        if( animateImage )
                        {
                            $(animateImage).effect("shake");
                        }
                    }
                    else if( maxAnswers > 0 && checkedItems.length > maxAnswers && !hasChecked )
                    {
                        if( maxAnswers > 1 )
                        {
                            var darf        = ((maxAnswers === 1) ? 'darf' : 'dürfen'),
                                antwortMax  = ((maxAnswers === 1) ? 'ne Antwort' : ' Antworten');

                            message.innerHTML = 'Es ' + darf + ' maximal nur ' + maxAnswers + antwortMax  + ' ausgewählt werden.';

                            if( animateImage )
                            {
                                $(animateImage).effect("shake");
                            }
                        }
                    }
                    else
                    {
                        if( !hasChecked )
                        {
                            message.innerHTML = '';
                            hasError = false;
                        }
                        else
                        {
                            message.innerHTML = strMessage;
                            hasError = !!strMessage.length;

                            if( animateImage && hasError)
                            {
                                $(animateImage).effect("shake");
                            }
                        }
                    }
                }
            }
        }

        return !hasError;
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



    questionnaire.updateOverviewPageAnswers = function()
    {
        var pageContainer = $questionnaire.querySelector(".page-item.page-" + ($activePage - 1));

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
                            ovAnswer    = this.getOverviewAnswerItem(answersCont, answer);

                        if( ovAnswer && answer.classList.contains("input-answer") )
                        {
                            ovAnswer.querySelector(".input-container").innerHTML = answer.querySelector("input").value;
                        }
                    }
                }
            }
        }
    };


})(window, jQuery, IIDO.Shop.Questionnaire);