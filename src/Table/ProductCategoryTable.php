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

namespace IIDO\ShopBundle\Table;


use Contao\CoreBundle\Monolog\ContaoContext;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use Psr\Log\LogLevel;


/**
 * Class ProductTable
 *
 * @package IIDO\ShopBundle\Table
 */
class ProductCategoryTable extends \Backend
{
    /**
     * Shop Product Category Table name
     *
     * @var string
     */
    protected $strTable = 'tl_iido_shop_product_category';



    /**
     * Shop Product Category Table name
     *
     * @var string
     */
    protected $strCategoriesTable = 'tl_iido_shop_product_categories';


    /**
     * Shop Product Table name
     *
     * @var string
     */
    protected $strProductTable = 'tl_iido_shop_product';


    /**
     * Shop Product Archive Table
     *
     * @var string
     */
    protected $strArchiveTable = 'tl_iido_shop_archive';



    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }



    public static function getTable()
    {
        $_self = new self();
        return $_self->strTable;
    }



    /**
     * Auto-generate the product alias if it has not been set yet
     *
     * @param mixed          $varValue
     * @param \DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue = \StringUtil::generateAlias($dc->activeRecord->title);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE alias=? AND id!=?")
            ->execute($varValue, $dc->id);

        // Check whether the product alias exists
        if ($objAlias->numRows)
        {
            if (!$autoAlias)
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }



    /**
     * Check the permission
     */
    public function checkPermission()
    {
        if (!$this->User->isAdmin && !$this->User->hasAccess('manage', 'iidoShopProductCategories'))
        {
            $this->redirect('contao/main.php?act=error');
        }
    }



    /**
     * Return the paste category button
     * @param \DataContainer
     * @param array
     * @param string
     * @param boolean
     * @param array
     * @return string
     */
    public function pasteCategory(\DataContainer $dc, $row, $table, $cr, $arrClipboard=null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
        {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter    = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto     = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

        if ($row['id'] > 0)
        {
            $return = $disablePA ? \Image::getHtml('pasteafter_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
        }

        return $return.($disablePI ? \Image::getHtml('pasteinto_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
    }



    /**
     * Add the correct indentation
     * @param array
     * @param string
     * @param object
     * @param string
     * @return string
     */
    public function generateLabel($arrRow, $strLabel, $objDca, $strAttributes)
    {
        return \Image::getHtml('iconPLAIN.gif', '', $strAttributes) . ' ' . $strLabel;
    }



    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ';
    }



    /**
     * Publish/unpublish a category
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        $objVersions = new \Versions($this->strTable, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();

        $level = TL_ERROR === TL_GENERAL ? LogLevel::ERROR : LogLevel::INFO;
        $logger = static::getContainer()->get('monolog.logger.contao');

        $logger->log($level, 'A new version of record "' . $this->strTable . '.id='.$intId.'" has been created'.$this->getParentEntries($this->strTable, $intId), array('contao' => new ContaoContext(__METHOD__, TL_GENERAL)));
    }



    /**
     * Set the allowed categories
     *
     * @param DataContainer
     */
    public function setAllowedCategories($dc = null)
    {
        if (!$dc->id)
        {
            return;
        }

        $objArchive = $this->Database->prepare("SELECT categories FROM " . $this->strArchiveTable . " WHERE limitCategories=1 AND id=(SELECT pid FROM " . $this->strProductTable . " WHERE id=?)")
            ->limit(1)
            ->execute($dc->id);

        if (!$objArchive->numRows)
        {
            return;
        }

        $arrCategories = deserialize($objArchive->categories, true);

        if (empty($arrCategories))
        {
            return;
        }

        $GLOBALS['TL_DCA'][ $this->strProductTable ]['fields']['categories']['rootNodes'] = $arrCategories;
    }



    /**
     * Update the category relations
     *
     * @param \DataContainer
     */
    public function updateCategories(\DataContainer $dc)
    {
        $this->import('BackendUser', 'User');
        $arrCategories = deserialize($dc->activeRecord->categories);

        // Use the default categories if the user is not allowed to edit the field directly
        if (!$this->User->isAdmin && !in_array($this->strProductTable . '::iidoShopProductCategories', $this->User->alexf)) {

            // Return if the record is not new
            if ($dc->activeRecord->tstamp)
            {
                return;
            }

            $arrCategories = $this->User->iidoShopProductCategories_default;
        }

        $this->deleteCategories($dc);

        if (is_array($arrCategories) && !empty($arrCategories))
        {
            foreach ($arrCategories as $intCategory)
            {
                $this->Database->prepare("INSERT INTO " . $this->strCategoriesTable . " (category_id, product_id) VALUES (?, ?)")
                    ->execute($intCategory, $dc->id);
            }

            $this->Database->prepare("UPDATE " . $this->strProductTable . " SET categories=? WHERE id=?")
                ->execute(serialize($arrCategories), $dc->id);
        }

        // add primary category
//        if ($dc->activeRecord->primaryCategory > 0)
//        {
//
//            // already added before
//            if (is_array($arrCategories) && !empty($arrCategories) && in_array($dc->activeRecord->primaryCategory, $arrCategories))
//            {
//                return;
//            }
//
//            $this->Database->prepare("INSERT INTO " . $this->strCategoriesTable . " (category_id, product_id) VALUES (?, ?)")
//                ->execute($dc->activeRecord->primaryCategory, $dc->id);
//        }
    }



    /**
     * Delete the category relations
     *
     * @param \DataContainer
     */
    public function deleteCategories(\DataContainer $dc)
    {
        $this->Database->prepare("DELETE FROM " . $this->strCategoriesTable . " WHERE product_id=?")
            ->execute($dc->id);
    }



    public function getShopCategories()
    {
        $objCategories  = IidoShopProductCategoryModel::findBy("pid", 0);
        $arrCategories  = array();

        if( $objCategories )
        {
            while( $objCategories->next() )
            {
                $arrSubCategories   = array();
                $objSubCategories   = IidoShopProductCategoryModel::findBy("pid", $objCategories->id);

                if( $objSubCategories )
                {
                    while( $objSubCategories->next() )
                    {
                        $arrSubCategories[ $objSubCategories->id ] = $objSubCategories->title;
                    }
                }

                if( count($arrSubCategories) )
                {
                    $arrCategories[ $objCategories->title ] = $arrSubCategories;
                }
            }
        }

        return $arrCategories;
    }
}