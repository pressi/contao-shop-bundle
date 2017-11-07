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


list( $namespace, $subNamespace, $subName, $prefix, $tablePrefix, $listenerName ) = \IIDO\ShopBundle\Config\BundleConfig::getBundleConfigArray();

$assetsPath = \IIDO\ShopBundle\Config\BundleConfig::getBundlePath(true, false);
$ns         = $namespace . '\\' . $subNamespace;

/**
 * Backend modules
 */

array_insert($GLOBALS['BE_MOD'], 3, array
(

    $prefix . 'Shop' => array
   (
        $prefix . 'Products' => array
        (
            'callback'      => $ns . '\Backend\Module\ProductModule',
            'tables'        => array($tablePrefix . 'product', $tablePrefix . 'category', 'tl_content'),
//            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
        ),

        $prefix . 'API' => array
        (
//            'callback'      => $ns . '\Backend\Module\APIModul',
            'tables'        => array($tablePrefix . 'api'),
//            'stylesheet'    => $assetsPath . 'css/backend/contao-placeholder.css'
        ),

//        $prefix . 'ConfigContao' => array
//        (
//            'callback'      => $ns . '\BackendModule\ConfigClientModule',
//            'stylesheet'    => $assetsPath . 'css/backend/config-contao.css'
//        )
   )

));