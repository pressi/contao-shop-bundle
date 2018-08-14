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
        $objApi     = $this->objApi;

        $strName            = '';
        $intPrice           = 0;
        $strMode            = '';
        $currentItemNumber  = '';
        $editMode           = false;
        $arrItemNumber      = array();
        $arrLangLabels      = $GLOBALS['TL_LANG']['iido_shop_configurator']['label'];

        $arrValue = $arrInputValue = array
        (
            'design'    => '',
            'binding'   => '',
            'length'    => '',
            'flex'      => '',
            'tuning'    => ''
        );

        $arrLabels = $arrLangLabels;
        $arrLabels['extraLink'] = $this->iidoShopExtraLinkLabel;

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
            $flex               = \Input::post("flex");
            $strSubMode         = \Input::post("SUBMODE");

            $arrItemNumber      = explode(".", $currentItemNumber);

            $skiNumber          = array_shift(explode(".", preg_replace('/^C./', '', $currentItemNumber)));
            $currentItem        = $objApi->runApiUrl('article/?articleNumber-eq=' . ShopHelper::getRealItemNumber($currentItemNumber, $objApi));
            //TODO: run articlePrice/ID

            $arrValue['tuning']         = ($tuning ? 'Standardtuning (Gratis)' : '');
            $arrInputValue['tuning']    = $tuning;

            $intPrice           = $currentItem['articlePrices'][0]['price'];

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
        list($arrConfig, $designs, $lengths, $bindings) = $this->getConfigFromFile( $skiNumberRange, $skiNumber );

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

            if( $editMode && $length === $arrItemNumber[3])
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
        foreach($objApi->getFlex() as $flexAlias => $arrCurrFlex)
        {
            $currFlex = $arrCurrFlex;

            $currFlex['title'] = $arrCurrFlex['label'];
            $currFlex['alias'] = $flexAlias;

            $arrFlexs[] = $currFlex;

            if( !isset($arrConfig['default']['flex']) )
            {
                $arrConfig['default']['flex'] = "XXX"; //$flexAlias;
            }

            if( $editMode )
            {
                if( $flexAlias === $flex )
                {
                    $arrValue['flex']       = $flexAlias;
                    $arrInputValue['flex']  = $flexAlias;
                }
            }
        }

        foreach($bindings as $binding)
        {
            $productBinding = $objApi->runApiUrl('article/?articleNumber-eq=' . $binding);

            if( $productBinding['active'] === "1" || $productBinding['active'] === 1 || $productBinding['active'] )
            {
                $strImage = $objApi->getItemImage( $productBinding );

                if( !$strImage )
                {
                    $bindingImageSRC    = IidoShopProductModel::findBy("itemNumber", $binding)->overviewSRC;
                    $objImage           = \FilesModel::findByPk( $bindingImageSRC );

                    $strImage           = ($objImage ? $objImage->path : '');
                }

                $arrBindings[] = array
                (
                    'title'         => $productBinding['name'],
                    'description'   => $productBinding['description'],
                    'articleNumber' => $binding,
                    'image'         => $strImage
                );

                if( !isset($arrConfig['default']['binding']) )
                {
                    $arrConfig['default']['binding'] = "none"; //$binding;
                }

                if( $editMode && $arrItemNumber[5] === $binding )
                {
                    $arrValue['binding']        = $productBinding['name'];
                    $arrInputValue['binding']   = $binding;

                    $bindingImage = ImageHelper::getImageTag($bindingImageSRC, array(), true);
                }
            }
        }
        $productStartImage = $this->getDesignStartImage($skiNumber);

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

            if( $editMode && $arrItemNumber[2] === $design )
            {
                $arrValue['design']         = '<div class="color_circle cc-' . $designAlias . '"><span class="title">' . $designLabel . '</span></div>';
                $arrInputValue['design']    = $design;

//                $productStartImage = $this->getDesignImage( $design, $skiNumber, $arrLengths, $arrFlexs,true );
                if( !$productStartImage )
                {
                    $productStartImage = $designImage;
                }
            }
        }

        if( !$editMode && !$productStartImage )
        {
//            $productStartImage = IidoShopProductModel::findOneBy("itemNumber", $skiNumber )->overviewSRC;

            $productStartImage = $arrDesigns[0]['image'];
        }

        $objTemplate->designs       = $arrDesigns;
        $objTemplate->lengths       = $arrLengths;
        $objTemplate->flexs         = $arrFlexs;
        $objTemplate->bindings      = $arrBindings;

        $objTemplate->tunings       = $arrTunings;

        $objTemplate->arrConfig     = $arrConfig;

        $objTemplate->priceUnit     = ShopConfig::getCurrency(); //'€'; //ShopConfig; TODO: get from shop config!! TODO: add the config module!!
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
        $arrFlexs = array('XXX'=>array('articleNumber'=>'XXX'),'YYY'=>array('articleNumber'=>'YYY'),'ZZZ'=>array('articleNumber'=>'ZZZ'));

        foreach( $arrLengths as $arrLength )
        {
            $lengthNum = $arrLength['articleNumber'];

            foreach( $arrFlexs as $arrFlex )
            {
                $flexNum = $arrFlex['articleNumber'];

                $designItemNumber   = $skiNumber . '.' . $design . '.' . $lengthNum . '.' . $flexNum;

                $arrProduct = $this->objApi->runApiUrl('article/?articleNumber-eq=' . $designItemNumber);

//                $arrProducts[ $designItemNumber ] = $arrProduct;
//print_r( $designItemNumber ); echo "<br>";
                if( is_array($arrProduct['articleImages']) && count($arrProduct['articleImages']) )
                {
                    $strFirstPath   = '';
                    $mainImagePath  = '';

                    foreach( $arrProduct['articleImages'] as $artImage )
                    {
//                        echo "<pre>"; print_r( $arrProduct ); exit;
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
                }

//                $imageSRC           = IidoShopProductModel::findBy("itemNumber", $designItemNumber)->overviewSRC;
//
//                $objImage           = \FilesModel::findByPk( $imageSRC );
//
//                if( $objImage )
//                {
//                    if( $getSource )
//                    {
//                        return $imageSRC;
//                    }
//
//                    return $objImage->path;
//                }


            }
        }
//exit;
        if( $imagePath )
        {
            return $imagePath;
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
        $objFile        = new \File( 'system/tmp/' . $fileName );

        $arrConfig      = array();
        $designs        = array();
        $lengths        = array();
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

                $designs    = (array) $arrData->designs;
                $lengths    = (array) $arrData->lengths;
                $bindings   = (array) $arrData->bindings;
            }
        }

        if( $writeToFile )
        {
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
//                    $flexs[]     = $arrArticleNumber[ 4 ];
                        $bindings[]  = $arrArticleNumber[ 5 ];

                        $intProPrice = $product['articlePrices'][0]->price;

                        if( !$intProPrice && is_array($product['articlePrices'][0]) )
                        {
                            $intProPrice = $product['articlePrices'][0]['price'];
                        }

                        $arrConfig['products'][] = array
                        (
                            'articleNumber'     => $articleNumber,
                            'price'             => $intProPrice
                        );

                        $proSkiNumber   = $skiNumber . '.' . $arrArticleNumber[ 2 ] . '.' . $arrArticleNumber[ 3 ];
                        $arrSkiProducts = $this->getProductsFromFile( $proSkiNumber, '.___' );

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

                                        $intPrice = $skiProduct['articlePrices'][0]->price;

                                        if( !$intPrice && is_array($skiProduct['articlePrices'][0]) )
                                        {
                                            $intPrice = $skiProduct['articlePrices'][0]['price'];
                                        }

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

            $arrData = array
            (
                'config'        => $arrConfig,
                'designs'       => $designs,
                'lengths'       => $lengths,
                'bindings'      => $bindings
            );

            $objFile->write( json_encode($arrData) );
            $objFile->close();
        }

        return [$arrConfig, $designs, $lengths, $bindings];
    }



    protected function getProductsFromFile( $skiNumberRange, $likeAddon = '' )
    {
        $writeToFile    = false;
        $fileName       = 'configurator-products-' . $skiNumberRange . '.json';
        $objFile        = new \File( 'system/tmp/' . $fileName );

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
            if( $likeAddon )
            {
                $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . $likeAddon);
            }
            else
            {
                $arrProducts = $this->objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumberRange . '%25&pageSize=1000');
            }

            $objFile->write( json_encode($arrProducts) );
            $objFile->close();
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