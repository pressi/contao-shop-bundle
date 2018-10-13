<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;



class ShopFilesHelper
{

    protected static $configFile    = 'shop-config-';
    protected static $productFile   = 'shop-products-';

    protected static $configFileConfigurator    = 'shop-configurator-config-';
    protected static $productFileConfigurator   = 'shop-configurator-products-';

    protected static $type = '.json';

    protected static $folder = 'assets/shop_tmp/';


    /**
     * read config file
     *
     * @param int $itemNumber
     *
     * @return boolean|float|string
     * @throws \Exception
     */
    public static function readConfigFile( $itemNumber )
    {
        $fileName   = self::$folder . self::$configFile . $itemNumber . self::$type;
        $objFile    = new \File( $fileName );

        if( $objFile )
        {
            return json_decode($objFile->getContent(), TRUE);
        }

        return false;
    }

}