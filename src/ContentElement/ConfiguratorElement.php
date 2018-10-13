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

    
    
    protected $arrWoodCore = ['PP', 'EP'];


    protected $objApi;



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

        $skiNumber  = \Input::post("skiNumber");
        $objApi     = $this->objApi;

        $strName            = '';
        $intPrice           = 0;
        $strMode            = '';
        $strSubMode         = '';
        $currentItemNumber  = '';

        $editMode           = false;
        $questionnaireMode  = false;

        $arrItemNumber      = array();
        $arrLabels          = $GLOBALS['TL_LANG']['iido_shop_configurator']['label'];

        $arrValue = $arrInputValue = array
        (
            'design'    => '',
            'length'    => '',
            'woodCore'  => '',
            'gurt'      => '',

            'binding'   => '',
            'flex'      => '',

            'tuning'    => ''
        );

        $arrLabels['extraLink'] = $this->iidoShopExtraLinkLabel;

        $objSession = \Session::getInstance();

        if( $objSession->get("FORM_SUBMIT") === "questionnaire" || \Input::post("FORM_SUBMIT") === "questionnaire" )
        {
            if( $objSession->get("FORM_SUBMIT") === "questionnaire" )
            {
                \Input::setPost("FORM_SUBMIT", "questionnaire");
                \Input::setPost("itemNumber", $objSession->get("itemNumber"));

                $objSession->remove("FORM_SUBMIT");
                $objSession->remove("itemNumber");
            }

            $questionnaireMode = true;

            $currentItemNumber  = $objSession->get("itemNumber")?:\Input::post("itemNumber");
            $arrItemNumber      = explode(".", $currentItemNumber);

            $flexIndex          = 4;
            $skiIndex           = 0;

            if( $arrItemNumber[0] === "C" )
            {
                $flexIndex  = 5;
                $skiIndex   = 1;
            }

            $flex               = $arrItemNumber[ $flexIndex ];
            $skiNumber          = $arrItemNumber[ $skiIndex ];

            \Input::setPost("category", $this->getCategoryId($skiNumber));

//            echo "<pre>"; print_r( $currentItemNumber ); exit;
        }
        elseif( \Input::post("EDIT_FORM") === "edit_item" )
        {
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

            \Input::setPost("category", $this->getCategoryId($skiNumber));
        }

        $objTemplate->itemNumberRange   = $this->strItemNumberRange;
        $objTemplate->skiNumber         = $skiNumber;

        $skiNumberRange = $this->strItemNumberRange . '.' . $skiNumber;

        $objCategory    = IidoShopProductCategoryModel::findByPk( \Input::post('category') );
//        $arrProducts    = $this->getProductsFromFile( $skiNumberRange );
        $objSki         = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);
//        $arrSkis        = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumber . '%25');

//        $arrSki = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber . '.AB.159.YYY');
//echo "<pre>";
//print_r( $skiNumberRange );
//echo "<br>";
//print_r( $skiNumber );
//print_r( $arrSki['id'] ); echo "<br>";
//print_r( $arrSki ); echo "<br>";
//print_r( $objApi->runApiUrl('warehouseStock?articleId-eq=' . $arrSki['id']) );
//        print_r( $objApi->runApiUrl('salesOrder?orderNumber-eq=1100') );
// exit;
        $this->stepDetails  = $this->renderStepDetails( $objCategory, $objSki );

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

//        $designs = $lengths = $flexs = $bindings = $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = $arrConfig = array();

        $flexs = $arrDesigns = $arrLengths = $arrFlexs = $arrBindings = $arrTunings = array();
        list($arrConfig, $designs, $lengths, $woodCores, $flexs, $keils, $bindings) = $this->getConfigFromFile( $skiNumberRange, $skiNumber );

        if( !count($arrItemNumber) )
        {
            $arrItemNumber[0] = $skiNumber;
            $arrItemNumber[1] = $designs[0];
            $arrItemNumber[2] = ShopHelper::getShortestSize($lengths);
            $arrItemNumber[3] = 'PP';
            $arrItemNumber[4] = ShopHelper::getMaxMinFlexNum($flexs, 'min');
            $arrItemNumber[5] = '__';
        }

//echo "<pre>"; print_r( $arrConfig ); exit;
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
//        $flexs       = array_unique($flexs);
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

            if( ($editMode && $length === $arrItemNumber[3]) || ($questionnaireMode && $length === $arrItemNumber[2]))
            {
                $arrValue['length']         = $length;
                $arrInputValue['length']    = $length;
            }
        }

