<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use HeimrichHannot\Ajax\Ajax;
use HeimrichHannot\Ajax\AjaxAction;
use IIDO\ShopBundle\Ajax\ShopAjax;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ShopHelper;


class ShopCartElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_cart';




    /**
     * Initialize the object
     *
     * @param \ContentModel $objElement
     * @param string       $strColumn
     */
    public function __construct($objElement, $strColumn='main')
    {
        parent::__construct($objElement, $strColumn);

        $strTableFieldPrefix = BundleConfig::getTableFieldPrefix();

        if( !\Config::get($strTableFieldPrefix . "enableShopLight") )
        {
            Ajax::runActiveAction('iidoShop', 'getPrice', new ShopAjax($this));
            Ajax::runActiveAction('iidoShop', 'renderPrice', new ShopAjax($this));
        }
    }



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

        $strLang = $GLOBALS['TL_LANG']['iido_shop_cart'];

        $this->Template->editLabel          = $strLang['label']['edit'];
        $this->Template->toWatchlistLabel   = $strLang['label']['toWatchlist'];
        $this->Template->removeLabel        = $strLang['label']['remove'];

        $arrCartList    = ShopConfig::getCartList();
        $intCartPrice   = 0;

        $this->Template->count = count($arrCartList);

        if( count($arrCartList) )
        {
            $arrProducts = array();
//echo "<pre>"; print_r( $arrCartList ); exit;
            foreach($arrCartList as $item)
            {

                $arrShopProduct = ShopHelper::getProduct( $item, $strLang, $this );
                $intPrice       = ($arrShopProduct['intPrice'] * $item['quantity']);

                $arrProducts[] = $arrShopProduct;

                $intCartPrice = ($intCartPrice + $intPrice);
            }

            $this->Template->items  = $arrProducts;
            $this->Template->empty  = $strLang['cartEmpty'];
        }
        else
        {
            $this->Template->empty  = $strLang['cartEmpty'];
        }

        $arrLinks       = array();
        $arrShopLinks   = \StringUtil::deserialize($this->iidoShopCartLinks, TRUE);
        $checkOutLink   = '';

        if( count($arrShopLinks) )
        {
            foreach($arrShopLinks as $arrLink)
            {
                if( $arrLink['link'] )
                {
                    $arrLinks[] = array
                    (
                        'link'      => $arrLink['link'],
                        'text'      => $arrLink['text'],
                        'tag'       => '<a href="' . $arrLink['link'] . '">' . $arrLink['text'] . '</a>'
                    );
                }
            }
        }

        $checkOutClass = 'check-out-link-tag';

        if( $this->iidoShopCartCheckOutPage )
        {
            if( !count($arrCartList) )
            {
                $checkOutClass .= ' hidden';
            }

            $objCheckOutPage    = \PageModel::findByPk( $this->iidoShopCartCheckOutPage );

            $checkOutHref       = $objCheckOutPage->getFrontendUrl();
            $checkOutLink       = '<span class="' . $checkOutClass . '" id="checkoutLink"><a href="' . $checkOutHref . '">' . ($this->iidoShopCartCheckOutText?:$objCheckOutPage->title) . '</a></span>';
        }

        $this->Template->links          = $arrLinks;

        $this->Template->priceText      = $this->iidoShopCartPriceText;
        $this->Template->priceUnit      = '&euro;';
        $this->Template->price          = ShopHelper::renderPrice($intCartPrice, true);
        $this->Template->checkOutLink   = $checkOutLink;

        $this->Template->editLink       = \PageModel::findByPk( $this->iidoShopEditPage )->getFrontendUrl();
    }
}