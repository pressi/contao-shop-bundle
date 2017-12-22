<?php
/*******************************************************************
 *
 * (c) 2017 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use Http\Message\Cookie;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\API\WeclappApi;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;

use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class ShopCartElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_cart';




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

            $objTemplate->wildcard  = '### SHOP: WARENKORB ###';
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

        $this->Template->editLabel          = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['edit'];
        $this->Template->toWatchlistLabel   = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['toWatchlist'];
        $this->Template->removeLabel        = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['remove'];

        $strLangSize            = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['size'];
        $strLangFlex            = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['flex'];
        $strLangBinding         = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['binding'];
        $strLangTuning          = $GLOBALS['TL_LANG']['iido_shop_cart']['label']['tuning'];

        $arrCartList = ShopConfig::getCartList();

        $this->Template->count = count($arrCartList);

        if( count($arrCartList) )
        {
            $arrProducts = array();

            //TODO: check if api is active // else use contao product details not form api!!
            foreach($arrCartList as $item)
            {
                $itemNumber     = $item['itemNumber'];

                $sizeNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$3', $itemNumber);
                $flexNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$4', $itemNumber);
                $bindingNumber  = preg_replace('/([A-Za-z0-9.]{1,}).B([0-9]{1,})$/', 'B$2', $itemNumber);
                $tuningNumber   = $item['tuning'];

                $objApi         = ApiHelper::getApiObject();
                $objProduct     = $objApi->runApiUrl('article/?articleNumber-eq=' . $itemNumber);
                $objBinding     = $objApi->runApiUrl('article/?articleNumber-eq=' . $bindingNumber);
                $objTuning      = false; // $objApi->runApiUrl('article/?articleNumber-eq=' . $tuningNumber );
                $arrCategories  = $this->getProductCategories( $itemNumber );
                $strClass       = '';

                $objShopProduct = $this->findShopProduct( $itemNumber );

                $intPrice       = $objProduct['articlePrices'][0]['price'];
                $imageTag       = '';
                $detailInfos    = '';

                if( $objShopProduct )
                {
                    $imageTag   = ImageHelper::getImageTag( $objShopProduct->overviewSRC );
                }

                $strLabel = 'ORIGINAL+';

                if( preg_match('/^C/', $itemNumber) || preg_match('/^S/', $itemNumber) )
                {
                    $strLabel       = 'ORIGINAL+ SKI';
                    $detailInfos    = $strLangSize . ': ' . $sizeNumber;

                    if( count($objBinding) )
                    {
                        $detailInfos .= ' / ' . $strLangBinding . ': ' . $objBinding['name'];
                    }

                    $detailInfos .= ' / ' . $strLangFlex . ': ' . $objApi->getFlexName( $flexNumber );

                    if( $objTuning )
                    {
                        $detailInfos .= ' / ' . $strLangTuning . ': ' . $objTuning['name'];
                    }

                    $strClass .= ' product-item-ski';
                }

                $arrProducts[] = array
                (
                    'name'          => $item['name'],
                    'itemNumber'    => $itemNumber,
                    'quantity'      => $item['quantity'],

                    'label'         => $strLabel,
                    'detailInfos'   => $detailInfos,
                    'price'         => $intPrice,
                    'imageTag'      => $imageTag,

                    'categories'    => $arrCategories,
                    'class'         => trim($strClass)
                );
            }

            $this->Template->items = $arrProducts;
        }
        else
        {
            $this->Template->empty = 'Derzeit sind keine Produkte in deinem Warenkorb.';
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