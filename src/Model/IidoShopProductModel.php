<?php


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
     * Fin products by archive id
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
}
