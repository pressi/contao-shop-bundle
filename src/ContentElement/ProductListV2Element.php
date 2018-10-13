<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;


class ProductListV2Element extends \ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_productList';



    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            $arrFields      = $GLOBALS['TL_LANG']['tl_content'];
            $addToWildCard  = '';

            if( $this->loadProductsFrom )
            {
                $addToWildCard = '<br>Produkte Anzeigen von ' . $arrFields['options']['loadProductsFrom'][ $this->loadProductsFrom ];

                if( $this->loadProductsFrom !== "archive" && $this->iidoShopShowProductsFrom )
                {
                    $addToWildCard .= '<br>Produkte Laden via ' . $arrFields['options']['iidoShopShowProductsFrom'][ $this->iidoShopShowProductsFrom ];

                    $loadFrom = '';

                    if( $this->iidoShopShowProductsFrom === "categories" )
                    {
                        $loadFrom = ' (';
                        $arrCategories = \StringUtil::deserialize($this->iidoShopCategories, TRUE);

                        foreach($arrCategories as $key => $category)
                        {
                            $objCategory = IidoShopProductCategoryModel::findByPk( $category );

                            $loadFrom .= (($key > 1) ? ',' : '') . $objCategory->title;
                        }
                        $loadFrom .= ')';
                    }
                    else
                    {
                        $loadFrom = '<br>Artikelnummer: ' . $this->iidoShopProductItemNumber;
                    }

                    $addToWildCard .= $loadFrom;
                }

                if( $this->iidoShopProductsShowMode )
                {
                    $addToWildCard .= '<br>Anzeige-Modus: ' . $arrFields['options']['iidoShopProductsShowMode'][ $this->iidoShopProductsShowMode ];
                }

                $addToWildCard .= '<br><br>';
            }

            $filter = '';

            if( $this->iidoShopProductsAddFilter )
            {
                $filter = ' + FILTER';
            }

            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### PRODUKT LISTE' . $filter . ' ###' . $addToWildCard;
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        $this->iidoShopCategories = \StringUtil::deserialize($this->iidoShopCategories, TRUE);

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        $fromApi        = false;
        $arrProducts    = array();
//        $tablePrefix    = BundleConfig::getTablePrefix();

        if( $this->loadProductsFrom === "archive" )
        {
        }
        else
        {
            $apiName = $this->loadProductsFrom;

            if( ApiHelper::isApiEnabled( $apiName ) )
            {
                $fromApi    = true;
                $api        = ApiHelper::getApiObject( $apiName );
                /* @var $api \IIDO\ShopBundle\API\WeclappApi */

                $itemNumbers    = '';
                $strMessageText = '';

                switch( $this->iidoShopShowProductsFrom )
                {
                    case 'itemNumber':
                        $itemNumbers    = $this->iidoShopProductItemNumber;
                        $strMessageText = '<strong>bei dem Inhaltselement</strong> ';
                        break;

                    case 'categories':
                        $itemNumbers    = $this->getItemNumbersFromCategories( $this->iidoShopCategories );
                        $strMessageText = '<strong>bei der ausgewählten Kategorie</strong> ';
                        break;
                }

                if( !strlen($itemNumbers) )
                {
                    $this->Template->error      = TRUE;
                    $this->Template->message    = 'Es sind keine Artikelnummern ausgewählt. Bitte hinterlegen Sie ' . $strMessageText . 'die Artikelnummern.';
                }
                else
                {
//                    $arrProducts = $api->getProductList( $itemNumbers, $this->iidoShopDetailPage, false, $this->iidoShopProductsShowMode );
                    $arrProducts = $api->getProductListV2( $itemNumbers, $this->iidoShopDetailPage, false, $this->iidoShopProductsShowMode );
                }
            }
        }
        
        $this->Template->formApi    = $fromApi;
        $this->Template->products   = $arrProducts; //$objProducts;
    }



    protected function getItemNumbersFromCategories( array $arrCategories )
    {
        $itemNumbers = '';

        if( is_array($arrCategories) && count($arrCategories) )
        {
            foreach($arrCategories as $categoryID)
            {
                $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

                if( $objCategory )
                {
                    if( $objCategory->itemNumbers )
                    {
                        $itemNumbers .= (strlen($itemNumbers) ? ',' : '') . $objCategory->itemNumbers;
                    }
                }
            }
        }

        return $itemNumbers;
    }
}