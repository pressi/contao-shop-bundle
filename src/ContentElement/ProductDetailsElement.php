<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContentElement;


use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Helper\ApiHelper;
use Contao\Model\Collection;
use IIDO\ShopBundle\Helper\ShopFilesHelper;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Helper\ShopOrderHelper;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use HeimrichHannot\Ajax\Ajax;
use IIDO\ShopBundle\Ajax\ShopAjax;


class ProductDetailsElement extends \ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_iido_shop_productDetails';


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
     * article number parts
     *
     * @var array
     */
    protected $itemMode = array
    (
        'article',
        'design',
        'gender',
        'size'
    );



    public function generate()
    {
        if( TL_MODE === "BE" )
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard  = '### PRODUKT DETAILS ###';
            $objTemplate->title     = $this->headline;
            $objTemplate->id        = $this->id;
            $objTemplate->link      = $this->name;
            $objTemplate->href      = '';

            return $objTemplate->parse();
        }
        else
        {
            Ajax::runActiveAction('iidoShop', 'getAddToCartMessage', new ShopAjax($this));
            Ajax::runActiveAction('iidoShop', 'getAddToWatchlistMessage', new ShopAjax($this));
        }

//        $objApi = ApiHelper::getApiObject();
//        ShopOrderHelper::sendEmails( ShopOrderHelper::getOrder( 20 ), $objApi->runApiUrl('salesOrder/?orderNumber-eq=1123') );

        return parent::generate();
    }



    /**
     * Generate the content element
     */
    protected function compile()
    {
        \Controller::loadLanguageFile( "iido_shop_configurator" );
        \Controller::loadLanguageFile("iido_shop");

        $apiName        = ApiHelper::enableApis( true );
        $productUrlPath = ApiHelper::getUrlPath();
        $strMode        = \Input::get("mode");
        $strSubMode     = 'cart';

        $chooseInfos    = array();

        if($strMode === "edit-list")
        {
            $strMode    = 'edit';
            $strSubMode = 'watchlist';
        }

        $arrValue = $arrInputValue = array('design'=>'','gender'=>'','size'=>'');

        $arrProductIdOrAliasOrItemNumber    = explode("-", (\Config::get("useAutoItem") ? \Input::get("auto_item") : \Input::get( $productUrlPath )));
        $productIdOrAliasOrItemNumber       = array_pop($arrProductIdOrAliasOrItemNumber);

        if( $productIdOrAliasOrItemNumber )
        {
            if( $apiName )
            {
                $objApi         = ApiHelper::getApiObject( $apiName );
                /* @var $objApi \IIDO\ShopBundle\API\WeclappApi */
//            $productUrlPath = $objApi->getUrlPath();
                $objProduct     = $objApi->getProduct( $productIdOrAliasOrItemNumber );

                if( $objProduct )
                {
                    $arrItemNumber  = explode(".", $objProduct->itemNumber);

                    $this->objApi   = $objApi;

                    $arrValue['design'] = '<div class="color_circle cc-' . $objApi->getColorCode( $arrItemNumber[1] ) . '"></div>';

                    $arrInputValue['design'] = $arrItemNumber[1];
                }
            }
            else
            {
                $objProduct = IidoShopProductModel::findByIdOrAlias( $productIdOrAliasOrItemNumber );

                if( !$objProduct )
                {
                    $objProduct = IidoShopProductModel::findByItemNumber( $productIdOrAliasOrItemNumber );
                }

                $arrItemNumber  = explode(".", $objProduct->itemNumber);
            }

            $this->Template->noProduct  = FALSE;

            if( !$objProduct )
            {
                $this->Template->noProduct      = TRUE;
                $this->Template->message        = $GLOBALS['TL_LANG']['iido_shop']['noProductFound'];
                $this->Template->messageName    = $GLOBALS['TL_LANG']['iido_shop']['noProductFoundName'];
            }
            else
            {
                $arrLabel   = $GLOBALS['TL_LANG']['iido_shop']['label'];
                list($arrProducts, $arrConfig)  = $this->getProductVariants( $arrItemNumber[0] );

                foreach( $arrItemNumber as $key => $itemChoose )
                {
                    if( $key > 1 )
                    {
                        $chooseMode = $this->itemMode[ $key ];
                        $strValue   = $itemChoose;
                        $arrItems   = array();

                        $arrInputValue[ $chooseMode ] = $arrItemNumber[ $key ];

                        if( $chooseMode === "gender" )
                        {
                            $strValue = $arrLabel['gender'][ $strValue ];
                        }

                        if( count($arrConfig) )
                        {
                            foreach( $arrConfig[ $chooseMode . 's' ] as $modeConfig )
                            {
                                $strItemName = $modeConfig;

                                if( $chooseMode === "gender" )
                                {
                                    $strItemName = $arrLabel['gender'][ $modeConfig ];
                                }

                                $arrItems[] = array
                                (
                                    'name'          => $strItemName,
                                    'articleNumber' => $modeConfig
                                );
                            }
                        }

                        $chooseInfos[] = array
                        (
                            'mode'  => $chooseMode,
                            'label' => $arrLabel['chooser'][ $chooseMode ],
                            'value' => $strValue,
                            'items' => $arrItems
                        );
                    }
                }

                $arrDesignChooser = array();

                foreach($arrConfig['designs'] as $design)
                {
                    $strMode = 'ski';

                    if( !preg_match('/^C/', $arrItemNumber[0]) && !preg_match('/^S/', $arrItemNumber[0]) )
                    {
                        if( preg_match('/^A3/', $arrItemNumber[0]) )
                        {
                            $strMode = 'shirt';
                        }
                    }

                    $designAlias    = $objApi->getColorCode( $design );
                    $designLabel    = $objApi->getColorLabel( $design, $strMode );
                    $designImage    = $this->getDesignImage( $design, $arrItemNumber);

                    $arrDesignChooser[] = array
                    (
                        'alias'         => $designAlias,
                        'label'         => $designLabel,
                        'articleNumber' => $design,
                        'image'         => $designImage
                    );
                }


                $strCartLink        = '';
                $strWatchlistLink   = '';
                $objCategory        = false;

                if( $this->iidoShopCart )
                {
                    $strCartLink = \PageModel::findByPk( $this->iidoShopCart )->getFrontendUrl();
                }

                if( $this->iidoShopWatchlist )
                {
                    $strWatchlistLink = \PageModel::findByPk( $this->iidoShopWatchlist )->getFrontendUrl();
                }

                if( $objProduct )
                {
                    $arrCategories = \StringUtil::deserialize($objProduct->categories, TRUE);

                    foreach($arrCategories as $categoryID)
                    {
                        $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

                        if( $objCategory )
                        {
                            break;
                        }
                    }
                }

                if( !$objCategory )
                {
                    $objCategory = ShopHelper::getProductCategory( $arrItemNumber[0] );
                }

                $objProduct->itemNumberRange = $arrItemNumber[0];

                $this->Template->product    = $objProduct;
                $this->Template->label      = $GLOBALS['TL_LANG']['iido_shop_configurator']['label'];

                $this->Template->priceUnit      = ShopConfig::getCurrency();
                $this->Template->cartNum        = ShopConfig::getCartNum();
                $this->Template->cartLink       = $strCartLink;
                $this->Template->watchlistNum   = ShopConfig::getWatchlistNum();
                $this->Template->watchlistLink  = $strWatchlistLink;

                $this->Template->category       = $objCategory;
                $this->Template->catColor       = ($objCategory ? ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) ) : '');
                $this->Template->chooserValue   = $arrValue;
                $this->Template->chooserInputValue = $arrInputValue;
                $this->Template->arrItemNumber  = $arrItemNumber;

                $this->Template->mode           = $strMode;
                $this->Template->subMode        = $strSubMode;

                $this->Template->chooseInfos    = $chooseInfos;
                $this->Template->designs        = $arrDesignChooser;
                $this->Template->noDesigns      = (count($arrItemNumber) === 1);
                $this->Template->stepDetails    = $this->renderStepDetails( $objCategory, $objProduct );
            }
        }
        else
        {
            $this->Template->noProduct      = TRUE;
            $this->Template->message        = $GLOBALS['TL_LANG']['iido_shop']['noProductFound'];
            $this->Template->messageName    = $GLOBALS['TL_LANG']['iido_shop']['noProductFoundName'];
        }
    }



    /**
     * Render step detail infos
     *
     * @param       $objCategory
     * @param array $arrProduct
     *
     * @return string
     */
    protected function renderStepDetails( $objCategory, $arrProduct )
    {
        $arrProduct = (array) $arrProduct;
        $strContent = '';

        if( $arrProduct['longText'] )
        {
            $strContent = '<div class="ce_text intro"><div class="element-inside"><h2 class="headline text-center">' . $arrProduct['shortDescription1'] . '</h2>' . $arrProduct['longText'] . '</div></div>';
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



    protected function getProductVariants( $itemNumber )
    {
        $arrProducts    = array();
        $arrConfig      = array('designs'=>[],'genders'=>[],'sizes'=>[]);

        if( $itemNumber && strlen($itemNumber) )
        {
            $fileName       = 'shop-config-' . $itemNumber . '.json';
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
                foreach( $arrProducts as $arrProduct )
                {
                    $objProduct = ShopHelper::getProductObject( $arrProduct );

                    if( $objProduct )
                    {
                        $arrCurrItemNumber = explode(".", $objProduct->articleNumber);

                        if( $arrCurrItemNumber[1] )
                        {
                            $arrConfig['designs'][ $arrCurrItemNumber[1] ] = $arrCurrItemNumber[1];
                        }

                        if( $arrCurrItemNumber[2] )
                        {
                            $arrConfig['genders'][ $arrCurrItemNumber[2] ] = $arrCurrItemNumber[2];
                        }

                        if( $arrCurrItemNumber[3] )
                        {
                            $sizeOrderNumber = ShopHelper::getSizeOrderNumber( $arrCurrItemNumber[3] );
//                            echo "<pre>"; print_r( $sizeOrderNumber ); echo "</pre>";
                            $arrConfig['sizes'][ $sizeOrderNumber ] = $arrCurrItemNumber[3];
                        }

//                        if( $arrCurrItemNumber[4] )
//                        {
//                            $arrConfig['XXX'][ $arrCurrItemNumber[4] ] = $arrCurrItemNumber[4];
//                        }
                    }
                }

                ksort( $arrConfig['sizes'] );

//                echo "<pre>"; print_r( $arrConfig['sizes'] ); exit;

                $arrConfig['designs']   = array_values( $arrConfig['designs'] );
                $arrConfig['genders']   = array_values( $arrConfig['genders'] );
                $arrConfig['sizes']     = array_values( $arrConfig['sizes'] );

                $objFile->write( json_encode($arrConfig) );
                $objFile->close();
            }
        }

        return array($arrProducts, $arrConfig);
    }



    /**
     * Get API Products from API or file
     *
     * @param $skiNumber
     *
     * @return array|mixed
     * @throws \Exception
     */
    protected function getApiProductsFromFile( $itemNumber )
    {
        $fileName       = 'shop-products-' . $itemNumber . '.json';
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
            $arrProducts = $this->getApiProducts( $itemNumber . '%25' );

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

        return $arrProducts;
    }


    /**
     * Get design image
     *
     * @param        $design
     * @param        $arrItemNumber
     *
     * @return string
     * @throws \Exception
     */
    protected function getDesignImage( $design, $arrItemNumber )
    {
        $arrItemNumber[1] = $design;

        if( count($arrItemNumber) >= 4 )
        {
            $arrConfig = ShopFilesHelper::readConfigFile( $arrItemNumber[0] );

            $size = ShopHelper::getShortestProductSize( $arrConfig['sizes'] );

            $arrItemNumber[3] = $size;
        }

        $objProduct = $this->objApi->runApiUrl('article/?articleNumber-eq=' . implode(".", $arrItemNumber));
        $imagePath  = $this->objApi->getItemImage( (array) $objProduct );

        if( $imagePath )
        {
            $newImagePath = ImageHelper::getImagePath($imagePath, array(330, 350, 'box'));

            if( $newImagePath )
            {
                $imagePath = $newImagePath;
            }
        }

        return $imagePath;
    }
}