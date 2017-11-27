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

namespace IIDO\ShopBundle\ContentElement;


use Http\Message\Cookie;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\ShopBundle\API\WeclappApi;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;

use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;


class ConfiguratorElement extends \ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_configurator';


    /**
     * Item number range
     *
     * @var string
     */
    protected $strItemNumberRange = 'C';



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

            $objTemplate->wildcard  = '### SHOP: PRODUKT KONFIGURATOR - Original+ ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }
        else
        {
            $GLOBALS['TL_JAVASCRIPT'][] = BundleConfig::getBundlePath( true ) . '/javascript/IIDO.Shop.Configurator.js|static';
        }

        return parent::generate();
    }



    /**
     * Compile the content element
     */
    protected function compile()
    {
        $nextStepNum        = 2;
        $stepNum            = (int) \Input::post("NEXT_STEP");

        if( $stepNum <= 0 || $stepNum === "" )
        {
            $stepNum = 1;
        }

        $strStepTemplate    = 'iido_shop_configurator_step' . $stepNum;
        $stepFunction       = 'renderStep' . $stepNum;
        $chooseContent      = '';

        $objStepTemplate    = new \BackendTemplate( $strStepTemplate );

        // Step Template Vars
        $objStepTemplate->id        = $this->id;

        $this->$stepFunction( $objStepTemplate );

        $this->Template->stepContent    = $objStepTemplate->parse();
        $this->Template->stepNum        = $stepNum;
        $this->Template->nextStepNum    = $nextStepNum;

        if( $stepNum > 1 )
        {
            $objChooseTemplate      = new \BackendTemplate('iido_shop_configurator_step1');
            $objChooseTemplate->id  = $this->id . '_1';

            $this->renderStep1( $objChooseTemplate );

            $chooseContent = $objChooseTemplate->parse();
        }

        $this->Template->chooseContent  = $chooseContent;
    }



    /**
     * Render Step 1 Template
     *
     * @param $objTemplate
     */
    protected function renderStep1( &$objTemplate )
    {
        $arrCategories          = \StringUtil::deserialize( $this->iidoShopCategories, TRUE );
        $arrConfigCategories    = array();

        foreach( $arrCategories as $categoryID )
        {
            $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

            if( $objCategory )
            {
                $itemNumber = $this->getSkiNumber( $objCategory, $this->iidoShopArchive);
                $arrRow     = $objCategory->row();

                $arrRow['itemNumberRange'] = $itemNumber;

                $arrConfigCategories[] = $arrRow;
            }
        }

        $objTemplate->arrCategories = $arrConfigCategories;
    }



    /**
     * Render Step 2 Template
     *
     * @param $objTemplate
     */
    protected function renderStep2( &$objTemplate )
    {
        \Controller::loadLanguageFile("iido_shop_configurator");

        $skiNumber = \Input::post("skiNumber");

        $objTemplate->designLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['design'];
        $objTemplate->bindingLabel      = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['binding'];
        $objTemplate->lengthLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['length'];
        $objTemplate->flexLabel         = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['flex'];
        $objTemplate->tuningLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['tuning'];
        $objTemplate->buyLabel          = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['buy'];
        $objTemplate->noticeLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['notice'];
        $objTemplate->priceLabel        = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['price'];
        $objTemplate->cartLabel         = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['cart'];
        $objTemplate->detailLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['detail'];

        $objTemplate->itemNumberRange   = $this->strItemNumberRange;
        $objTemplate->skiNumber         = $skiNumber;

        $skiNumberRange = $this->strItemNumberRange . '.' . $skiNumber;

        $objApi         = ApiHelper::getApiObject();

        $objCategory    = IidoShopProductCategoryModel::findByPk( \Input::post('category') );
        $arrProducts    = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '%25');
        $objSki         = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);
