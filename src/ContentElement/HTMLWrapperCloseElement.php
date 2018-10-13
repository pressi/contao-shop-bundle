<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


/**
 * Front end content element "html wrapper start".
 *
 * @package IIDO\BasicBundle
 * @author Stephan Preßl <development@prestep.at>
 */
class HTMLWrapperCloseElement extends \ContentElement
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_htmlClose';



    /**
     * Generate configurator element
     *
     * @return string
     */
    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### HTML WRAPPER - CLOSE ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        $objSiblings    = \ContentModel::findPublishedByPidAndTable( $this->pid, 'tl_article');
        $countSeparator = 0;
        $hasSeparator   = false;

        if( $objSiblings )
        {
            $isStart    = false;
            $isStop     = false;

            while( $objSiblings->next() )
            {
                if( !$isStart )
                {
                    if( $objSiblings->type === "htmlOpen" )
                    {
                        $isStart        = true;
                        $countSeparator = 0;
                        continue;
                    }
                }
                else
                {
                    if( !$isStop )
                    {
                        if( $objSiblings->type === "htmlSeparator" )
                        {
                            $countSeparator++;
                            continue;
                        }
                    }

                    if( $objSiblings->type === "htmlClose" && $objSiblings->id === $this->id )
                    {
                        $isStop = true;
                        continue;
                    }
                }
            }
        }

        if( $countSeparator )
        {
            $hasSeparator = true;
        }

        $this->Template->hasSeparator   = $hasSeparator;
    }
}
