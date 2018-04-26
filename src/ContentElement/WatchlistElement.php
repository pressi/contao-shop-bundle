<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class WatchlistElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_watchlist';




    /**
     * Generate configurator element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### SHOP: MERKLISTE ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }



    /**
     * Compile the content element
     */
    protected function compile()
    {
        \Controller::loadLanguageFile("iido_shop_cart");

        $strLang = $GLOBALS['TL_LANG']['iido_shop_cart'];

        $this->Template->editLabel          = $strLang['label']['edit'];
        $this->Template->buyLabel           = $strLang['label']['buy'];
        $this->Template->removeLabel        = $strLang['label']['remove'];

        $strLangSize            = $strLang['label']['size'];
        $strLangFlex            = $strLang['label']['flex'];
        $strLangBinding         = $strLang['label']['binding'];
        $strLangTuning          = $strLang['label']['tuning'];

        $arrWatchlistList       = ShopConfig::getWatchlistList();

        $this->Template->count  = count($arrWatchlistList);

        if( count($arrWatchlistList) )
        {
            $arrProducts    = array();

            foreach($arrWatchlistList as $item)
            {
                $arrShopProduct = ShopHelper::getProduct( $item, $strLang, $this );

                $arrProducts[] = $arrShopProduct;
            }

            $this->Template->items      = $arrProducts;
            $this->Template->empty      = $strLang['watchlistEmpty'];
        }
        else
        {
            $this->Template->empty = $strLang['watchlistEmpty'];
        }
    }



    protected function findShopProduct( $itemNumber )
    {
        $productTable   = IidoShopProductModel::getTable();

        $skiNumber      = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9.]{1,})/', 'S$1', $itemNumber);
        $colorNumber    = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$2', $itemNumber);

        $objProducts = \Database::getInstance()->prepare("SELECT * FROM " . $productTable ." WHERE itemNumber LIKE ?")->execute($skiNumber . '.' . $colorNumber . '%');

        if( $objProducts && $objProducts->count() )
        {
            while( $objProduct = $objProducts->next() )
            {
                if( $objProduct->overviewSRC )
                {
                    return $objProduct;
                }
            }
        }

        return null;
    }



    protected function getProductCategories( $itemNumber )
    {
        $objProduct     = IidoShopProductModel::findBy("itemNumber", $itemNumber);
        $arrCategories  = \StringUtil::deserialize($objProduct->categories, TRUE);

        if( !count($arrCategories) )
        {
            $skiNumber      = preg_replace('/C.S([A-Za-z0-9]{1,}).([A-Za-z0-9\.]{1,})/', 'C.S$1', $itemNumber);
            $productTable   = IidoShopProductModel::getTable();
            $objProducts    = \Database::getInstance()->prepare("SELECT * FROM " . $productTable ." WHERE itemNumber LIKE ?")->execute($skiNumber . '%');

            if( $objProducts )
            {
                while( $objProducts->next() )
                {
                    $arrProductCategories = \StringUtil::deserialize($objProducts->categories, TRUE);

                    if( count($arrProductCategories) )
                    {
                        $arrCategories = $arrProductCategories;
                        break;
                    }
                }
            }
        }

        if( count($arrCategories) )
        {
            foreach($arrCategories as $num => $categoryID)
            {
                $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

                if( $objCategory )
                {
                    $arrCategories[ $num ] = $objCategory;
                }
            }
        }

        return $arrCategories;
    }
}