<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable           = 'tl_iido_shop_product_categories';


/**
 * Shop Product Categories Table
 */
$GLOBALS['TL_DCA'][ $strTable ] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'sql' => array
        (
            'keys' => array
            (
                'category_id' => 'index',
                'product_id' => 'index'
            )
        )
    ),

    // Fields
    'fields' => array
    (
        'category_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'product_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        )
    )
);
