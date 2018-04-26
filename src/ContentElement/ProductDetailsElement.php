<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use HeimrichHannot\Ajax\Ajax;
use IIDO\ShopBundle\Ajax\ShopAjax;


class ProductDetailsElement extends \ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_productDetails';



    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### PRODUKT DETAILS ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }
        else
        {
            Ajax::runActiveAction('iidoShop', 'getAddToCartMessage', new ShopAjax($this));
            Ajax::runActiveAction('iidoShop', 'getAddToWatchlistMessage', new ShopAjax($this));
        }

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        \Controller::loadLanguageFile( "iido_shop_configurator" );

        $apiName        = ApiHelper::enableApis( true );
        $productUrlPath = ApiHelper::getUrlPath();
        $objProduct     = false;
        $strMode        = \Input::get("mode");
        $strSubMode     = 'cart';

        if($strMode === "edit-list")
        {
            $strMode    = 'edit';
            $strSubMode = 'watchlist';
        }

        $arrItemNumber  = array();
        $arrValue       = array
        (
            'design'    => '',
            'size'      => ''
        );

        $arrProductIdOrAliasOrItemNumber    = explode("-", (\Config::get("useAutoItem") ? \Input::get("auto_item") : \Input::get( $productUrlPath )));
        $productIdOrAliasOrItemNumber       = array_pop($arrProductIdOrAliasOrItemNumber);

        if( $apiName )
        {
            $objApi         = ApiHelper::getApiObject( $apiName );
//            $productUrlPath = $objApi->getUrlPath();
            $objProduct     = $objApi->getProduct( $productIdOrAliasOrItemNumber );

            $arrItemNumber  = explode(".", $objProduct->itemNumber);

            $arrValue['design'] = '<div class="color_circle cc-' . $objApi->getColorCode( $arrItemNumber[1] ) . '"></div>';
        }
        else
        {
            $objProduct = IidoShopProductModel::findByIdOrAlias( $productIdOrAliasOrItemNumber );

            if( !$objProduct )
            {
                $objProduct = IidoShopProductModel::findByItemNumber( $productIdOrAliasOrItemNumber );
            }

            $arrItemNumber  = explode(".", $objProduct->itemNumber);
        }

        $strCartLink        = '';
        $strWatchlistLink   = '';
        $objCategory        = false;

        if( $this->iidoShopCart )
        {
            $strCartLink = \PageModel::findByPk( $this->iidoShopCart )->getFrontendUrl();
        }

        if( $this->iidoShopWatchlist )
        {
            $strWatchlistLink = \PageModel::findByPk( $this->iidoShopWatchlist )->getFrontendUrl();
        }

        if( $objProduct )
        {
            $arrCategories = \StringUtil::deserialize($objProduct->categories, TRUE);

            foreach($arrCategories as $categoryID)
            {
                $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

                if( $objCategory )
                {
                    break;
                }
            }
        }

        $this->Template->product    = $objProduct;
        $this->Template->label      = $GLOBALS['TL_LANG']['iido_shop_configurator']['label'];

        $this->Template->priceUnit      = '€'; //ShopConfig; TODO: get from shop config!! TODO: add the config module!!
        $this->Template->cartNum        = ShopConfig::getCartNum();
        $this->Template->cartLink       = $strCartLink;
        $this->Template->watchlistNum   = ShopConfig::getWatchlistNum();
        $this->Template->watchlistLink  = $strWatchlistLink;

        $this->Template->catColor       = ($objCategory ? ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) ) : '');
        $this->Template->chooserValue   = $arrValue;
        $this->Template->arrItemNumber  = $arrItemNumber;

        $this->Template->mode           = $strMode;
        $this->Template->subMode        = $strSubMode;
    }
}