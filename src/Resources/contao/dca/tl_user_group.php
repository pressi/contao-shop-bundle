<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Extend the default palette
 */
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('iido_shopArchive_legend', 'amg_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('iidoShopArchives', 'iidoShopArchivep'), 'iido_shopArchive_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user_group')
;


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopArchives'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopArchives'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_iido_shop_archive.title',
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['iidoShopArchivep'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iidoShopArchivep'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => array('create', 'delete'),
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => array('multiple'=>true),
	'sql'                     => "blob NULL"
);