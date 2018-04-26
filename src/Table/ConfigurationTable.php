<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Table;



class ConfigurationTable
{
    protected $strTable = 'tl_iido_shop_configuration';


    public static function getTable()
    {
        $_self = new self();
        return $_self->strTable;
    }



    public function loadDataContainer($dc)
    {
        if( \Input::get("p") === "ai" )
        {
            $GLOBALS['TL_DCA'][ self::getTable() ]['palettes']['default'] = $GLOBALS['TL_DCA'][ self::getTable() ]['palettes']['ai'];
        }
    }

}