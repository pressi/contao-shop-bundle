<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTableFileName   = \IIDO\ShopBundle\Config\BundleConfig::getFileTable( __FILE__ );
$tableFieldPrefix   = \IIDO\ShopBundle\Config\BundleConfig::getTableFieldPrefix();
$strTableClass      = \IIDO\BasicBundle\Config\BundleConfig::getTableClass( $strTableFileName );



\IIDO\BasicBundle\Helper\DcaHelper::createNewTable( $strTableFileName );



/**
 * Palettes
 */

$arrDefaultFields = array
(
    'questionnaire_legend' => array
    (
        'questionnaire'
    ),

    'user_legend' => array
    (
        'userID'
    ),

    'data_legend' => array
    (
        'questionnaireData'
    )
);

\IIDO\BasicBundle\Helper\DcaHelper::addPalette('default', $arrDefaultFields, $strTableFileName);



/**
 * Fields
 */

\IIDO\BasicBundle\Helper\DcaHelper::addTextField('questionnaire', $strTableFileName, array('rgxp'=>'digit'));
\IIDO\BasicBundle\Helper\DcaHelper::addTextField('userID', $strTableFileName);
\IIDO\BasicBundle\Helper\DcaHelper::addBlobField('questionnaireData', $strTableFileName);