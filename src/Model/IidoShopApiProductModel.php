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
class IidoShopApiProductModel
{

    /**
     * Api name
     *
     * @var string
     */
    protected static $strApi = '';



    /**
     * Data
     * @var array
     */
    protected $arrData = array();



    /**
     * Load the relations and optionally process a data result array
     *
     * @param array $arrData An optional data array
     */
    public function __construct( array $arrData=array() )
    {
        $this->arrData = $arrData;
    }



    /**
     * Set an object property
     *
     * @param string $strKey   The property name
     * @param mixed  $varValue The property value
     */
    public function __set( $strKey, $varValue )
    {
        if ($this->$strKey === $varValue)
        {
            return;
        }

        $this->arrData[ $strKey ] = $varValue;
    }



    /**
     * Return an object property
     *
     * @param string $strKey The property key
     *
     * @return mixed|null The property value or null
     */
    public function __get($strKey)
    {
        if (isset($this->arrData[$strKey]))
        {
            return $this->arrData[$strKey];
        }

        return null;
    }
}
