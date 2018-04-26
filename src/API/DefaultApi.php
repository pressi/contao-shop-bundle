<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\API;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\ShopBundle\Config\BundleConfig;


class DefaultApi
{
    /**
     * API Name
     *
     * @var string
     */
    protected $apiName;


    /**
     * Active Importer
     *
     * @var boolean
     */
    protected $activeImporter;


    /**
     * API Config
     *
     * @var array()
     */
    protected $arrConfig = array();


    /**
     * Product Url Path
     *
     * @var array
     */
    protected $productUrlPath = array
    (
        'de'    => 'produkt',
        'en'    => 'product'
    );



    /**
     * check if api is active
     *
     * @return mixed|null
     */
    public function isActive()
    {
        return \Config::get( BundleConfig::getTableFieldPrefix() . 'enable' . ucfirst( $this->apiName ) . 'Api' );
    }



    /**
     * Get Product Url Path
     *
     * @return string
     */
    public function getUrlPath()
    {
        return $this->productUrlPath[ BasicHelper::getLanguage() ];
    }



    /**
     * check if api importer is active
     *
     * @return bool
     */
    public function hasImporter()
    {
        return $this->activeImporter;
    }



    /**
     * gets api importer config
     *
     * @param bool $returnObject
     *
     * @return array|object
     */
    public function getImporter( $returnObject = false)
    {
        $strName = ucfirst( $this->apiName );

        $arrConfig = array
        (
            'name'      => $this->apiName,
            'label'     => $strName . '-Importer',
            'className' => $strName . 'Importer',
            'classPath' => '\\IIDO\ShopBundle\\Importer\\' . $strName . 'Importer'
        );

        return $returnObject ? (object) $arrConfig : $arrConfig;
    }
}