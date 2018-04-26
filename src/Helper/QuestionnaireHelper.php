<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\API\DefaultApi;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class QuestionnaireHelper
{

    public static function renderEmailText( $objClass )
    {
        $strContent = '';

        foreach( $objClass->pages as $arrPage )
        {
            echo "<pre>";
            print_r( $arrPage );
            exit;
        }

        return $strContent;
    }

}