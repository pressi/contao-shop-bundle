<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use Doctrine\Bundle\DoctrineCacheBundle\Tests\Functional\BaseCacheTest;
use Http\Message\Cookie;
use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\API\WeclappApi;
use IIDO\ShopBundle\Config\ApiConfig;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Helper\ShopOrderHelper;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use HeimrichHannot\Ajax\Ajax;
use IIDO\ShopBundle\Ajax\ShopAjax;


class ConfiguratorV3Element extends \ContentElement
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
     * Wood Core, available item number ranges
     *
     * @var array
     */
    protected $arrWoodCore = ['EP', 'PP'];


    /**
     * API Object
     *
     * @var object
     */
    protected $objApi;


    /**
     * JSON, Config / Products file lifetime
     *
     * @var float|int
     */
    protected $fileLifetime = (60*120);


    protected $urlFromGet = FALSE;



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

            $this->objApi = ApiHelper::getApiObject();
        }

        if( \Input::get("auto_item") )
        {
            \Input::setPost("skiNumber", \Input::get("auto_item") );

            $this->urlFromGet = TRUE;
        }

        // TODO: Bindungsnummern und Tuningnummern im Backend verwalten!!

//        $arrCustomer = $this->objApi->runApiUrl('customer/?customerNumber-eq=C1328');

