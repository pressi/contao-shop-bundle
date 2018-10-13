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
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use HeimrichHannot\Ajax\Ajax;
use IIDO\ShopBundle\Ajax\ShopAjax;


class ConfiguratorV2Element extends \ContentElement
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

//            echo "<pre>"; print_r( ShopHelper::getCartPrice() ); exit;
        }

//        $objUsers = $this->objApi->runApiUrl('customer?lastName-like=' . urlencode('Rumpfhuber') );
//        $objUsers = $this->objApi->runApiUrl('customer?email-like=mail@stephanpressl.at' );
//        $objUsers = $this->objApi->runApiUrl('customer?customerNumber-eq=C1342' );
//        $objAttr = $this->objApi->runApiUrl('customAttributeDefinition');

//        echo "<pre>";
//        print_r( 'customer?lastName-like=' . urldecode('Preßl') );
//        echo "<br>";
//        print_r( $objAttr );
//        echo "<br>";
//        print_r( $objUsers );
//        echo "<br>";
//        print_r( $objUsers[0]['addresses'][1]['street1'] === 'Höribachhof, 7/8' );
//        exit;
//        $this->objApi->addQuestionnaireDataToWeclapp($objUsers);

//        echo "<pre>"; print_r( $objUser['lastName'] === 'Preßl' ); exit;

//        $objOrder = $this->objApi->runApiUrl('salesOrder?orderNumber-eq=1231');

