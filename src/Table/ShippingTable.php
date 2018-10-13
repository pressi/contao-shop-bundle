<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Table;


use IIDO\ShopBundle\Helper\ApiHelper;
use IIDO\ShopBundle\Helper\ShippingHelper;
use IIDO\ShopBundle\Model\IidoShopShippingModel;


class ShippingTable extends \Backend
{
    protected $strTable = 'tl_iido_shop_shipping';



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



    public function getShippings()
    {
        $arrOptions     = array();
        $objShippings   = $this->Database->prepare("SELECT * FROM " . $this->strTable)->execute(); //IidoShopShippingModel::findAll();

        if( $objShippings && $objShippings->count() )
        {
            while( $objShippings->next() )
            {
                $frontendTitle = '';

                if( $objShippings->frontendTitle )
                {
                    $strFeTitle = $objShippings->frontendTitle;

                    $strFeTitle = preg_replace('/\{\{iflng::de\}\}/', '', $strFeTitle);
                    $strFeTitle = preg_replace('/\{\{iflng::en\}\}([A-Za-z0-9\s\-öäüÖÄÜß\{\},;.:_\+\(\)]{0,})/', '', $strFeTitle);

                    $frontendTitle = ' <span class="grey">[' . $strFeTitle . ']</span>';
                }

                $arrOptions[ $objShippings->id ] = $objShippings->name . ' (' . $objShippings->alias . ') ' . $frontendTitle;
            }
        }

        return $arrOptions;
    }



    public function getApiMethods()
    {
        $arrMethods = array();

        $objApi         = ApiHelper::getApiObject();
        $arrApiMethods  = $objApi->runApiUrl("/shipmentMethod");

        if( $arrApiMethods && is_array($arrApiMethods) && count($arrApiMethods) )
        {
            $arrMethods[] = '-';

            foreach($arrApiMethods as $arrMethod)
            {
                $arrMethods[ $arrMethod['id'] ] = $arrMethod['name'];
            }
        }

        return $arrMethods;
    }



    public function getApiShippingArticles()
    {
        $arrMethods = array();

        $objApi         = ApiHelper::getApiObject();
        $arrApiMethods  = $objApi->runApiUrl("/article/?articleNumber-like=L%25");

        if( $arrApiMethods && is_array($arrApiMethods) && count($arrApiMethods) )
        {
            $arrMethods[] = '-';

            foreach($arrApiMethods as $arrMethod)
            {
                $arrMethods[ $arrMethod['id'] ] = $arrMethod['name'];
            }
        }

        return $arrMethods;
    }



    public function getShippingCountries()
    {
        \Controller::loadLanguageFile("countries");

        $arrCountries = $GLOBALS['TL_LANG']['CNT'];

        $arrCountries['eu']         = 'EU (Europäische Union)';
        $arrCountries['world']      = 'Rest der Welt';
//        $arrCountries['eu_n_at']    = 'EU (Europäische Union) exkl. Österreich';
//        $arrCountries['eu_n_at,de'] = 'EU (Europäische Union) exkl. Österreich & Deutschland';

//        $arrCountries['eu_ch']          = 'EU (Europäische Union) inkl. Schweiz';
//        $arrCountries['eu_ch_n_at']     = 'EU (Europäische Union) inkl. Schweiz, exkl. Österreich';
//        $arrCountries['eu_ch_n_at,de']  = 'EU (Europäische Union) inkl. Schweiz, exkl. Österreich & Deutschland';

        return $arrCountries;
    }



    /**
     * Add an image to each record
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param array          $args
     *
     * @return array
     */
    public function renderLabel($row, $label, \DataContainer $dc, $args)
    {
        if( !$row['price'] || $row['price'] === 0 )
        {
            if( $row['enablePricePerCountry'] )
            {
                $args[2] = 'Länderspezifisch';
            }
            else
            {
                $args[2] = 'kostenlos';
            }
        }
        else
        {
            $args[2] = number_format($args[2], 2, ',', '.');
            $args[2] = preg_replace('/,00$/', ',-', $args[2]);
        }

        return $args;
    }



    /**
     * Check the permission
     */
    public function checkCountryOptionsPermission()
    {
//        $this->import('BackendUser', 'User');

        if (!$this->User->isAdmin && !$this->User->hasAccess('manage', 'iidoShopShippingCountryOptions'))
        {
            unset($GLOBALS['TL_DCA'][ $this->strTable ]['list']['global_operations']['countryOptions']);
        }
    }


}