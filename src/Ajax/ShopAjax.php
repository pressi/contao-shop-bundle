<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Ajax;

use HeimrichHannot\Ajax\Response\ResponseData;
use HeimrichHannot\Ajax\Response\ResponseSuccess;
use HeimrichHannot\Ajax\Response\ResponseError;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ShopHelper;


class ShopAjax
{

    /**
     * Current ShopCart ContentElement
     *
     * @var object
     */
    protected $ce;




    public function __construct( $objElement )
    {
        $this->ce   = $objElement;
    }



    /**
     * get product price
     *
     * @param      $itemNumber
     * @param      $productName
     * @param      $quantity
     *
     * @return ResponseError|ResponseSuccess
     */
    public function getPrice( $itemNumber, $productName, $quantity )
    {
        $objProduct     = ShopConfig::getProduct( ShopHelper::getRealItemNumber($itemNumber) );
        $intPrice       = (float) $objProduct->price;

        $strHtml        = array('price' => ($intPrice * (int) $quantity));

        $objResponse    = new ResponseSuccess();
        $objData        = new ResponseData( $strHtml );

        $objResponse->setResult( $objData );

        return $objResponse;
    }



    /**
     * get product price rendered
     *
     * @param      $price
     * @param      $useDecimals
     *
     * @return ResponseError|ResponseSuccess
     */
    public function renderPrice( $price, $useDecimals = false)
    {
//        $strHtml        = array('price' => ShopHelper::renderPrice($price, $useDecimals));
//
//        $objResponse    = new ResponseSuccess();
//        $objData        = new ResponseData( $strHtml );
//
//        $objResponse->setResult( $objData );
//
//        return $objResponse;

        return $this->renderReturn( ShopHelper::renderPrice($price, $useDecimals), 'price' );
    }



    public function getAddToCartMessage( $prodcutName, $fieldAddon = '' )
    {
        $tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
        $strText            = \Config::get( $tableFieldPrefix . 'addToCartText' . $fieldAddon );

        $strText            = preg_replace('/##name##/', $prodcutName, $strText);

        return $this->renderReturn( \Controller::replaceInsertTags($strText) );
    }



    public function getConfiguratorAddToCartMessage( $productName )
    {
        $strText = $this->getAddToCartMessage( $productName, 'Configurator' );

        if( !$strText )
        {
            $strText = $this->getAddToCartMessage( $productName );
        }

        return $strText;
    }



    public function getAddToWatchlistMessage( $prodcutName, $fieldAddon = '' )
    {
        $tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
        $strText            = \Config::get( $tableFieldPrefix . 'addToWatchlistText' . $fieldAddon );

        $strText            = preg_replace('/##name##/', $prodcutName, $strText);

        return $this->renderReturn( \Controller::replaceInsertTags($strText) );
    }



    public function getConfiguratorAddToWatchlistMessage( $productName )
    {
        $strText = $this->getAddToWatchlistMessage( $productName, 'Configurator' );

        if( !$strText )
        {
            $strText = $this->getAddToWatchlistMessage( $productName );
        }

        return $strText;
    }



    protected function renderReturn( $strHtml, $strKey = 'content' )
    {
        $arrHtml = array();

        $arrHtml[ $strKey ] = $strHtml;

        $objResponse    = new ResponseSuccess();
        $objData        = new ResponseData( $arrHtml );

        $objResponse->setResult( $objData );

        return $objResponse;
    }

}