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
        this.initImageMap();
    };



    questionnaire.nextPage = function( nextButton )
    {
        if( this.checkAnswerStatus(nextButton) )
        {
            $activePage = ($activePage + 1);

            this.checkPageStatus( "next" );
            this.checkPageAnimation();
            this.updateOverviewPageAnswers();
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
            configContainer = answerItem.parentNode.parentNode;

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

                if( $hasOverview && ovAnswerItem )
                {
                    $(ovAnswerItem).siblings().removeClass("is-checked");
                    // $(ovAnswerItem).siblings().find("input").checked = false;
                }
            }

        }

        if( maxAnswers === 1 )
        {
            this.goToNextPage();
        }
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
            ovAnswerItem.querySelector(".input-container").innerHTML = inputTag.value;
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
                        message         = questionItem.querySelector(".error-msg"),

                        questMinAns     = parseInt(answersCont.getAttribute("data-min-answers")),
                        questMaxAns     = parseInt(answersCont.getAttribute("data-max-answers"));

                    if( minAnswers !== undefined && minAnswers !== "undefined" && minAnswers !== null && minAnswers )
                    {
                        questMinAns = minAnswers;
                    }

                    if( maxAnswers !== undefined && maxAnswers !== "undefined" && maxAnswers === null && maxAnswers )
                    {
                        questMaxAns = maxAnswers;
                    }

                    var hasChecked = false,strMessage = '',
                        animateImage = $questionnaire.querySelector(".image.is-animated");

                    if( checkedItems.length === 0 )
                    {
                        for(var num=0; num<answersItems.length;num++)
                        {
                            var answerItem = answersItems[ num ];

                            if( answerItem.classList.contains("input-answer") && questMinAns > 0 )
                            {
                                hasChecked = true;

                                var varValue = answerItem.querySelector("input").value;

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

                                if( !varValue.length )
                                {
                                    var fieldName = '';

                                    if( questionItems.length > 1 )
                                    {
                                        fieldName = ' "' + answerItem.querySelector("label").innerHTML + '"';
                                    }

                                    strMessage += 'Das Feld' + fieldName +' muss ausgefüllt werden!';
                                }
                            }
                        }
                    }

                    if( questMinAns > 0 && checkedItems.length < questMinAns && !hasChecked )
                    {
                        var muss        = ((questMinAns === 1) ? 'muss' : 'müssen'),
                            antwort     = ((questMinAns === 1) ? 'ne Antwort' : ' Antworten');

                        message.innerHTML = 'Es ' + muss + ' mindestens ' + questMinAns + antwort  + ' ausgewählt werden.';

                        if( animateImage )
                        {
                            $(animateImage).effect("shake");
                        }

                        questionItem.classList.add("error");
                    }
                    else if( questMaxAns > 0 && checkedItems.length > questMaxAns && !hasChecked )
                    {
                        if( questMaxAns > 1 )
                        {
                            var darf        = ((questMaxAns === 1) ? 'darf' : 'dürfen'),
                                antwortMax  = ((questMaxAns === 1) ? 'ne Antwort' : ' Antworten');

                            message.innerHTML = 'Es ' + darf + ' maximal nur ' + questMaxAns + antwortMax  + ' ausgewählt werden.';

                            if( animateImage )
                            {
                                $(animateImage).effect("shake");
                            }

                            questionItem.classList.add("error");
                        }
                    }
                    else
                    {
                        if( !hasChecked )
                        {
                            if( questionItems.length > 1 )
                            {
                                message.innerHTML = "";
                                // hasError = hasError;
                                questionItem.classList.remove("error");
                            }
                            else
                            {
                                message.innerHTML = '';
                                hasError = false;

                                questionItem.classList.remove("error");
                            }
                        }
                        else
                        {
                            message.innerHTML = strMessage;
                            hasError = !!strMessage.length;

                            if( animateImage && hasError)
                            {
                                $(animateImage).effect("shake");
                            }

                            if( hasError )
                            {
                                questionItem.classList.add("error");
                            }
                            else
                            {
                                questionItem.classList.remove("error");
                            }

                        }
                    }
                }

                if( !hasError)
                {
                    questionItem.classList.remove("error");
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
                                ovAnswerTextLabel = answer.querySelector("input").value;
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
                                ovAnswer.querySelector(".input-container").innerHTML = answer.querySelector("input").value;
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
    }


})(window, jQuery, IIDO.Shop.Questionnaire);