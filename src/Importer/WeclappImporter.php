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

namespace IIDO\ShopBundle\Importer;


use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\API\WeclappApi;

use IIDO\ShopBundle\Model\IidoShopProductModel as ProductModel;


class WeclappImporter extends DefaultImporter
{
    /**
     * Importer Page Steps
     *
     * @var int
     */
    protected $pageSteps = 500;


    /**
     * Importer Filter Fields
     *
     * @var array
     */
    protected $filterFields = array
    (
    );


    /**
     * Importer Search Fields
     *
     * @var array
     */
    protected $searchFields = array
    (
        'name'              => 'Name',
        'articleNumber'     => 'Artikelnummer'
    );


    /**
     * Importer Sorting Fields
     *
     * @var array
     */
    protected $sortFields = array
    (
        'name'              => 'Name',
        'articleNumber'     => 'Artikelnummer'
    );



    /**
     * @param \BackendTemplate $objTemplate
     *
     * @return \BackendTemplate
     */
    public function renderTemplate($objTemplate)
    {
        $objTemplate->filterFields  = $this->filterFields;
        $objTemplate->searchFields  = $this->searchFields;
        $objTemplate->sortFields    = $this->sortFields;

        $objTemplate->pageSteps    = $this->pageSteps;

        $arrInternProducts          = array();
        $objProducts                = ProductModel::findByArchive( \Input::get("id") );

        $objClass       = new WeclappApi();
        $activePage     = 1;
        $sortValue      = key( $this->sortFields );
        $filterUrl      = '';

        $objTemplate    = $this->manageImporter( $objTemplate );


        if( \Input::post("FORM_SUBMIT") === "tl_filters" )
        {
            if( \Input::post("tl_limit") )
            {
                $activePage = \Input::post("tl_limit");
            }

            if( \Input::post("tl_sort") )
            {
                $sortValue = \Input::post("tl_sort");
            }

            if( \Input::post("tl_value") )
            {
                $filterValue    = \Input::post("tl_value");
                $filterUrl      = '&' . \Input::post("tl_field") . '-ilike=' . preg_replace('/\*/', '%25', $filterValue);
            }
        }

        $arrProducts = $objClass->runApiUrl('article/?pageSize=' . $this->pageSteps . '&page=' . $activePage . '&sort=' . $sortValue . $filterUrl );

        $objTemplate->products      = $arrProducts;
        $objTemplate->productsCount = $objClass->runApiUrl('article/count' . preg_replace('/\&/', '?', $filterUrl));
        $objTemplate->activePage    = $activePage;

        if( !is_array($arrProducts) )
        {
            $objTemplate->products      = array();
            $objTemplate->error         = $arrProducts;
        }

        if( $objProducts )
        {
            while( $objProducts->next() )
            {
                $arrInternProducts[] = $objProducts->current();
            }
        }

        $objTemplate->internProducts = $arrInternProducts;

        return parent::renderTemplate($objTemplate);
    }



    protected function manageImporter( $objTemplate )
    {
        if( \Input::post("FORM_SUBMIT") === 'tl_shop_products_import' && \Input::post("IMPORT_MODE") === 'weclapp' )
        {
            $objClass       = new WeclappApi();
            $arrProducts    = \Input::post("products");
            $count          = 0;

            foreach($arrProducts as $productID)
            {
                $objOnlineProduct   = $objClass->runApiUrl("article/id/" . $productID );

                if( $objOnlineProduct && is_array($objOnlineProduct) && count($objOnlineProduct) )
                {
                    $objOnlineProduct   = (object) $objOnlineProduct;
                    $objProduct         = new ProductModel();

                    $objProduct->name       = $objOnlineProduct->name;
                    $objProduct->itemNumber = $objOnlineProduct->articleNumber;
                    $objProduct->tstamp     = time();
                    $objProduct->pid        = \Input::get("id");
                    $objProduct->published  = TRUE;

                    $objProduct->imported   = TRUE;
                    $objProduct->importDate = time();
                    $objProduct->importUser = \BackendUser::getInstance()->id;

                    $objProduct = $objProduct->save();

                    $strAlias = \StringUtil::generateAlias( $objProduct->name );
                    $objAlias = \Database::getInstance()
                        ->prepare("SELECT id FROM tl_iido_shop_product WHERE alias=? AND id!=?")
                        ->execute($strAlias, $objProduct->id);

                    // Check whether the product alias exists
                    if ($objAlias->numRows)
                    {
                        $strAlias .= '-' . $objProduct->id;
                    }

                    $objProduct->alias      = $strAlias;
                    $objProduct->save();

                    $count++;
                }
            }

            $objTemplate->message = 'Es wurden ' . $count . ' Produkte erfolgreich importiert.';
        }

        return $objTemplate;
    }
}