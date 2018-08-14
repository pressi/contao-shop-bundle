<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\EventListener;


use IIDO\BasicBundle\EventListener\DefaultListener;
use IIDO\BasicBundle\Helper\Message;
use IIDO\ShopBundle\Helper\ApiHelper;


/**
 * Class Backend Template Hook
 * @package IIDO\Customize\Hook
 */
class BackendTemplateListener extends DefaultListener
{

    /**
     * Edit the Frontend Template
     *
     * @param string $strContent
     * @param string $strTemplate
     *
     * @return string
     */
    public function outputShopBackendTemplate($strContent, $strTemplate)
    {
        $config = \Config::getInstance();

        if( $config->isComplete() )
        {
            if( $strTemplate === "be_main" )
            {
                if( \Input::get("do") === "iidoShopProducts" && !\Input::get("table") )
                {
                    $strMessage = '';
                    $strApi     = ApiHelper::enableApis(true);

                    if( $strApi )
                    {
                        $strMessage = Message::render(array('info'=>'<strong>' . ucfirst($strApi) . '</strong> API ist aktiviert. Alle Produktdaten werden von dort geladen!'));
                    }

                    if( $strMessage )
                    {
                        if( preg_match('/tl_empty/', $strContent) )
                        {
                            $strContent = preg_replace('/<p class="tl_empty">/', '<div class="iido-message-wrapper">' . $strMessage . '</div><p class="tl_empty">', $strContent);
                        }
                        else
                        {
                            $strContent = preg_replace('/<div([A-Za-z0-9\s\-_]{0,})class="tl_listing_container list_view"([A-Za-z0-9\s\-_]{0,})id="tl_listing">/', '<div$1class="tl_listing_container list_view"$2id="tl_listing">' . $strMessage, $strContent);
                        }
                    }
                }
            }
        }

        return $strContent;
    }

}
