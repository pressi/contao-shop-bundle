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


use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;


/**
 * Class ProductTable
 *
 * @package IIDO\ShopBundle\Table
 */
class ProductTable extends \Backend
{
    /**
     * Shop Product Table name
     *
     * @var string
     */
    protected $strTable = 'tl_iido_shop_product';



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
     * Check permissions to edit shop product table
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Check the theme import and export permissions (see #5835)
        switch (\Input::get('key'))
        {
            case 'import':
                if (!$this->User->hasAccess('import', 'iidoShopProducts'))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to import products.');
                }
                break;

            case 'export':
                if (!$this->User->hasAccess('export', 'iidoShopProducts'))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to export products.');
                }
                break;
        }

        // Set the root IDs
        if (!is_array($this->User->iidoShopArchives) || empty($this->User->iidoShopArchives))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iidoShopArchives;
        }

        $id = strlen(\Input::get('id')) ? \Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Input::get('pid')) || !in_array(\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create products in shop archive ID ' . \Input::get('pid') . '.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Input::get('pid'), $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' product ID ' . $id . ' to shop archive ID ' . \Input::get('pid') . '.');
                }
            // NO BREAK STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $this->Database->prepare("SELECT pid FROM " . $this->strTable . " WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid product ID ' . $id . '.');
                }

                if (!in_array($objArchive->pid, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' product ID ' . $id . ' of shop archive ID ' . $objArchive->pid . '.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access shop archive ID ' . $id . '.');
                }

                $objArchive = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1)
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid shop archive ID ' . $id . '.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
                $objSession = \System::getContainer()->get('session');

                $session = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $objSession->replace($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Input::get('act') . '".');
                }
                elseif (!in_array($id, $root))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access shop archive ID ' . $id . '.');
                }
                break;
        }
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
            $varValue = \StringUtil::generateAlias($dc->activeRecord->name);
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
     * Add the type of input field
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listProductArticles($arrRow)
    {
        $arrCategories  = array();
        $arrItemCats    = \StringUtil::deserialize($arrRow['categories'], TRUE);

        if( count($arrItemCats) )
        {
            foreach( $arrItemCats as $categoryID)
            {
                $objCategory = IidoShopProductCategoryModel::findByPk($categoryID);

                if( $objCategory )
                {
                    $arrCategories[] = $objCategory->title;
                }
            }
        }

        return '<div class="tl_content_left"><span class="product-name">' . $arrRow['name'] . '</span> <span class="product-item-number" style="color:#999;padding-left:3px">[' . $arrRow['itemNumber'] . ']</span><span class="product-categories" style="color:#999;padding-left:3px">' . (count($arrCategories) ? ' - ' : '') . implode(",", $arrCategories) . '</span></div>';
    }



    /**
     * Return the "feature/unfeature element" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('fid')))
        {
            $this->toggleFeatured(\Input::get('fid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the fid, so hacking attempts are logged
        if (!$this->User->hasAccess($this->strTable . '::featured', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

        if (!$row['featured'])
        {
            $icon = 'featured_.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="' . ($row['featured'] ? 1 : 0) . '"').'</a> ';
    }



    /**
     * Feature/unfeature a product
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param \DataContainer $dc
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleFeatured($intId, $blnVisible, \DataContainer $dc=null)
    {
        // Check permissions to edit
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'feature');
        $this->checkPermission();

        // Check permissions to feature
        if (!$this->User->hasAccess($this->strTable . '::featured', 'alexf'))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to feature/unfeature product ID ' . $intId . '.');
        }

        $objVersions = new \Versions($this->strTable, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['featured']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['fields']['featured']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=". time() .", featured='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
    }



    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess($this->strTable . '::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
    }



    /**
     * Disable/enable a user group
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param \DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc=null)
    {
        // Set the ID and action
        \Input::setGet('id', $intId);
        \Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess($this->strTable . '::published', 'alexf'))
        {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish product  ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows)
            {
                $dc->activeRecord = $objRow;
            }
        }

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
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA'][ $this->strTable ]['config']['onsubmit_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }



    /**
     * Return the "import products" link
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     */
    public function importProducts($href, $label, $title, $class, $attributes)
    {
        return $this->User->hasAccess('import', 'iidoShopProducts') ? '<a href="'.$this->addToUrl($href).'" class="'.$class.'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : '';
    }



    public function renderProductImporter()
    {
        /** @var \FileUpload $objUploader */
        $objUploader = new \FileUpload();

        $objTemplate = $this->manageUploader( $objUploader );

        // Return the form
        return \Message::generate() . $objTemplate->parse();
    }



    protected function manageUploader( $objUploader )
    {
        $beTemplate = 'be_product_import_overview';
        $importMode = \Input::get("importMode");

        if( strlen($importMode) )
        {
            $beTemplate = 'be_product_import_' . $importMode;
        }

        $objTemplate = new \BackendTemplate( $beTemplate );
        $objTemplate->objUploader   = $objUploader;
        $objTemplate->lang          = $GLOBALS['TL_LANG'][ $this->strTable ];

        if( !strlen($importMode) )
        {
            $objTemplate = $this->renderOverviewTemplate( $objTemplate );
        }
        else
        {
            if( $importMode !== "csv" )
            {
                $objImporter    = ApiConfig::getImporter( $importMode );
                $objTemplate    = $objImporter->renderTemplate( $objTemplate );
            }
        }

        return $objTemplate;
    }



    protected function renderOverviewTemplate( $objTemplate )
    {
        $arrImporter = array();

        foreach( $GLOBALS['IIDO']['SHOP']['API'] as $strApi )
        {
            if( ApiConfig::isActive( $strApi ) )
            {
                $apiClass   = ApiConfig::getClass( $strApi );
                $objApi     = new $apiClass();

                if( $objApi->hasImporter() )
                {
                    $arrImporter[ $strApi ] = $objApi->getImporter( true );
                }
            }
        }

        $objTemplate->importer = $arrImporter;

        return $objTemplate;
    }
}