//        foreach($flexs as $flex)
//        {
//            $flexName = $objApi->getFlexName( $flex );
//
//            $arrFlexs[] = array
//            (
//                'title'         => $flexName,
//                'range'         => $objApi->getFlexRange( $flex ),
//                'articleNumber' => $flex
//            );
//
//            if( !isset($arrConfig['default']['flex']) )
//            {
//                $arrConfig['default']['flex'] = $flex;
//            }
//
//            if( $editMode )
//            {
//                $flexModeCode = $objApi->getFlexKey( $arrItemNumber[4] );
//
//                if( $flex === $flexModeCode )
//                {
//                    $arrValue['flex']       = $flexName;
//                    $arrInputValue['flex']  = $flex;
//                }
//            }
//        }

//        echo "<pre>"; print_r( $flexs );
//echo "<br>";
//        print_r( $objApi->getFlex() );
//        exit;
//        echo "<pre>";
        foreach($objApi->getFlex() as $flexAlias => $arrCurrFlex)
        {
            $flexNum = trim($arrCurrFlex['range']['num']);

            if( in_array($flexNum, $flexs) )
            {
                $currFlex = $arrCurrFlex;

                $currFlex['title'] = $arrCurrFlex['label'];
                $currFlex['alias'] = $flexNum; //$flexAlias;

                $arrFlexs[] = $currFlex;
            }

            if( !isset($arrConfig['default']['flex']) )
            {
                $arrConfig['default']['flex'] = "56"; //$flexAlias;
            }

//            if( $editMode )
//            {
//                if( $flexAlias === $flex )
//                {
//                    $arrValue['flex']       = $flexAlias;
//                    $arrInputValue['flex']  = $flexAlias;
//                }
//            }
            if( $questionnaireMode )
            {
                if( $flexNum === $arrItemNumber[4] )
                {
                    $arrValue['flex']       = $objApi->getFlexName( $flexNum );
                    $arrInputValue['flex']  = $flexNum;
                }
            }
        }
//        print_r( $arrValue );
//        print_r( $arrInputValue );
//        exit;

        $productStartImage = $this->getDesignStartImage($skiNumber);

        foreach($bindings as $binding)
        {
            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);

            if( $productBinding['active'] === "1" || $productBinding['active'] === 1 || $productBinding['active'] )
            {
//                $strImage = $objApi->getItemImage( $productBinding );
//                echo "<pre>";
//                print_r( $currentItemNumber );
//                echo "<br>";
//                print_r( $arrItemNumber);
//                exit;
                $skiBefore = 'C.';

                if( $arrItemNumber[0] === 'C' )
                {
                    $skiBefore = '';
                }
//echo "<pre>"; print_r( $productBinding ); exit;
//                echo "<pre>"; print_r( $skiBefore . implode(".", $arrItemNumber) . '.' . $productBinding['articleNumber'] ); exit;
//                $strImage = $objApi->getItemImage( array('articleNumber'=>$skiBefore . implode(".", $arrItemNumber) . '.' . $productBinding['articleNumber']) );
                $arrImage = array();

                foreach($designs as $design)
                {
                    $arrImageItemNumber = $arrItemNumber;

                    $arrImageItemNumber[1] = $design;
//echo "<pre>"; print_r( $skiBefore . implode(".", $arrImageItemNumber) . '.' . $productBinding['articleNumber'] ); exit;
                    $strDesignImage = $objApi->getItemImage( array('articleNumber'=>$skiBefore . implode(".", $arrImageItemNumber) . '.' . $productBinding['articleNumber']) );

                    $arrImage[ $design ] = $strDesignImage;
                }


//                echo "<pre>";
//                print_R( $strImage );
//                exit;

//                if( !$strImage )
//                {
//                    $bindingImageSRC    = IidoShopProductModel::findBy("itemNumber", $binding)->overviewSRC;
//                    $objImage           = \FilesModel::findByPk( $bindingImageSRC );

//                    $strImage           = ($objImage ? $objImage->path : '');
//                }

                $arrBindings[] = array
                (
                    'title'         => $productBinding['name'],
                    'description'   => $productBinding['description'],
                    'articleNumber' => $binding,
                    'image'         => htmlspecialchars( json_encode($arrImage), ENT_QUOTES, 'UTF-8')
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
                }
                elseif( $questionnaireMode )
                {
                    $arrValue['binding']        = $arrLabels['noBinding'];
                    $arrInputValue['binding']   = 'none';

//                    $productStartImage          = $arrImage[ $designs[0] ];
                }
            }
        }
