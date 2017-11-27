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


class DefaultImporter
{

    public function __construct() { }



    public function renderTemplate( $objTemplate )
    {
        $objFilterTemplate = new \BackendTemplate( "be_product_import_filter" );

        $objFilterTemplate->productsCount   = $objTemplate->productsCount;

        $objFilterTemplate->sortFields      = $objTemplate->sortFields;
        $objFilterTemplate->searchFields    = $objTemplate->searchFields;

        $objFilterTemplate->pageSteps       = $objTemplate->pageSteps;
        $objFilterTemplate->activePage      = $objTemplate->activePage;


        $objTemplate->filterContent = $objFilterTemplate->parse();

        return $objTemplate;
    }
}