//        echo "<pre>";
//        print_r( $arrCustomer ); echo "<br>";
//        print_r( ShopOrderHelper::checkDeliveryAddress($arrCustomer, ShopOrderHelper::getOrder( 57 )) ); exit;

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

        if( \Session::getInstance()->get("FORM_SUBMIT") === "questionnaire" || $this->urlFromGet )
        {
            $stepNum = 2;
        }

        if( $stepNum <= 0 || $stepNum === "" )
        {
            $stepNum = 1;
        }

        if( $this->iidoShopRedirect )
        {
            $actionUrl = \PageModel::findByPk( $this->iidoShopRedirect )->getFrontendUrl();
        }

        $this->stepDetails  = '';

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
        $this->Template->stepDetails    = $this->stepDetails;
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
                $itemNumber = '';

                if( $objCategory->itemNumbers )
                {
//                    $arrNumbers = explode(",", $objCategory->itemNumbers);
                    $arrItemNumber  = explode(".", preg_replace('/^' . $this->strItemNumberRange . '\./', '', $objCategory->itemNumbers));
                    $itemNumber     = array_shift( $arrItemNumber );
                }
                else
                {
                    $itemNumber = $this->getSkiNumber( $objCategory, $this->iidoShopArchive);
                }

                $arrRow         = $objCategory->row();

                $arrRow['itemNumberRange'] = $itemNumber;

                $arrConfigCategories[ $objCategory->sorting ] = $arrRow;
            }
        }

        ksort($arrConfigCategories);
        $arrConfigCategories = array_values($arrConfigCategories);

        $objTemplate->arrCategories = $arrConfigCategories;
    }



    /**
     * Render Step 2 Template
     *
     * @param $objTemplate
     *
     * @throws \Exception
     */
    protected function renderStep2( &$objTemplate )
    {
        \Controller::loadLanguageFile("iido_shop_configurator");

        $skiNumber          = \Input::post("skiNumber");
        $objApi             = $this->objApi;
        /* @var $objApi WeclappApi */

        $arrItemNumber      = array();
        $arrLabels          = $GLOBALS['TL_LANG']['iido_shop_configurator']['label'];
        $arrLabels['extraLink'] = $this->iidoShopExtraLinkLabel;

        $editMode           = false;
        $questionnaireMode  = false;

        $strName            = '';
        $strMode            = '';
        $strSubMode         = '';
        $intPrice           = 0;
        $currentItemNumber  = '';

        $objCategory        = false;
        $objSession         = \Session::getInstance();
        $strLang            = BasicHelper::getLanguage();


        // SET DEFAULT CONFIGURATOR VALUES
        $arrValue = $arrInputValue = array('design'=>'','length'=>'','woodCore'=>'','binding'=>'','flex'=>'','tuning'=>'');


        if( $objSession->get("FORM_SUBMIT") === "questionnaire" || \Input::post("FORM_SUBMIT") === "questionnaire" )
        {
            if( $objSession->get("FORM_SUBMIT") === "questionnaire" )
            {
                \Input::setPost("FORM_SUBMIT", "questionnaire");
                \Input::setPost("itemNumber", $objSession->get("itemNumber"));

                $objSession->remove("FORM_SUBMIT");
                $objSession->remove("itemNumber");
            }

            $questionnaireMode  = true;

            $arrItemNumber      = explode(".", \Input::post("itemNumber"));
            $skiIndex           = 0;

            if( $arrItemNumber[0] === $this->strItemNumberRange )
            {
                $skiIndex       = 1;
            }

            $skiNumber          = $arrItemNumber[ $skiIndex ];

            list($categoryID, $objCategory) = $this->getCategoryId($skiNumber, true);

            \Input::setPost("category", $categoryID);
        }
        elseif( \Input::post("EDIT_FORM") === "edit_item" )
        {
            // TODO: ÜBERPRÜFEN!!!
            $editMode           = true;

            $strMode            = \Input::post("SHOP_MODE");
            $currentItemNumber  = \Input::post("itemNumber");
            $strName            = \Input::post("name");
            $tuning             = \Input::post("tuning");
            $flex               = \Input::post("flex");
            $strSubMode         = \Input::post("SUBMODE");

            $arrItemNumber      = explode(".", $currentItemNumber);
//echo "<pre>"; print_r( ShopHelper::getRealItemNumber($currentItemNumber, $objApi) ); exit;
            $skiNumber          = array_shift(explode(".", preg_replace('/^C./', '', $currentItemNumber)));
            $currentItem        = $objApi->runApiUrl('article/?articleNumber-eq=' . ShopHelper::getRealItemNumber($currentItemNumber, $objApi));
            //TODO: run articlePrice/ID

            $arrValue['tuning']         = ($tuning ? 'Standardtuning (Gratis)' : '');
            $arrInputValue['tuning']    = $tuning;

            $intPrice           = ShopHelper::getCurrentPrice( $currentItem );

            list($categoryID, $objCategory) = $this->getCategoryId($skiNumber, true);

            \Input::setPost("category", $categoryID);
        }

        $objTemplate->itemNumberRange   = $this->strItemNumberRange;
        $objTemplate->skiNumber         = $skiNumber;

        $this->arrConfig = $objApi->getConfiguratorConfig( $skiNumber );

        $objCategory    = $objCategory ? : IidoShopProductCategoryModel::findByPk( \Input::post('category') );

        if( !$objCategory && $this->urlFromGet )
        {
            list($categoryID, $objCategory) = $this->getCategoryId($skiNumber, true);
        }

//        $objSki         = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);
        $objSki         = $this->getMainSki( $skiNumber );

//echo "<pre>"; print_r( $objSki ); echo "<br>"; print_r( $skiNumber ); echo "<br>"; print_r( $this->arrConfig ); exit;
        // STEP DETAILS
        $this->stepDetails  = $this->renderStepDetails( $objCategory, $objSki );


        // CART AND WATCHLIST LINKS
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

//        $this->allSkiVariants   = array();
//        $this->arrConfig        = array();

        $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = array();

//        echo "<pre>"; print_r( $objApi->getConfiguratorConfig( $skiNumber ) ); exit;

        $arrConfig  = $this->arrConfig;
        $designs    = $this->arrConfig['config']['designs'];
        $lengths    = $this->arrConfig['config']['lengths'];
        $woodCores  = $this->arrConfig['config']['woodCores'];
        $flexs      = $this->arrConfig['config']['flexs'];
        $keils      = $this->arrConfig['config']['keils'];
        $bindings   = $this->arrConfig['config']['bindings'];

        if( !count($arrItemNumber) )
        {
            $arrItemNumber[0] = $skiNumber;
            $arrItemNumber[1] = $designs[0];
            $arrItemNumber[2] = ShopHelper::getShortestSize($lengths);
            $arrItemNumber[3] = $this->arrWoodCore[0];
            $arrItemNumber[4] = ShopHelper::getMaxMinFlexNum($flexs, 'min');
            $arrItemNumber[5] = '__';
        }


        // SET LENGTH DATA
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

            if( ($editMode && $length === $arrItemNumber[3]) || ($questionnaireMode && $length === $arrItemNumber[2]))
            {
                $arrValue['length']         = $length;
                $arrInputValue['length']    = $length;
            }
        }


        // SET FLEX DATA
        $arrCurrentFlexs = $objApi->getFlex();
        foreach($arrCurrentFlexs as $flexAlias => $arrCurrFlex)
        {
            $flexNum = trim($arrCurrFlex['range']['num']);

            if( in_array($flexNum, $flexs) )
            {
                $currFlex = $arrCurrFlex;

                $currFlex['title'] = $arrCurrFlex['label'];
                $currFlex['alias'] = $flexNum;

                $arrFlexs[] = $currFlex;
            }

            if( !isset($arrConfig['default']['flex']) )
            {
                $arrConfig['default']['flex'] = $arrCurrentFlexs['medium']['range']['num'];
            }

            if( $questionnaireMode )
            {
                if( $flexNum === $arrItemNumber[4] )
                {
                    $arrValue['flex']       = $objApi->getFlexName( $flexNum );
                    $arrInputValue['flex']  = $flexNum;
                }
            }
        }


        // PRODUCT START IMAGE
        $productStartImage      = '';
        $objProductStartImage   = false;

        $arrObjectProductStartImages = array();


        // SET FLEX DATA
        foreach($bindings as $binding)
        {
            $productBinding = $this->getBindingFromConfig( $binding );
//            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);
            $objBinding     = ShopHelper::getProductObject( $productBinding );

            if( $objBinding )
            {
                $arrImage = array();
                $arrItemImageObjects = array();

                foreach($designs as $design)
                {
                    $strDesignImage = $this->getBindingImage( $skiNumber, $design, $objBinding->articleNumber );

                    $arrItemImageObjects[ $design ] = $strDesignImage;

                    if( $strDesignImage )
                    {
                        $strResizedDesignImage = ImageHelper::getImagePath($strDesignImage, array(310, '', 'proportional'));

                        if( $strResizedDesignImage )
                        {
                            $strDesignImage = $strResizedDesignImage;
                        }
                    }

                    $arrImage[ $design ] = $strDesignImage;
                }

                $strBindingDescription = $productBinding['description'];

                if( $productBinding['meta'][ $strLang ]['description'] )
                {
                    $strBindingDescription = nl2br( $productBinding['meta'][ $strLang ]['description'] );
                }

                $arrBindings[] = array
                (
                    'title'         => $productBinding['name'],
                    'description'   => $strBindingDescription,
                    'articleNumber' => $binding,
                    'image'         => htmlspecialchars( json_encode($arrImage), ENT_QUOTES, 'UTF-8'),
                    'objImage'      => $arrItemImageObjects
                );

                if( !isset($arrConfig['default']['binding']) )
                {
                    $arrConfig['default']['binding'] = "none"; //$binding;
                }

                if( $editMode && $arrItemNumber[5] === $binding )
                {
                    $arrValue['binding']        = $productBinding['name'];
                    $arrInputValue['binding']   = $binding;

//                    $bindingImage = ImageHelper::getImageTag($bindingImageSRC, array(), true);
                    $productStartImage      = $arrImage[ $arrConfig['default']['design'] ]; //ImageHelper::getImageTag($arrImage[ $arrConfig['default']['design'] ], array(), true, $arrItemImageObjects[ $arrConfig['default']['design'] ]);
                    $objProductStartImage   = $arrItemImageObjects[ $arrConfig['default']['design'] ];
                }
                elseif( $questionnaireMode )
                {
                    $arrValue['binding']        = $arrLabels['noBinding'];
                    $arrInputValue['binding']   = 'none';
                }
            }
        }