//        echo "<pre>"; print_r( $arrBindings ); exit;

        foreach($designs as $design)
        {
            $designImage    = $this->getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs );
            $designAlias    = $objApi->getColorCode( $design );
            $designLabel    = $objApi->getColorLabel( $design );

            $arrDesigns[] = array
            (
                'alias'         => $designAlias,
                'label'         => $designLabel,
                'articleNumber' => $design,
                'image'         => $designImage
            );

            if( preg_match('/B$/', $design) && !isset($arrConfig['default']['design']) )
            {
                $arrConfig['default']['design'] = $design;
            }

            if( ($editMode && $arrItemNumber[2] === $design) || ($questionnaireMode && $arrItemNumber[1] === $design) )
            {
                $arrValue['design']         = '<div class="color_circle cc-' . $designAlias . '"><span class="title">' . $designLabel . '</span></div>';
                $arrInputValue['design']    = $design;

//                $productStartImage = $this->getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs,true );
//                if( !$productStartImage )
//                {
                    $productStartImage = $designImage;
//                }
            }
        }
//        echo "<pre>"; print_r( $arrDesigns ); exit;
        if( $questionnaireMode )
        {
            $arrValue['tuning'] = 'Standardtuning (Gratis)';
            $arrInputValue['tuning'] = 'Standardtuning';
        }

        if( !$editMode && !$productStartImage )
        {
//            $productStartImage = IidoShopProductModel::findOneBy("itemNumber", $skiNumber )->overviewSRC;

            $productStartImage = $arrDesigns[0]['image'];
        }
//echo "<pre>"; print_r( $arrConfig ); exit;
        $arrConfig['woodCores'] = $woodCores;
        $arrConfig['flexs']     = $flexs;
        $arrConfig['keils']     = $keils;

        $arrConfig['default']['woodCore']   = $woodCores[0];
        $arrConfig['default']['keil']       = $keils[0];

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = ShopConfig::getCurrency();
        $objTemplate->productPrice  = ($intPrice ?: $objSki['articlePrices'][0]['price']);
        $objTemplate->productName   = ($strName ?: $objSki['name']);
        $objTemplate->productDesc   = $objSki['shortDescription1'];
        $objTemplate->productSlogan = $objSki['shortDescription2'];
        $objTemplate->productImage  = $productStartImage;
        $objTemplate->bindingImage  = $bindingImage;

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



    protected function getDesignStartImage( $skiNumber )
    {
        $arrProduct = $this->objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);

        if( is_array($arrProduct['articleImages']) && count($arrProduct['articleImages']) )
        {
            $strFirstPath   = '';
            $mainImagePath  = '';

            foreach( $arrProduct['articleImages'] as $artImage )
            {
                $strPath = $this->objApi->downloadArticleImage( $arrProduct['id'], $artImage['id'], $artImage['fileName'], $artImage['lastModifiedDate'] );

                if( !$strFirstPath )
                {
                    $strFirstPath = $strPath;
                    break;
                }

                if( $artImage['mainImage'] )
                {
//                            $imageTag   = $this->objApi->getImageTag( $strPath );
                    $imagePath  = ImageHelper::renderImagePath( $strPath );

                    $mainImagePath  = ImageHelper::renderImagePath( $strPath );
                    break;
                }
                else
                {
                    $arrImages[] = ImageHelper::renderImagePath( $strPath );
                }
            }

            if( $mainImagePath )
            {
                return $mainImagePath;
            }

            return $strFirstPath;
        }

        return false;
    }



    protected function getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs, $getSource = false )
    {
//        $imageTag   = '';
        $imagePath  = '';
//echo "<pre>";
        $arrImages      = array();
//        $arrProducts    = array();
//        $arrFlexs = array('XXX'=>array('articleNumber'=>'XXX'),'YYY'=>array('articleNumber'=>'YYY'),'ZZZ'=>array('articleNumber'=>'ZZZ'));
//echo "<pre>"; print_r( $arrLengths );
//echo "<br>";
//print_r( $arrFlexs );
//exit;
        $size = ShopHelper::getShortestSize( $arrLengths );
        $flex = ShopHelper::getMinFlex( $arrFlexs );

//        echo "<pre>";
//        print_R( $design );
//        echo "<br>";
//        print_r( $size );
//        echo "<br>";
//        print_r( $flex );
//        exit;
        $itemNumber     = $skiNumber . '.' . $design . '.' . $size . '.PP.' . $flex . '.__';
        $arrProducts    = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $itemNumber);

