<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\BackendModule;


use IIDO\ShopBundle\Helper\QuestionnaireHelper;


/**
 * Backend Module: Shop Statistic
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class ShopStatisticModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_iido_shop_statistic';



    /**
     * Generate the module
     */
    protected function compile()
    {
        \Controller::loadLanguageFile("iido_shop_backend");

        $arrLangLabel = $GLOBALS['TL_LANG']['iido_shop_backend']['statistic'];

        $overview   = true;
        $strMode    = \Input::get("mode");

        $this->Template->label = $arrLangLabel;

        if( $strMode )
        {
            $modeName = 'render' . ucfirst( \Input::get("mode") ) . 'Mode';
            $this->Template->content = $this->$modeName();

            $overview = false;

            $this->Template->removeButtons = TRUE;
        }

        $this->Template->overview   = $overview;
        $this->Template->User       = \BackendUser::getInstance();
    }



    protected function renderAiMode()
    {
        $objTemplate = new \BackendTemplate( $this->strTemplate . '_ai' );

        $questionnaires     = array();
        $arrQuestionnaires  = array();

        $strTable           = \ContentModel::getTable();
        $objQuestionnaires  = \ContentModel::findBy(array($strTable . '.type=?'), array('rsce_shop_questonaire'));

        if( $objQuestionnaires )
        {
            while( $objQuestionnaires->next() )
            {
                $questionnaires[ $objQuestionnaires->id ] = $objQuestionnaires->current();

                $arrData = json_decode($objQuestionnaires->rsce_data, TRUE);

                $objArticle = \ArticleModel::findByPk( $objQuestionnaires->pid );
                $pageId     = $objArticle->pid;

                $objQuestPage   = \PageModel::findWithDetails( $pageId );
                $pageTitle      = $objQuestPage->pageTitle?:$objQuestPage->title;

                $objRootPage    = \PageModel::findByPk( $objQuestPage->rootId );

                $arrQuestionnaires[ $objRootPage->title . ' (ID: ' . $objRootPage->id . ')' ][] = array
                (
                    'id'            => $objQuestionnaires->id,
                    'title'         => $arrData['title'],

                    'article'       => $objArticle,
                    'articleTitle'  => $objArticle->title,

                    'page'          => $objQuestPage,
                    'pageTitle'     => $pageTitle,

                    'hasData'       => QuestionnaireHelper::checkIfQuestionnaireHasData( $objQuestionnaires->id )
                );
            }
        }


        $objTemplate->label = $this->Template->label;
        $objTemplate->questionnaires = $arrQuestionnaires;

        $arrQuestData = array();

        if( \Input::get("questionnaire") )
        {
            $overview = false;
            $objResult = QuestionnaireHelper::getQuestionnaireResult( \Input::get("questionnaire") );

            if( $objResult && $objResult->count() > 0 )
            {
                $objQuest       = $questionnaires[ $objQuestionnaires->id ];
                $arrQuestConfig = json_decode($objQuest->rsce_data, TRUE);

                while( $objResult->next() )
                {
                    $arrQuestData[] = $objResult->row();
                }
            }
        }
        else
        {
            $overview = true;
        }

        $objTemplate->questionnaireData     = $arrQuestData;
        $objTemplate->addButtons            = TRUE;
        $objTemplate->overview              = $overview;

        return $objTemplate->parse();
    }



    protected function renderOrderMode()
    {
        $objTemplate = new \BackendTemplate( $this->strTemplate . '_order' );

        $objTemplate->addButtons = TRUE;

        return $objTemplate->parse();
    }

}
