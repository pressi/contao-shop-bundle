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



class ArchiveTable extends \Backend
{
    protected $strTable = 'tl_iido_shop_archive';



    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }



    /**
     * Check permissions to edit table
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->iidoShopArchives) || empty($this->User->iidoShopArchives))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->iidoShopArchives;
        }

        $GLOBALS['TL_DCA'][ $this->strTable ]['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$this->User->hasAccess('create', 'iidoShopArchivep'))
        {
            $GLOBALS['TL_DCA'][ $this->strTable ]['config']['closed'] = true;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = \System::getContainer()->get('session');

        // Check current action
        switch (\Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Input::get('id'), $root))
                {
                    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $objSessionBag */
                    $objSessionBag = $objSession->getBag('contao_backend');

                    $arrNew = $objSessionBag->get('new_records');

                    if (is_array($arrNew[ $this->strTable ]) && in_array(\Input::get('id'), $arrNew[ $this->strTable ]))
                    {
                        // Add the permissions on group level
                        if ($this->User->inherit != 'custom')
                        {
                            $objGroup = $this->Database->execute("SELECT id, iidoShopArchives, iidoShopArchivep FROM tl_user_group WHERE id IN(" . implode(',', array_map('intval', $this->User->groups)) . ")");

                            while ($objGroup->next())
                            {
                                $arrNewp = \StringUtil::deserialize($objGroup->iidoShopArchivep);

                                if (is_array($arrNewp) && in_array('create', $arrNewp))
                                {
                                    $arrNews = \StringUtil::deserialize($objGroup->iidoShopArchives, true);
                                    $arrNews[] = \Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user_group SET iidoShopArchives=? WHERE id=?")
                                        ->execute(serialize($arrNews), $objGroup->id);
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ($this->User->inherit != 'group')
                        {
                            $objUser = $this->Database->prepare("SELECT iidoShopArchives, iidoShopArchivep FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrNewp = \StringUtil::deserialize($objUser->iidoShopArchivep);

                            if (is_array($arrNewp) && in_array('create', $arrNewp))
                            {
                                $arrNews = \StringUtil::deserialize($objUser->iidoShopArchives, true);
                                $arrNews[] = \Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET iidoShopArchives=? WHERE id=?")
                                    ->execute(serialize($arrNews), $this->User->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[] = \Input::get('id');
                        $this->User->news = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Input::get('id'), $root) || (\Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'iidoShopArchivep')))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' IIDO Shop product archive ID ' . \Input::get('id') . '.');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();
                if (\Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'iidoShopArchivep'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (strlen(\Input::get('act')))
                {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Input::get('act') . ' IIDO Shop product archives.');
                }
                break;
        }
    }



    /**
     * Return the edit header button
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
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->canEditFieldsOf($this->strTable) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }



    /**
     * Return the copy archive button
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
    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('create', 'iidoShopArchivep') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }



    /**
     * Return the delete archive button
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
    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('delete', 'iidoShopArchivep') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}