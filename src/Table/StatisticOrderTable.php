<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Table;


use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\ShippingHelper;
use IIDO\ShopBundle\Model\IidoShopShippingModel;


class StatisticOrderTable
{
    protected $strTable = 'tl_iido_shop_statistic_order';



    public function renderApiInfoField()
    {
        return '';
    }
}