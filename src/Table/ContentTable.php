<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Table;


use IIDO\ShopBundle\Model\IidoShopArchiveModel;


class ContentTable extends \Backend
{

    protected $strTable = 'tl_content';



    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    public function checkShopApi( \DataContainer $dc )
    {
        $tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
        $arrOptions         = $GLOBALS['TL_LANG']['tl_content']['options'][ $dc->field ];

        foreach( $arrOptions as $key => $value)
        {
            if( $key === "archive" )
            {
                continue;
            }

            if( !key_exists($key, $GLOBALS['IIDO']['SHOP']['API']) )
            {
                unset( $arrOptions[ $key ] );
                continue;
            }

            if( !\Config::get( $tableFieldPrefix . 'enable' . ucfirst($key) . 'Api') )
            {
                unset( $arrOptions[ $key ] );
            }
        }

        return $arrOptions;
    }



    /**
     * Get all product archives and return them as array
     *
     * @return array
     */
    public function getProductArchives()
    {
        if (!$this->User->isAdmin && !\is_array($this->User->iidoShopArchives))
        {
            return array();
        }

        $arrArchives = array();
        $objArchives = $this->Database->execute("SELECT id, title FROM " . IidoShopArchiveModel::getTable() ." ORDER BY title");

        while ($objArchives->next())
        {
            if ($this->User->hasAccess($objArchives->id, 'iidoShopArchives'))
            {
                $arrArchives[$objArchives->id] = $objArchives->title;
            }
        }

        return $arrArchives;
    }
}