//        krsort($arrBindings);
//        $arrBindings = array_values($arrBindings);


        // SET DESIGN DATA
        $key = 0;
        foreach($designs as $design)
        {
            $designImage    = $this->getDesignImage( $design, $skiNumber );
            $designAlias    = $objApi->getColorCode( $design );
            $designLabel    = $objApi->getColorLabel( $design );

            $objectDesignImage = $designImage;

            if( $designImage )
            {
                $resizedDesignImage = ImageHelper::getImagePath($designImage, array(310, '', 'proportional'));

                if( $resizedDesignImage )
                {
                    $designImage = $resizedDesignImage;
                }
            }

            $arrDesigns[] = array
            (
                'alias'         => $designAlias,
                'label'         => $designLabel,
                'articleNumber' => $design,
                'image'         => $designImage,
                'objImage'      => $objectDesignImage
            );

            if( $key===0 && !isset($arrConfig['default']['design']) )
            {
                $arrConfig['default']['design'] = $design;
            }

            if( ($editMode && $arrItemNumber[2] === $design) || ($questionnaireMode && $arrItemNumber[1] === $design) )
            {
                $arrValue['design']         = '<div class="color_circle cc-' . $designAlias . '"><span class="title">' . $designLabel . '</span></div>';
                $arrInputValue['design']    = $design;

                if( !$productStartImage )
                {
                    $productStartImage      = $designImage;
                    $objProductStartImage   = $objectDesignImage;
                }
            }

            $key++;
        }