//        if( $design === "BA" )
//        {
//            echo "<pre>";
//            print_r( $itemNumber );
//            echo "<br>";
//            print_r( $arrProducts );
//            exit;
//        }

        if( count($arrProducts) )
        {
            foreach($arrProducts as $arrProduct)
            {
                $objProduct = ShopHelper::getProductObject( $arrProduct );

                if( $objProduct && is_array($objProduct->articleImages) && count($objProduct->articleImages) )
                {
                    $strFirstPath   = '';
                    $mainImagePath  = '';

                    foreach( $arrProduct['articleImages'] as $artImage )
                    {
                        $strPath = $this->objApi->downloadArticleImage( $arrProduct['id'], $artImage['id'], $artImage['fileName'] );

                        if( !$strFirstPath )
                        {
                            $strFirstPath = $strPath;
                            break;
                        }

                        if( $artImage['mainImage'] )
                        {
//                            $imageTag   = $this->objApi->getImageTag( $strPath );
                            $imagePath  = ImageHelper::renderImagePath( $strPath );

                            $mainImagePath  = ImageHelper::renderImagePath( $strPath );
                            break;
                        }
                        else
                        {
                            $arrImages[] = ImageHelper::renderImagePath( $strPath );
                        }
                    }

                    if( !$imagePath && $strFirstPath )
                    {
//                        $imageTag   = $this->objApi->getImageTag( $strFirstPath );
                        $imagePath  = ImageHelper::renderImagePath( $strFirstPath );
                    }

                    if( $mainImagePath )
                    {
                        $arrImages[] = $mainImagePath;

                        $imagePath = $mainImagePath;
                    }

                    if( $imagePath )
                    {
                        break;
                    }
                }
            }
        }

        if( $imagePath )
        {
            return $imagePath;
        }

        return '';