//        $arrSkis        = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumber . '%25');

        $designs = $lengths = $flexs = $bindings = $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = $arrConfig = array();

        foreach( $arrProducts as $product )
        {
            $articleNumber      = $product['articleNumber'];
            $arrArticleNumber   = explode(".", $articleNumber);

            $designs[]   = $arrArticleNumber[ 2 ];
            $lengths[]   = $arrArticleNumber[ 3 ];
            $flexs[]     = $arrArticleNumber[ 4 ];
            $bindings[]  = $arrArticleNumber[ 5 ];

            $arrConfig['products'][] = array
            (
                'articleNumber'     => $articleNumber,
                'price'             => $product['articlePrices'][0]['price']
            );
        }

//        foreach($arrSkis as $arrSki)
//        {
//            $arrConfig['products'][] = array
//            (
//                'articleNumber'     => $arrSki['articleNumber'],
//                'price'             => $arrSki['articlePrices'][0]['price']
//            );
//        }

        $designs     = array_unique($designs);
        $lengths     = array_unique($lengths);
        $flexs       = array_unique($flexs);
        $bindings    = array_unique($bindings);

        foreach($designs as $design)
        {
            $arrDesigns[] = array
            (
                'alias'         => $objApi->getColorCode( $design ),
                'articleNumber' => $design
            );

            if( preg_match('/B$/', $design) && !isset($arrConfig['default']['design']) )
            {
                $arrConfig['default']['design'] = $design;
            }
        }

        foreach($lengths as $length)
        {
            $arrLengths[] = array
            (
                'articleNumber' => $length
            );

            if( !isset($arrConfig['default']['length']) )
            {
                $arrConfig['default']['length'] = $length;
            }
        }

        foreach($flexs as $flex)
        {
            $arrFlexs[] = array
            (
                'title'         => $objApi->getFlexName( $flex ),
                'articleNumber' => $flex
            );

            if( !isset($arrConfig['default']['flex']) )
            {
                $arrConfig['default']['flex'] = $flex;
            }
        }

        foreach($bindings as $binding)
        {
            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);

            $arrBindings[] = array
            (
                'title'         => $productBinding[0]['name'],
                'description'   => $productBinding[0]['description'],
                'articleNumber' => $binding
            );

            if( !isset($arrConfig['default']['binding']) )
            {
                $arrConfig['default']['binding'] = $binding;
            }
        }

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = '€'; //ShopConfig; TODO: get from shop config!! TODO: add the config module!!
        $objTemplate->productPrice  = $objSki[0]['articlePrices'][0]['price'];
        $objTemplate->productName   = $objSki[0]['name'];

        $objTemplate->cartNum       = $this->getCartNum();

        $objTemplate->catColor      = ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) );
    }



    /**
     * Get ski number
     *
     * @param int|IidoShopProductCategoryModel $category
     * @param int $archiveID
     *
     * @return bool|mixed
     */
    protected function getSkiNumber( $category, $archiveID )
    {
        if( !$category instanceof IidoShopProductCategoryModel )
        {
            $category = IidoShopProductCategoryModel::findByPk( $category );
        }

        if( $category )
        {
            $objProducts = IidoShopProductCategoryModel::getProducts( $category->id, $archiveID );

            if( $objProducts )
            {
                while( $objProducts->next() )
                {
                    $itemNumber     = preg_replace('/^' . $this->strItemNumberRange . '\./', '', $objProducts->itemNumber);
                    $arrItemNumber  = explode(".", $itemNumber);

                    return array_shift( $arrItemNumber );
                }
            }
        }

        return false;
    }



    /**
     * Get products num in cart
     *
     * @return int
     */
    protected function getCartNum()
    {
        $arrCookie      = json_decode($_COOKIE['iido_shop_cart'], TRUE);
        $num            = 0;

        if( count($arrCookie) )
        {
            foreach($arrCookie as $cartProduct)
            {
                $num = ($num + (int) $cartProduct['quantity']);
            }
        }

        return $num;
    }
}