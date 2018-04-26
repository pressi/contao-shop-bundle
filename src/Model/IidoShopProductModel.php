<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Model;


/**
 *
 *
 */
class IidoShopProductModel extends \Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_iido_shop_product';



    /**
     * Find products by archive id
     *
     * @param integer $intPid     The archive ID
     * @param array   $arrOptions An optional options array
     *
     * @return IidoShopProductModel|null The model or null if there is no product
     */
    public static function findByArchive($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrValues  = array();
        $arrColumns = array();

        if ($intPid)
        {
            $arrColumns[] = "$t.pid=?";
            $arrValues[] = $intPid;
        }

        return static::findBy($arrColumns, $arrValues, $arrOptions);
    }



    /**
     * Find product by item number
     *
     * @param       $itemNumber
     * @param array $arrOptions
     *
     * @return IidoShopProductModel|null
     */
    public static function findByItemNumber($itemNumber, array $arrOptions=array())
    {
        $t = static::$strTable;

        $arrValues  = array();
        $arrColumns = array();

        if( $itemNumber )
        {
            $arrColumns[] = "$t.itemNumber=?";
            $arrValues[] = $itemNumber;
        }

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }
}
