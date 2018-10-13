<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

$strTable = UserGroupModel::getTable();

\System::loadLanguageFile( UserModel::getTable() );



/**
 * Extend the default palette
 */
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('iido_shopArchive_legend', 'amg_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('iidoShopArchives', 'iidoShopArchivep', 'iidoShopProducts', 'iidoShopProductCategories', 'iidoShopProductCategories_default', 'iidoShopSettings', 'iidoShopStatistic'), 'iido_shopArchive_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', $strTable);



/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopArchives'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopArchives'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_iido_shop_archive.title',
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA'][ $strTable ]['fields']['iidoShopArchivep'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopArchivep'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);


\IIDO\BasicBundle\Helper\DcaHelper::addField("iidoShopProducts", "checkbox", $strTable, array('multiple'=>true));


$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopProductCategories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopProductCategories'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => $GLOBALS['TL_LANG']['tl_user']['options']['iidoShopProductCategories'],
    'eval'                    => ['multiple' => true, 'tl_class' => 'clr'],
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopProductCategories_default'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopProductCategories_default'],
    'exclude'                 => true,
    'inputType'               => 'treePicker',
    'foreignKey'              => 'tl_iido_shop_product_category.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'foreignTable'=>'tl_iido_shop_product_category', 'titleField'=>'title', 'searchField'=>'title', 'managerHref'=>'do=iidoShopProducts&table=tl_iido_shop_product_category'),
    'sql'                     => "blob NULL"
);



$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopSettings'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopSettings'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => $GLOBALS['TL_LANG']['tl_user']['options']['iidoShopSettings'],
    'eval'                    => ['multiple' => true, 'tl_class' => 'clr'],
    'sql'                     => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopStatistic'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopStatistic'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => $GLOBALS['TL_LANG']['tl_user']['options']['iidoShopStatistic'],
    'eval'                    => ['multiple' => true, 'tl_class' => 'clr'],
    'sql'                     => "varchar(32) NOT NULL default ''"
);