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


class ProductListElement extends \ContentElement
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
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### PRODUKT LISTE ###';
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

//                echo "<pre>";
//                print_r( $this->iidoShopProductItemNumber );
//                echo "<br>";
//                print_r( $this->iidoShopDetailPage );
//                exit;
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
                    $arrProducts = $api->getProductList( $itemNumbers, $this->iidoShopDetailPage );
                }
            }
        }

//        $objProducts    = new Collection( $arrProducts, $tablePrefix . 'products');

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