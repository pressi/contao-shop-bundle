<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Table;


use IIDO\ShopBundle\Helper\PaymentHelper;
use IIDO\ShopBundle\Model\IidoShopPaymentModel;


class PaymentTable extends \Backend
{
    protected $strTable = 'tl_iido_shop_payment';



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



    public function getPaymentTypes( \DataContainer $dc )
    {
        $arrTypes = PaymentHelper::getAllTypes();

        if( \Input::get("act") === "edit" )
        {
            $objPayments = $this->Database->prepare('SELECT * FROM ' . $this->strTable)->execute();

            if( $objPayments && $objPayments->count() )
            {
                while( $objPayments->next() )
                {
                    if( in_array($objPayments->type, array_keys($arrTypes)) )
                    {
                        if( $dc->activeRecord->type !== $objPayments->type || !$dc->activeRecord->type )
                        {
                            unset( $arrTypes[ $objPayments->type ] );
                        }
                    }
                }
            }
        }

        return $arrTypes;
    }



    public function getPaymentName( $varValue, \DataContainer $dc)
    {
        if( !$varValue )
        {
            if( !$dc->activeRecord )
            {
                $arrTypes   = PaymentHelper::getAllTypes();
                $strType    = key( $arrTypes );
            }
            else
            {
                $strType = $dc->activeRecord->type;

                if( !$strType )
                {
                    $arrTypes   = PaymentHelper::getAllTypes();
                    $strType    = key( $arrTypes );
                }
            }

            $varValue = PaymentHelper::get($strType, "name");
        }

        return $varValue;
    }



    public function getPaymentAlias( $varValue, \DataContainer $dc)
    {
        if( !$varValue )
        {
            if( !$dc->activeRecord )
            {
                $arrTypes   = PaymentHelper::getAllTypes();
                $strType    = key( $arrTypes );
            }
            else
            {
                $strType = $dc->activeRecord->type;

                if( !$strType )
                {
                    $arrTypes   = PaymentHelper::getAllTypes();
                    $strType    = key( $arrTypes );
                }
            }

            $varValue = $strType;
        }

        return $varValue;
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
        $args[0]    = PaymentHelper::getAllTypes()[ $row['type'] ];
        $args[1]    = ($row['alias']?:$row['type']);

//        if( $row['name'] )
//        {
//            $label = $label . ' - <span class="label-name">' . $row['name'] . ']</span>';
//        }

//        if( $row['alias'] || $row['type'] )
//        {
//            $label = $label . ' <span class="label-addon" style="color:#cfcfcf;">[' . ($row['alias']?:$row['type']) . ']</span>';
//        }

        return $args;
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



    public function loadPaymentTable()
    {
        $arrAllTypes        = PaymentHelper::getAllTypes();
        $objPayments        = $this->Database->prepare("SELECT * FROM " . $this->strTable)->execute();

        if( $objPayments && $objPayments->count() )
        {
            while( $objPayments->next() )
            {
                if( isset($arrAllTypes[ $objPayments->type ]) )
                {
                    unset( $arrAllTypes[ $objPayments->type ] );
                }
            }
        }

        if( !count($arrAllTypes) )
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['closed'] = TRUE;
        }
    }



    public function getPayments()
    {
        $arrOptions     = array();
        $arrPayments    = PaymentHelper::getAllTypes();
        $objPayments    = $this->Database->prepare("SELECT * FROM " . $this->strTable)->execute(); //IidoShopShippingModel::findAll();

        if( $objPayments && $objPayments->count() )
        {
            while( $objPayments->next() )
            {
                $frontendTitle = '';

                if( $objPayments->name )
                {
                    $frontendTitle = ' <span class="grey">[' . $objPayments->name . ']</span>';
                }

                $arrOptions[ $objPayments->id ] = $arrPayments[ $objPayments->type ] . ' (' . $objPayments->type . ') ' . $frontendTitle;
            }
        }

        return $arrOptions;
    }


}