//        if( $questionnaireMode )
//        {
//            $arrValue['tuning'] = 'Standardtuning (Gratis)';
//            $arrInputValue['tuning'] = 'Standardtuning';
//        }

        if( !$editMode && !$productStartImage )
        {
            $productStartImage      = $arrDesigns[0]['image'];
            $objProductStartImage   = $arrDesigns[0]['objImage'];
        }

        if( !$productStartImage )
        {
            $skiImage = $objApi->getItemImage( (array) $objSki);

            $objProductStartImage = $skiImage;

            if( $skiImage )
            {
                $productStartImage = $skiImage;
            }
        }

        $arrConfig['woodCores'] = $woodCores;
        $arrConfig['flexs']     = $flexs;
        $arrConfig['keils']     = $keils;

        $arrConfig['default']['woodCore']   = $this->arrWoodCore[0];
        $arrConfig['default']['keil']       = array_shift($keils);

        foreach($this->arrConfig['products'] as $skiVariant)
        {
            $skiVariant     = (array) $skiVariant;
            $arrSkiVariant  = array();

            if( preg_match('/^' . $this->strItemNumberRange . './', $skiVariant['articleNumber']) )
            {
                $arrSkiVariant['articleNumber'] = $skiVariant['articleNumber'];
            }
            else
            {
                $arrSkiVariant['articleNumber'] = $skiVariant['articleNumber'] . '.none';
            }

            if( count($arrSkiVariant) && $arrSkiVariant['articleNumber'] )
            {
                $arrSkiVariant['price'] = $skiVariant['price']; //ShopHelper::getCurrentPrice( $skiVariant );

                $arrConfig['products'][] = $arrSkiVariant;
            }
        }

        $tunings = $this->arrConfig['tunings'];

        if( $tunings && is_array($tunings) )
        {
            foreach($tunings as $arrTuning)
            {
                $strTuningDescription = $arrTuning['description'];

                if( $arrTuning['meta'][ $strLang ]['description'] )
                {
                    $strTuningDescription = nl2br( $arrTuning['meta'][ $strLang ]['description'] );
                }

                $arrTunings[ $arrTuning['articleNumber'] ] = array
                (
                    'name'          => $arrTuning['name'],
                    'description'   => $strTuningDescription,
                    'itemNumber'    => $arrTuning['articleNumber'],
                    'price'         => $arrTuning['price'] //ShopHelper::getCurrentPrice( $arrTuning )
                );
            }
        }

        if( count($arrTunings) )
        {
            $arrConfig['tunings'] = $arrTunings;
        }

        if( $questionnaireMode )
        {
            $arrValue['tuning'] = $arrTunings['K0001']['name'];
            $arrInputValue['tuning'] = 'K0001';
        }

        $arrConfig['default']['tuning'] = 'K0001'; // TODO: Standardtuning im Backend verwalten!

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = ShopConfig::getCurrency();
        $objTemplate->productPrice  = ShopHelper::renderPrice($intPrice ?: $objSki['price'], true);
        $objTemplate->productName   = ($strName ?: $objSki['name']);
        $objTemplate->productDesc   = $objSki['meta'][ $strLang ]['shortDescription1']?:$objSki['shortDescription1'];
        $objTemplate->productSlogan = $objSki['meta'][ $strLang ]['shortDescription2']?:$objSki['shortDescription2'];
        $objTemplate->productImage  = $productStartImage;
        $objTemplate->productImageObj = $objProductStartImage;

        $objTemplate->cartNum       = ShopConfig::getCartNum();
        $objTemplate->cartLink      = $strCartLink;
        $objTemplate->watchlistNum  = ShopConfig::getWatchlistNum();
        $objTemplate->watchlistLink = $strWatchlistLink;

        $objTemplate->catColor          = ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) );
        $objTemplate->chooserValue      = $arrValue;
        $objTemplate->chooserInputValue = $arrInputValue;

        $objTemplate->mode              = $strMode;
        $objTemplate->subMode           = $strSubMode;
        $objTemplate->currentItemNumber = $currentItemNumber;
        $objTemplate->questionnaireMode = $questionnaireMode;

        $objTemplate->extraLink         = $this->iidoShopExtraLink;