//        echo "<pre>";
//        print_r( $objOrder );
//        exit;

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

        if( \Session::getInstance()->get("FORM_SUBMIT") === "questionnaire" )
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
//echo "<pre>"; print_r( $arrItemNumber ); echo "</pre>"; exit;
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

        $objCategory    = $objCategory ? : IidoShopProductCategoryModel::findByPk( \Input::post('category') );
        $objSki         = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);


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

        $this->allSkiVariants   = array();
        $this->arrConfig        = array();

        $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = array();
        list($this->allSkiVariants, $this->arrConfig) = $this->getSkiVariantsAndConfig( $skiNumber );

        $arrConfig  = $this->arrConfig;
        $designs    = $this->arrConfig['designs'];
        $lengths    = $this->arrConfig['lengths'];
        $woodCores  = $this->arrConfig['woodCores'];
        $flexs      = $this->arrConfig['flexs'];
        $keils      = $this->arrConfig['keils'];
        $bindings   = $this->arrConfig['bindings'];

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
            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);
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

                $strBindingName = $productBinding['name'];

                $sortingNumber = ShopHelper::renderBindingOrderNumber($strBindingName);
                $arrBindings[ $sortingNumber ] = array
                (
                    'title'         => $strBindingName,
                    'description'   => $productBinding['description'],
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

        krsort($arrBindings);
        $arrBindings = array_values($arrBindings);


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

        if( $questionnaireMode )
        {
            $arrValue['tuning'] = 'Standardtuning (Gratis)';
            $arrInputValue['tuning'] = 'Standardtuning';
        }

        if( !$editMode && !$productStartImage )
        {
            $productStartImage      = $arrDesigns[0]['image'];
            $objProductStartImage   = $arrDesigns[0]['objImage'];
        }

        if( !$productStartImage )
        {
            $skiImage = $objApi->getItemImage( (array) $objSki);

            $objProductStartImage = $skiImage;
//echo "<pre>1"; print_r( $skiImage ); exit;
            if( $skiImage )
            {
                $productStartImage = $skiImage;
            }
        }
//        echo "<pre>"; print_r( $productStartImage ); exit;
        $arrConfig['woodCores'] = $woodCores;
        $arrConfig['flexs']     = $flexs;
        $arrConfig['keils']     = $keils;

        $arrConfig['default']['woodCore']   = $this->arrWoodCore[0];
        $arrConfig['default']['keil']       = array_shift($keils);
//echo "<pre>"; print_r( $productStartImage );
//echo "<br>";
//print_R( $objProductStartImage);
//exit;

        foreach($this->allSkiVariants as $skiVariant)
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
                $arrSkiVariant['price'] = ShopHelper::getCurrentPrice( $skiVariant );

                $arrConfig['products'][] = $arrSkiVariant;
            }
        }

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = ShopConfig::getCurrency();
        $objTemplate->productPrice  = ($intPrice ?: ShopHelper::getCurrentPrice( (array) $objSki ));
        $objTemplate->productName   = ($strName ?: $objSki['name']);
        $objTemplate->productDesc   = $objSki['shortDescription1'];
        $objTemplate->productSlogan = $objSki['shortDescription2'];
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

        $objTemplate->configNumber      = false;
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
        $lengthKey          = ShopHelper::getShortestSize( $this->arrConfig['lengths'] );
        $flexKey            = ShopHelper::getMaxMinFlexNum( $this->arrConfig['flexs'], 'min' );
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

        foreach( $this->allSkiVariants as $skiVariant )
        {
            if( preg_match('/^' . $searchItemNumber . '/', $skiVariant['articleNumber']) )
            {
                $getImage = false;

                if( $binding )
                {
                    if( preg_match('/' . $binding . '$/', $skiVariant['articleNumber']) )
                    {
                        if( count($skiVariant['articleImages']) )
                        {
                            $getImage = TRUE;
                        }
                    }
                }
                else
                {
                    if( count($skiVariant['articleImages']) )
                    {
                        $getImage = true;
                    }
                }

                if( $getImage)
                {
                    $itemImage = $this->objApi->getItemImage( $skiVariant );

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
        $strContent = '';

        if( $arrSki['longText'] )
        {
            $strContent = '<div class="ce_text intro"><div class="element-inside"><h2 class="headline text-center">' . $arrSki['shortDescription1'] . '</h2>' . $arrSki['longText'] . '</div></div>';
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



    /**
     * Get all ski variants and write a JSON file with the config
     *
     * @param $skiNumber
     *
     * @return array
     * @throws \Exception
     */
    protected function getSkiVariantsAndConfig( $skiNumber )
    {
        $arrProducts    = array();
        $arrConfig      = array('designs'=>[],'lengths'=>[],'woodCores'=>[],'flexs'=>[],'keils'=>[],'bindings'=>[]);

        if( $skiNumber && strlen($skiNumber) )
        {
            $itemNumber     = $skiNumber;

            if( !preg_match('/^' . $this->strItemNumberRange . './', $skiNumber) )
            {
                $itemNumber = $this->strItemNumberRange . '.' . $skiNumber;
            }

            $fileName       = 'shop-configurator-config-' . $skiNumber . '.json';
            $objFile        = new \File( 'assets/shop_tmp/' . $fileName );
            $writeToFile    = true;

            if( $objFile->exists() )
            {
                if( (time()-$this->fileLifetime) < $objFile->mtime )
                {
                    $writeToFile = false;
                    $arrConfig = json_decode( $objFile->getContent(), TRUE );
                }
            }

            $arrProducts = $this->getApiProductsFromFile( $itemNumber );

            if( $writeToFile )
            {
                if( count($arrProducts) )
                {
                    foreach($arrProducts as $arrApiProduct)
                    {
                        $objApiProduct = ShopHelper::getProductObject( (array) $arrApiProduct );

                        if( $objApiProduct )
                        {
                            $arrItemNumber  = explode(".", $arrApiProduct['articleNumber']);

                            $indexAdd       = 0;
                            $designIndex    = 1;
                            $lengthIndex    = 2;
                            $woodCoreIndex  = 3;
                            $flexIndex      = 4;
                            $keilIndex      = 5;
                            $bindingIndex   = 6;

                            if( $arrItemNumber[0] === $this->strItemNumberRange )
                            {
                                $indexAdd = 1;
                            }

                            if( ($arrItemNumber[0] === $this->strItemNumberRange && count($arrItemNumber) >= 7) || ($arrItemNumber[0] !== $this->strItemNumberRange) )
                            {
                                $designCode     = $arrItemNumber[ ($designIndex + $indexAdd) ];
                                $lengthCode     = $arrItemNumber[ ($lengthIndex + $indexAdd) ];
                                $woodCoreCode   = $arrItemNumber[ ($woodCoreIndex + $indexAdd) ];
                                $flexCode       = $arrItemNumber[ ($flexIndex + $indexAdd) ];
                                $keilCode       = $arrItemNumber[ ($keilIndex + $indexAdd) ];
                                $bindingCode    = $arrItemNumber[ ($bindingIndex + $indexAdd) ];

                                if( $arrItemNumber[0] === $this->strItemNumberRange )
                                {
                                    $arrConfig['designs'][ $designCode ]        = $designCode;
                                    $arrConfig['lengths'][ $lengthCode ]        = $lengthCode;

                                    if( in_array($woodCoreCode, $this->arrWoodCore) )
                                    {
                                        $arrConfig['woodCores'][ $woodCoreCode ] = $woodCoreCode;
                                    }

                                    if( $flexCode )
                                    {
                                        $arrConfig['flexs'][ $flexCode ] = $flexCode;
                                    }

                                    if( $keilCode )
                                    {
                                        $arrConfig['keils'][ $keilCode ] = $keilCode;
                                    }

                                    if( $bindingCode )
                                    {
                                        $arrConfig['bindings'][ $bindingCode ] = $bindingCode;
                                    }
                                }
                            }
                        }

//                    $arrProducts[] = $arrApiProduct;
                    }
                }

                $arrConfig['designs']   = array_values( $arrConfig['designs'] );
                $arrConfig['lengths']   = array_values( $arrConfig['lengths'] );
                $arrConfig['woodCores'] = array_values( $arrConfig['woodCores'] );
                $arrConfig['flexs']     = array_values( $arrConfig['flexs'] );
                $arrConfig['keils']     = array_values( $arrConfig['keils'] );
                $arrConfig['bindings']  = array_values( $arrConfig['bindings'] );

                $objFile->write( json_encode($arrConfig) );
                $objFile->close();
            }
        }

        return array( $arrProducts, $arrConfig );
    }



    /**
     * Get API Products from API or file
     *
     * @param $skiNumber
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function getApiProductsFromFile( $skiNumber )
    {
        $fileName       = 'shop-configurator-products-' . $skiNumber . '.json';
        $objFile        = new \File( 'assets/shop_tmp/' . $fileName );
        $arrProducts    = array();
        $writeToFile    = true;

        if( $objFile->exists() )
        {
            if( (time()-$this->fileLifetime) < $objFile->mtime )
            {
                $writeToFile = false;
                $arrProducts = json_decode( $objFile->getContent(), TRUE );
            }
        }

        if( $writeToFile )
        {
            $arrProducts = $this->getApiProducts( $skiNumber . '.__.___.__.__.__.%25' );

            $objFile->write( json_encode($arrProducts) );
            $objFile->close();
        }

        return $arrProducts;
    }



    /**
     * Get API products from API
     *
     * @param     $searchPath
     * @param int $page
     *
     * @return array
     */
    protected function getApiProducts( $searchPath, $page = 1 )
    {
        $currentPage = $page;

        if( $page > 1 )
        {
            $addon = '&page=' . $page;
        }

        $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $searchPath . '&pageSize=1000' . $addon);

        if( count($arrProducts) === 1000 )
        {
            $page = ($page + 1);

            $arrProducts2   = $this->getApiProducts( $searchPath, $page);
            $arrProducts    = array_merge($arrProducts, $arrProducts2);
        }

        if( preg_match('/^' . $this->strItemNumberRange . './', $searchPath) && $currentPage === 1 )
        {
            $newSearchPath  = preg_replace('/^' . $this->strItemNumberRange . './', '', $searchPath);
            $newSearchPath  = preg_replace('/\.\%25$/', '', $newSearchPath);

            $arrProducts3   = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $newSearchPath . '&pageSize=1000');
            $arrProducts    = array_merge($arrProducts, $arrProducts3);

            if( count($arrProducts3) === 1000 )
            {
                $arrProducts4   = $this->getApiProducts( $newSearchPath, 2 );
                $arrProducts    = array_merge($arrProducts, $arrProducts4);
            }
        }

        return $arrProducts;
    }
}