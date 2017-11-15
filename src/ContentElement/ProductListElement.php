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


use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;


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

            $objTemplate->wildcard = '### PRODUKT LISTE ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    /**
     * Generate the content element
     */
    protected function compile()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### PRODUKT LISTE ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = '';

            return $objTemplate->parse();
        }

        $arrProducts    = array();
        $tablePrefix    = BundleConfig::getTablePrefix();

        $isApiEnabled   = ApiHelper::isApiEnabled( true );

        if( $isApiEnabled )
        {
            $apiName    = 'IIDO\\ShopBundle\\API\\' . ucfirst($isApiEnabled) . 'Api';
            $api        = new $apiName();

            $arrProducts = $api->getProductList();
        }

//        $objProducts    = new Collection( $arrProducts, $tablePrefix . 'products');

        $this->Template->products = $arrProducts; //$objProducts;
    }
}