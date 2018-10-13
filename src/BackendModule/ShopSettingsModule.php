<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\BackendModule;


/**
 * Backend Module: Contao Init
 *
 * @author Stephan Preßl <development@prestep.at>
 * @deprecated NO MORE IN USE!!
 */
class ShopSettingsModule extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate  = 'be_iido_shop_settings';



    /**
     * Generate the module
     */
    protected function compile()
    {
        \Controller::loadLanguageFile("iido_shop_backend");

        $overview   = true;
        $strMode    = \Input::get("mode");
        $strTable   = \Input::get("table");

        if( $strMode )
        {
            if( $strMode === "payment" || $strMode === "shipping" )
            {
//                $strTableClass = '\IIDO\ShopBundle\Model\IidoShop' . ucfirst( $strMode ) . 'Model';
                $strTableClass = \Model::getClassFromTable( $strTable );

                $this->Template->content = $this->renderTableMode( $strTableClass::getTable() );
            }
            else
            {
                $modeName = 'render' . ucfirst( \Input::get("mode") ) . 'Mode';
                $this->Template->content = $this->$modeName();
            }

            $overview = false;
        }

        $strLang    = $GLOBALS['TL_LANG']['iido_shop_backend']['settings'];

        $this->Template->label      = $strLang;
        $this->Template->overview   = $overview;
        $this->Template->User       = \BackendUser::getInstance();
    }



    protected function renderTableMode( $strTable, $mode = 'table' )
    {
        $tableMode      = '\DC_' . ucfirst($mode);

        $objTemplate    = new \BackendTemplate( $this->strTemplate . '_table' );
        $objTable       = new $tableMode( $strTable );

        $tableFuncName  = (($mode === 'file') ? 'edit' : 'showAll');
        $action         = \Input::get("act");

        if( $action )
        {
            if( $action !== "select" )
            {
                $tableFuncName = $action;
            }
        }

        $objTemplate->content = $objTable->$tableFuncName();

        return $objTemplate->parse();
    }



    protected function renderConfigurationMode()
    {
        return $this->renderTableMode( \Input::get("table"), 'file' );
    }



    protected function renderAiMode()
    {
        return $this->renderTableMode( \Input::get("table"), 'file' );
    }



    protected function renderVoucherMode()
    {
        return $this->renderTableMode( \Input::get("table") );
    }

}
