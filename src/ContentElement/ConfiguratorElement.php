<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use Http\Message\Cookie;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\API\WeclappApi;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use HeimrichHannot\Ajax\Ajax;
use IIDO\ShopBundle\Ajax\ShopAjax;


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

            Ajax::runActiveAction('iidoShop', 'getConfiguratorAddToCartMessage', new ShopAjax($this));
            Ajax::runActiveAction('iidoShop', 'getConfiguratorAddToWatchlistMessage', new ShopAjax($this));
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
        $actionUrl          = \Environment::get('request');

        if( $stepNum <= 0 || $stepNum === "" )
        {
            $stepNum = 1;
        }

        if( $this->iidoShopRedirect )
        {
            $actionUrl = \PageModel::findByPk( $this->iidoShopRedirect )->getFrontendUrl();
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

        $this->Template->actionUrl      = $actionUrl;

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

        $skiNumber  = \Input::post("skiNumber");
        $objApi     = ApiHelper::getApiObject();

        $strName            = '';
        $intPrice           = 0;
        $strMode            = '';
        $currentItemNumber  = '';
        $editMode           = false;
        $arrItemNumber      = array();

        $arrValue   = $arrInputValue = array
        (
            'design'    => '',
            'binding'   => '',
            'length'    => '',
            'flex'      => '',
            'tuning'    => ''
        );

        if( \Input::post("EDIT_FORM") === "questionnaire" )
        {

        }
        elseif( \Input::post("EDIT_FORM") === "edit_item" )
        {
            $editMode           = true;

            $strMode            = \Input::post("SHOP_MODE");
            $currentItemNumber  = \Input::post("itemNumber");
            $strName            = \Input::post("name");
            $tuning             = \Input::post("tuning");
            $strSubMode         = \Input::post("SUBMODE");

            $arrItemNumber      = explode(".", $currentItemNumber);

            $skiNumber          = array_shift(explode(".", preg_replace('/^C./', '', $currentItemNumber)));
            $currentItem        = $objApi->runApiUrl('article/?articleNumber-eq=' . ShopHelper::getRealItemNumber($currentItemNumber, $objApi));

            $arrValue['tuning']         = ($tuning ? 'Standardtuning (Gratis)' : '');
            $arrInputValue['tuning']    = $tuning;

            $intPrice           = $currentItem['articlePrices'][0]['price'];

            \Input::setPost("category", $this->getCategoryId($skiNumber));
        }

        $objTemplate->designLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['design'];
        $objTemplate->bindingLabel      = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['binding'];
        $objTemplate->lengthLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['length'];
        $objTemplate->flexLabel         = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['flex'];
        $objTemplate->tuningLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['tuning'];
        $objTemplate->buyLabel          = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['buy'];
        $objTemplate->saveLabel         = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['save'];
        $objTemplate->watchlistLabel    = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['watchlist'];
        $objTemplate->priceLabel        = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['price'];
        $objTemplate->cartLabel         = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['cart'];
        $objTemplate->detailLabel       = $GLOBALS['TL_LANG']['iido_shop_configurator']['label']['detail'];

        $objTemplate->itemNumberRange   = $this->strItemNumberRange;
        $objTemplate->skiNumber         = $skiNumber;

        $skiNumberRange = $this->strItemNumberRange . '.' . $skiNumber;

        $objCategory    = IidoShopProductCategoryModel::findByPk( \Input::post('category') );
        $arrProducts    = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '%25&pageSize=1000');
        $objSki         = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);