//        $objTemplate->extraLinkLabel    = $this->iidoShopExtraLinkLabel;
        $objTemplate->extraLinkClass    = $this->iidoShopExtraLinkClass;

        $objTemplate->hasMoreDetails    = (strlen($this->stepDetails));
        $objTemplate->label             = $arrLabels;

        $objTemplate->configNumber      = $skiNumber;
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
     * Get design image
     *
     * @param        $design
     * @param        $skiNumber
     * @param string $binding
     *
     * @return string
     */
    protected function getDesignImage( $design, $skiNumber, $binding = '' )
    {
        $lengthKey          = ShopHelper::getShortestSize( $this->arrConfig['config']['lengths'] );
        $flexKey            = ShopHelper::getMaxMinFlexNum( $this->arrConfig['config']['flexs'], 'min' );
        $woodCore           = $this->arrWoodCore[0];

        $searchItemNumber   = $skiNumber . '.' . $design . '.' . $lengthKey . '.' . $woodCore . '.' . $flexKey;

        $imagePath          = $this->getImagePathFromSearch( $searchItemNumber, $binding );

        if( !$imagePath )
        {
            $searchItemNumber   = $skiNumber . '.' . $design . '.' . $lengthKey . '.' . $this->arrWoodCore[1] . '.' . $flexKey;
            $imagePath          = $this->getImagePathFromSearch( $searchItemNumber, $binding );
        }

        return $imagePath;
    }



    /**
     * Search for image path, from article number and binding number
     *
     * @param        $searchItemNumber
     * @param string $binding
     *
     * @return string
     */
    protected function getImagePathFromSearch( $searchItemNumber, $binding = '' )
    {
        $imagePath = '';

//        foreach( $this->allSkiVariants as $skiVariant )
        foreach( $this->arrConfig['products'] as $skiVariant )
        {
            if( preg_match('/^' . $searchItemNumber . '/', $skiVariant['articleNumber']) )
            {
                $getImage = false;

                if( $binding )
                {
                    if( preg_match('/' . $binding . '$/', $skiVariant['articleNumber']) )
                    {
//                        if( count($skiVariant['articleImages']) )
                        if( $skiVariant['image'] )
                        {
                            $getImage = TRUE;
                        }
                    }
                }
                else
                {
//                    if( count($skiVariant['articleImages']) )
                    if( $skiVariant['image'] )
                    {
                        $getImage = true;
                    }
                }

                if( $getImage)
                {
                    $itemImage = $skiVariant['image']; //$this->objApi->getItemImage( $skiVariant );

                    if( $itemImage )
                    {
                        $imagePath = $itemImage;
                        break;
                    }
                }
            }
        }

        return $imagePath;
    }



    /**
     * Get binding image
     *
     * @param $skiNumber
     * @param $design
     * @param $binding
     *
     * @return string
     */
    protected function getBindingImage( $skiNumber, $design , $binding )
    {
        if( !preg_match('/^' . $this->strItemNumberRange . '/', $skiNumber) )
        {
            $skiNumber = $this->strItemNumberRange . '.' . $skiNumber;
        }

        return $this->getDesignImage($design, $skiNumber, $binding);
    }



    /**
     * Get category id (and object)
     *
     * @param      $skiNumber
     * @param bool $returnObject
     *
     * @return array|int|string
     */
    protected function getCategoryId( $skiNumber, $returnObject = false )
    {
        if( !preg_match('/^' . $this->strItemNumberRange . '/', $skiNumber) )
        {
            $skiNumber = $this->strItemNumberRange . '.' . $skiNumber;
        }

        $objCategory = IidoShopProductCategoryModel::findOneBy('itemNumbers', $skiNumber);

        if( $objCategory )
        {
            return $returnObject ? array($objCategory->id, $objCategory) : $objCategory->id;
        }

        return '';
    }



    /**
     * Render step detail infos
     *
     * @param       $objCategory
     * @param array $arrSki
     *
     * @return string
     */
    protected function renderStepDetails( $objCategory, array $arrSki = array() )
    {
        $strLang        = BasicHelper::getLanguage();

        $strContent     = '';
        $strLongText    = $arrSki['meta'][ $strLang ]['longText']?:$arrSki['longText'];
        $strShortDesc   = $arrSki['meta'][ $strLang ]['shortDescription1']?:$arrSki['shortDescription1'];

        if( $strLang !== "de" )
        {
            $strLongText = nl2br( $strLongText );
        }

        if( $strLongText )
        {
            $strContent = '<div class="ce_text intro"><div class="element-inside"><h2 class="headline text-center">' . $strShortDesc . '</h2>' . $strLongText . '</div></div>';
        }

        $objElements = \ContentModel::findPublishedByPidAndTable( $objCategory->id, $objCategory->getTable());

        if( $objElements && $objElements->count() )
        {
            while( $objElements->next() )
            {
                $strContent .= \Controller::getContentElement( $objElements->id );
            }
        }

        return $strContent;
    }



    protected function getBindingFromConfig( $bindingItemNumber )
    {
        foreach($this->arrConfig['bindings'] as $arrBinding)
        {
            if( $arrBinding['articleNumber'] === $bindingItemNumber )
            {
                return $arrBinding;
            }
        }

        return false;
    }



    protected function getMainSki( $skiNumber )
    {
        foreach($this->arrConfig['products'] as $arrSki)
        {
            if( $arrSki['articleNumber'] === $skiNumber )
            {
                return $arrSki;
            }
        }

        return false;
    }
}