//        foreach( $arrLengths as $arrLength )
//        {
//            $lengthNum = $arrLength['articleNumber'];
//
//            foreach( $arrFlexs as $arrFlex )
//            {
//                $flexNum = $arrFlex['alias'];
//
//                $designItemNumber   = $skiNumber . '.' . $design . '.' . $lengthNum . '.' . $this->arrWoodCore[0] . '.' . $flexNum . '.__';
////                echo "<pre>"; print_r( $designItemNumber ); echo "</pre>";
//                $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $designItemNumber);
//
////                $arrProducts[ $designItemNumber ] = $arrProduct;
////print_r( $designItemNumber ); echo "<br>";
//
//                if( count($arrProducts) )
//                {
//                    foreach($arrProducts as $arrProduct)
//                    {
//                        if( is_array($arrProduct['articleImages']) && count($arrProduct['articleImages']) )
//                        {
////                            echo "<pre>";
////                            print_r( $arrProduct );
////                            exit;
//                            $strFirstPath   = '';
//                            $mainImagePath  = '';
//
//                            foreach( $arrProduct['articleImages'] as $artImage )
//                            {
////                        echo "<pre>"; print_r( $arrProduct ); exit;
//                                $strPath = $this->objApi->downloadArticleImage( $arrProduct['id'], $artImage['id'], $artImage['fileName'] );
//
//                                if( !$strFirstPath )
//                                {
//                                    $strFirstPath = $strPath;
//                                    break;
//                                }
//
//                                if( $artImage['mainImage'] )
//                                {
////                            $imageTag   = $this->objApi->getImageTag( $strPath );
//                                    $imagePath  = ImageHelper::renderImagePath( $strPath );
//
//                                    $mainImagePath  = ImageHelper::renderImagePath( $strPath );
//                                    break;
//                                }
//                                else
//                                {
//                                    $arrImages[] = ImageHelper::renderImagePath( $strPath );
//                                }
//                            }
//
//                            if( !$imagePath && $strFirstPath )
//                            {
////                        $imageTag   = $this->objApi->getImageTag( $strFirstPath );
//                                $imagePath  = ImageHelper::renderImagePath( $strFirstPath );
//                            }
//
//                            if( $mainImagePath )
//                            {
//                                $arrImages[] = $mainImagePath;
//
//                                $imagePath = $mainImagePath;
//                            }
//                        }
//
//                        if( $imagePath )
//                        {
//                            break;
//                        }
//                    }
//                }
//
////                $imageSRC           = IidoShopProductModel::findBy("itemNumber", $designItemNumber)->overviewSRC;
////
////                $objImage           = \FilesModel::findByPk( $imageSRC );
////
////                if( $objImage )
////                {
////                    if( $getSource )
////                    {
////                        return $imageSRC;
////                    }
////
////                    return $objImage->path;
////                }
//
//
//            }
//        }
//exit;
//        echo "<pre>";
//        print_r( $imagePath );
//        exit;
        if( $imagePath )
        {
            return $imagePath;
        }

        return '';
    }



    protected function getCategoryId( $skiNumber )
    {
//        $objAllSkis = IidoShopProductModel::findByArchive( $this->iidoShopArchive );
//
//        if( $objAllSkis )
//        {
//            while( $objAllSkis->next() )
//            {
//                if( preg_match('/^C\.' . $skiNumber . '/', $objAllSkis->itemNumber) )
//                {
//                    $categories = \StringUtil::deserialize( $objAllSkis->categories, TRUE );
//
//                    if( count($categories) )
//                    {
//                        return $categories[0];
//                    }
//                }
//            }
//        }

        if( !preg_match('/^C/', $skiNumber) )
        {
            $skiNumber = 'C.' . $skiNumber;
        }

        $objCategory = IidoShopProductCategoryModel::findOneBy('itemNumbers', $skiNumber);

        if( $objCategory )
        {
            return $objCategory->id;
        }

        return '';
    }



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



    protected function getConfigFromFile( $skiNumberRange, $skiNumber )
    {
        $writeToFile    = false;
        $fileName       = 'configurator-products-config-' . $skiNumberRange . '.json';
        $objFile        = new \File( 'assets/shop_tmp/' . $fileName );

        $arrConfig      = array();
        $designs        = array();
        $lengths        = array();
        $woodCores      = array();
        $flexs          = array();
        $keils          = array();
        $bindings       = array();

        if( !$objFile->exists() )
        {
            $writeToFile = true;
        }
        else
        {
            if( (time()-$this->fileLifetime) > $objFile->mtime )
            {
                $writeToFile = true;
            }
            else
            {
                $arrData    = json_decode( $objFile->getContent() );
                $arrConfig  = (array) $arrData->config;
//echo "<pre>"; print_r( $arrConfig); exit;
                $designs    = (array) $arrData->designs;
                $lengths    = (array) $arrData->lengths;
                $woodCores  = (array) $arrData->woodCores;
                $flexs      = (array) $arrData->flexs;
                $keils      = (array) $arrData->keils;

                $bindings   = (array) $arrData->bindings;
            }
        }

        if( $writeToFile )
        {
//            echo "<pre>"; print_r( $skiNumberRange ); echo "</pre>";
            $arrProducts = $this->getProductsFromFile( $skiNumberRange );

            $insertProducts = array();

            foreach( $arrProducts as $product )
            {
                $product = (array) $product;

                if( $product['active'] === "1" || $product['active'] === 1 || $product['active'] )
                {
                    $articleNumber      = $product['articleNumber'];

                    if( !in_array($articleNumber, $insertProducts) )
                    {
                        $insertProducts[] = $articleNumber;

                        $arrArticleNumber   = explode(".", $articleNumber);

                        $designs[]   = $arrArticleNumber[ 2 ];
                        $lengths[]   = $arrArticleNumber[ 3 ];
                        $woodCores[] = $arrArticleNumber[ 4 ];
                        $flexs[]     = $arrArticleNumber[ 5 ];
                        $keils[]     = $arrArticleNumber[ 6 ];
                        $bindings[]  = $arrArticleNumber[ 7 ];

                        $intProPrice = ShopHelper::getCurrentPrice($product);

                        $arrConfig['products'][] = array
                        (
                            'articleNumber'     => $articleNumber,
                            'price'             => $intProPrice
                        );

                        $proSkiNumber   = $skiNumber . '.' . $arrArticleNumber[ 2 ] . '.' . $arrArticleNumber[ 3 ]. '.' . $arrArticleNumber[ 4 ]. '.' . $arrArticleNumber[ 5 ]. '.' . $arrArticleNumber[ 6 ];
//                        echo "<pre>"; print_r( $proSkiNumber ); echo "</pre>";
                        $arrSkiProducts = $this->getProductsFromFile( $skiNumber, $proSkiNumber );

                        if( $arrSkiProducts )
                        {
                            foreach($arrSkiProducts as $skiProduct)
                            {
                                $skiProduct = (array) $skiProduct;

                                if( $skiProduct['active'] === "1" || $skiProduct['active'] === 1 || $skiProduct['active'] )
                                {
                                    if( !in_array($skiProduct['articleNumber'], $insertProducts) )
                                    {
                                        $insertProducts[] = $skiProduct['articleNumber'];

                                        $intPrice = ShopHelper::getCurrentPrice( $skiProduct );

                                        $arrConfig['products'][] = array
                                        (
                                            'articleNumber' => $skiProduct['articleNumber'] . '.none',
                                            'price'         => $intPrice
                                        );
                                    }

                                }
                            }
                        }
                    }
                }
            }

            $designs    = array_values(array_unique($designs));
            $lengths    = array_values(array_unique($lengths));
            $woodCores  = array_values(array_unique($woodCores));
            $flexs      = array_values(array_unique($flexs));
            $keils      = array_values(array_unique($keils));
            $bindings   = array_values(array_unique($bindings));

            $arrData = array
            (
                'config'        => $arrConfig,
                'designs'       => $designs,
                'lengths'       => $lengths,
                'woodCores'     => $woodCores,
                'flexs'         => $flexs,
                'keils'         => $keils,
                'bindings'      => $bindings,
            );
//echo "<pre>"; print_r( $arrConfig ); exit;
            $objFile->write( json_encode($arrData) );
            $objFile->close();
        }

        return [$arrConfig, $designs, $lengths, $woodCores, $flexs, $keils, $bindings];
    }



    protected function getProductsFromFile( $skiNumberRange, $skiNumber = '', $likeAddon = '' )
    {
        if( !preg_match('/^C./', $skiNumberRange) )
        {
            $arrParts       = explode(".", $skiNumberRange);
            $skiNumberRange = $arrParts[0];
        }

        $arrProducts    = array();
        $writeToFile    = false;
        $fileName       = 'configurator-products-' . $skiNumberRange . '.json';
        $objFile        = new \File( 'assets/shop_tmp/' . $fileName );
        $readFile       = false;

        if( !$objFile->exists() )
        {
            $writeToFile = true;
        }
        else
        {
            if( (time()-$this->fileLifetime) > $objFile->mtime )
            {
                $writeToFile = true;
            }
            else
            {
                $arrProducts = json_decode( $objFile->getContent() );
            }
        }

        if( $writeToFile )
        {
            $skiNumberRange = ShopHelper::getSearchSkiItemNumber($skiNumberRange, 'like');

//            if( $skiNumber )
//            {
//                $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '&pageSize=1000');
//            }
//            else
//            {
                $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '&pageSize=1000');

                if( count($arrProducts) === 1000 )
                {
                    $arrProducts2   = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '&pageSize=1000&page=2');
                    $arrProducts    = array_merge($arrProducts, $arrProducts2);
                }
//            }

            $objFile->write( json_encode($arrProducts) );
            $objFile->close();
        }
        else
        {
            $readFile = true;
        }

        if( ($skiNumber && $writeToFile) || $readFile )
        {
            if( $skiNumber )
            {
                $skiNumberRange = $skiNumber;
            }

            $objOpenFile    = new \File( 'system/tmp/' . $fileName );
            $arrProducts    = array();
            $arrAllProducts    = json_decode( $objOpenFile->getContent() );
//echo "<pre>";
//print_r( $skiNumberRange );
//echo "<br>";
//print_r( $arrAllProducts );
//exit;
            foreach( $arrAllProducts as $arrProduct)
            {
                if( is_array($arrProduct) )
                {
                    $itemNumber = $arrProduct['articleNumber'];
                }
                else
                {
                    $itemNumber = $arrProduct->articleNumber;
                }

                if( preg_match('/^' . $skiNumberRange . '/', $itemNumber) )
                {
                    $arrProducts[] = (array) $arrProduct;
                }
            }
//            echo "<pre>"; print_r( $arrProducts );
//            exit;
        }

        return $arrProducts;
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