//        $arrSkis        = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumber . '%25');

        $strCartLink        = '';
        $strWatchlistLink   = '';

        if( $this->iidoShopCart )
        {
            $strCartLink = \PageModel::findByPk( $this->iidoShopCart )->getFrontendUrl();
        }

        if( $this->iidoShopWatchlist )
        {
            $strWatchlistLink = \PageModel::findByPk( $this->iidoShopWatchlist )->getFrontendUrl();
        }

        $designs = $lengths = $flexs = $bindings = $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = $arrConfig = array();

        foreach( $arrProducts as $product )
        {
            if( $product['active'] === "1" || $product['active'] === 1 || $product['active'] )
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

        $bindingImage       = '';
        $productStartImage  = '';

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

            if( $editMode && $length === $arrItemNumber[3])
            {
                $arrValue['length']         = $length;
                $arrInputValue['length']    = $length;
            }
        }

        foreach($flexs as $flex)
        {
            $flexName = $objApi->getFlexName( $flex );

            $arrFlexs[] = array
            (
                'title'         => $flexName,
                'range'         => $objApi->getFlexRange( $flex ),
                'articleNumber' => $flex
            );

            if( !isset($arrConfig['default']['flex']) )
            {
                $arrConfig['default']['flex'] = $flex;
            }

            if( $editMode )
            {
                $flexModeCode = $objApi->getFlexKey( $arrItemNumber[4] );

                if( $flex === $flexModeCode )
                {
                    $arrValue['flex']       = $flexName;
                    $arrInputValue['flex']  = $flex;
                }
            }
        }

        foreach($bindings as $binding)
        {
            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);

            if( $productBinding['active'] === "1" || $productBinding['active'] === 1 || $productBinding['active'] )
            {
                $bindingImageSRC    = IidoShopProductModel::findBy("itemNumber", $binding)->overviewSRC;
                $objImage           = \FilesModel::findByPk( $bindingImageSRC );

                $arrBindings[] = array
                (
                    'title'         => $productBinding['name'],
                    'description'   => $productBinding['description'],
                    'articleNumber' => $binding,
                    'image'         => ($objImage ? $objImage->path : '')
                );

                if( !isset($arrConfig['default']['binding']) )
                {
                    $arrConfig['default']['binding'] = $binding;
                }

                if( $editMode && $arrItemNumber[5] === $binding )
                {
                    $arrValue['binding']        = $productBinding['name'];
                    $arrInputValue['binding']   = $binding;

                    $bindingImage = ImageHelper::getImageTag($bindingImageSRC, array(), true);
                }
            }
        }

        foreach($designs as $design)
        {
            $designImage    = $this->getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs );
            $designAlias    = $objApi->getColorCode( $design );

            $arrDesigns[] = array
            (
                'alias'         => $designAlias,
                'articleNumber' => $design,
                'image'         => $designImage
            );

            if( preg_match('/B$/', $design) && !isset($arrConfig['default']['design']) )
            {
                $arrConfig['default']['design'] = $design;
            }

            if( $editMode && $arrItemNumber[2] === $design )
            {
                $arrValue['design']         = '<div class="color_circle cc-' . $designAlias . '"></div>';
                $arrInputValue['design']    = $design;

                $productStartImage = $this->getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs,true );
            }
        }

        if( !$editMode )
        {
            $productStartImage = IidoShopProductModel::findOneBy("itemNumber", $skiNumber )->overviewSRC;
        }

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = '€'; //ShopConfig; TODO: get from shop config!! TODO: add the config module!!
        $objTemplate->productPrice  = ($intPrice ?: $objSki['articlePrices'][0]['price']);
        $objTemplate->productName   = ($strName ?: $objSki['name']);
        $objTemplate->productImage  = $productStartImage;
        $objTemplate->bindingImage  = $bindingImage;

        $objTemplate->cartNum       = ShopConfig::getCartNum();
        $objTemplate->cartLink      = $strCartLink;
        $objTemplate->watchlistNum  = ShopConfig::getWatchlistNum();
        $objTemplate->watchlistLink = $strWatchlistLink;

        $objTemplate->catColor      = ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) );
        $objTemplate->chooserValue          = $arrValue;
        $objTemplate->chooserInputValue     = $arrInputValue;

        $objTemplate->mode                  = $strMode;
        $objTemplate->subMode               = $strSubMode;
        $objTemplate->currentItemNumber     = $currentItemNumber;
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



    protected function getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs, $getSource = false )
    {
        foreach( $arrLengths as $arrLength )
        {
            $lengthNum = $arrLength['articleNumber'];

            foreach( $arrFlexs as $arrFlex )
            {
                $flexNum = $arrFlex['articleNumber'];

                $designItemNumber   = $skiNumber . '.' . $design . '.' . $lengthNum . '.' . $flexNum;
                $imageSRC           = IidoShopProductModel::findBy("itemNumber", $designItemNumber)->overviewSRC;

                $objImage           = \FilesModel::findByPk( $imageSRC );

                if( $objImage )
                {
                    if( $getSource )
                    {
                        return $imageSRC;
                    }

                    return $objImage->path;
                }
            }
        }

        return '';
    }



    protected function getCategoryId( $skiNumber )
    {
        $objAllSkis = IidoShopProductModel::findByArchive( $this->iidoShopArchive );

        if( $objAllSkis )
        {
            while( $objAllSkis->next() )
            {
                if( preg_match('/^C\.' . $skiNumber . '/', $objAllSkis->itemNumber) )
                {
                    $categories = \StringUtil::deserialize( $objAllSkis->categories, TRUE );

                    if( count($categories) )
                    {
                        return $categories[0];
                    }
                }
            }
        }

        return '';
    }



//    protected function getRealItemNumber( $itemNumber, $objApi )
//    {
//        $flexNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$4', $itemNumber);
//        $flexKey        = $objApi->getFlexKey( $flexNumber );
//        $newItemNumber  = preg_replace('/\.' . $flexNumber . '\./', '.' . $flexKey . '.', $itemNumber);
//
//        return $newItemNumber;
//    }
}