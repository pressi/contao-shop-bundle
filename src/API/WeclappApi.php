<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\API;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ColorHelper;
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Helper\PaymentHelper;
use IIDO\ShopBundle\Helper\ShippingHelper;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Helper\ShopOrderHelper;
use IIDO\ShopBundle\Model\IidoShopProductModel;
use IIDO\ShopBundle\Model\IidoShopStatisticQuestionnaireModel;


/**
 * Weclapp API
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class WeclappApi extends DefaultApi
{
    /**
     * API Name
     * @var string
     */
    protected $apiName = 'weclapp';


    /**
     * API URL Version
     *
     * @var string
     */
    var $version = 'v1';


    /**
     * Active Importer
     *
     * @var boolean
     */
    protected $activeImporter = true;



    /**
     * Local Image Path
     *
     * @var string
     */
    protected $localImagePath = 'files/weclapp/article/'; // save in customer folder??



    /**
     * Color Codes
     *
     * @var array
     */
    protected $colorCodes = array
    (
        'AB'    => array
        (
            'alias'     => 'aqua_black',
            'label'     => array
            (
                'ski' => 'Aqua on Black'
            )
        ),
        'BA'    => array
        (
            'alias'     => 'black_aqua',
            'label'     => array
            (
                'ski'       => 'Black on Aqua',
                'shirt'     => '{{iflng::de}}Logodruck Aqua{{iflng::en}}logo print aqua{{iflng}}'
            )
        ),

        'WB'    => array
        (
            'alias'     => 'white_black',
            'label'     => array
            (
                'ski' => 'White on Black'
            )
        ),
        'BW'    => array
        (
            'alias'     => 'black_white',
            'label'     => array
            (
                'ski' => 'Black on White'
            )
        ),

        'BB'    => array
        (
            'alias'     => 'black_black',
            'label'     => array
            (
                'ski' => 'Black on Black'
            )
        ),

        'CB'    => array
        (
            'alias'     => 'cblue_black',
            'label'     => array
            (
                'ski' => 'Crystal-Blue on Black'
            )
        ),
        'BC'    => array
        (
            'alias'     => 'black_cblue',
            'label'     => array
            (
                'ski'       => 'Black on Crystal-Blue',
                'shirt'     => '{{iflng::de}}Logodruck Crystal-Blue{{iflng::en}}logo print crystal blue{{iflng}}'
            )
        ),

        'YB'    => array
        (
            'alias'     => 'yellow_black',
            'label'     => array
            (
                'ski' => 'Yellow on Black'
            )
        ),
        'MB'    => array
        (
            'alias'     => 'magenta_black',
            'label'     => array
            (
                'ski' => 'Magenta on Black'
            )
        ),
        'BM'    => array
        (
            'alias'     => 'black_magenta',
            'label'     => array
            (
                'ski'       => 'Black on Magenta',
                'shirt'     => '{{iflng::de}}Logodruck Magenta{{iflng::en}}logo print magenta{{iflng}}'
            )
        ),

        'IB'    => array
        (
            'alias'     => 'ired_black',
            'label'     => array
            (
                'ski' => 'Indian-Red on Black'
            )
        ),
        'BI'    => array
        (
            'alias'     => 'black_ired',
            'label'     => array
            (
                'ski' => 'Black on Indian-Red'
            )
        ),

        'CBB'    => array
        (
            'alias'     => 'cblue_black',
            'label'     => array
            (
                'ski' => 'Crystal-Blue on Black'
            )
        ),
        'BCB'    => array
        (
            'alias'     => 'black_cblue',
            'label'     => array
            (
                'ski' => 'Black on Crystal-Blue'
            )
        ),
    );



    protected $flexRange = array
    (
        'superhard' => array
        (
            'label' => 'Superhard',
            'range' => array
            (
                'min'   => 0,
                'max'   => 65,
                'num'   => 58
            )
        ),

        'hard' => array
        (
            'label' => 'Hard',
            'range' => array
            (
                'min'   => 66,
                'max'   => 72,
                'num'   => 57
            )
        ),

        'medium' => array
        (
            'label' => 'Medium',
            'range' => array
            (
                'min'   => 73,
                'max'   => 77,
                'num'   => 56
            )
        ),

        'soft' => array
        (
            'label' => 'Soft',
            'range' => array
            (
                'min'   => 78,
                'max'   => 85,
                'num'   => 56
            )
        ),

        'supersoft' => array
        (
            'label' => 'Supersoft',
            'range' => array
            (
                'min'   => 82,
                'max'   => 90,
                'num'   => 55
            )
        )
    );



    protected $filterModes = array
    (
        'eq'        => 'equal',
        'ne'        => 'not equal',
        'lt'        => 'less than',
        'gt'        => 'greater than',
        'le'        => 'less equal',
        'ge'        => 'greater equal',
        'null'      => 'propery is null (the query parameter value is ignored and can be ommitted)',
        'notnull'   => 'propery is not null (the query parameter value is ignored and can be ommitted)',
        'like'      => 'like expression (supports % and _ as placeholders, similar to SQL LIKE)',
        'notlike'   => 'not like expression',
        'ilike'     => 'like expression, ignoring case',
        'notilike'  => 'not like expression, ignoring case',
        'in'        => 'the property value is in the specified list of values, the query parameter value must be a JSON array with the values in the correct type, for example <strong>?customerNumber-in=["1006","1007"]</strong>',
        'notin'     => 'the property value is not in the specified list of values'
    );



    /**
     * API Url
     *
     * @var string
     */
    protected $apiUrl;



    protected $groupedPer = 'colorAndGender';


    protected $arrMainProducts = array();



    protected $questionnaireAttributeIDs = array
    (
        'anwendungsbereich'     =>  array
        (
            'id'        => 158784,
            'type'      => 'list',
            'options'   => array
            (
                'mod.6s'     => 158785,
                'mod.7'      => 214939,
                'mod.8'      => 158786,
                'mod.9'      => 158787,
                'mod.11'     => 158788
            )
        ),

        'skifahrertyp' => array
        (
            'id'        => 44187,
            'type'      => 'list',
            'options'   => array
            (
                'typ1'      => 44188,
                'typ2'      => 44189,
                'typ3'      => 44190,
                'typ3+'     => 65568
            )
        ),

        'speedcheck' => array
        (
            'id'        => 158791,
            'type'      => 'list',
            'options'   => array
            (
                'speed1'    => 158792,
                'speed2'    => 158793,
                'speed3'    => 158794,
                'speed4'    => 158795
            )
        ),

        'technikanalyse' => array
        (
            'id'        => 158798,
            'type'      => 'list',
            'options'   => array
            (
                'technik1'  => 158799,
                'technik2'  => 158800,
                'technik3'  => 158801,
                'technik4'  => 158802
            )
        ),

        'piste' => array
        (
            'id'        => 158805,
            'type'      => 'list',
            'options'   => array
            (
                'flach'         => 158806,
                'mittelsteil'   => 158807,
                'steil'         => 158808,
                'extrem'        => 158809
            )
        ),

        'radius' => array
        (
            'id'        => 158812,
            'type'      => 'list',
            'options'   => array
            (
                'radiusA'   => 158813,
                'radiusB-'  => 214940,
                'radiusB+'  => 158814,
                'radiusC'   => 158815
            )
        ),

        'geschlecht' => array
        (
            'id'        => 158818,
            'type'      => 'list',
            'options'   => array
            (
                'männlich'  => 158820,
                'weiblich'  => 158819
            )
        ),

        'alter' => array
        (
            'id'        => 158823,
            'type'      => 'integer'
        ),

        'size' => array
        (
            'id'        => 40823,
            'type'      => 'integer'
        ),

        'weight' => array
        (
            'id'        => 44181,
            'type'      => 'integer'
        ),

        'shoe_size' => array
        (
            'id'        => 158826,
            'type'      => 'integer'
        ),

        'sole_length' => array
        (
            'id'        => 44184,
            'type'      => 'integer'
        ),

        'shoe_brand' => array
        (
            'id'        => 158829,
            'type'      => 'string'
        ),

        'shoe_model' => array
        (
            'id'        => 158832,
            'type'      => 'string'
        ),

        'location' => array
        (
            'id'        => 158835,
            'type'      => 'multiselect_list',
            'options'   => array
            (
                'asia'                  => 158843,
                'northernAlps'          => 158841,
                'southernAlps'          => 158842,
                'eastCost'              => 158838,
                'pacificNorthwest'      => 158836,
                'rockies'               => 158837,
                'australia'             => 158844,
                'scandinaviaCostal'     => 158839,
                'scandinaviaInterior'   => 158840,
                'southAmerica'          => 158845,

                'newZealand'            => 158844
            )
        ),

        'frequenzcheck' => array
        (
            'id'        => 158848,
            'type'      => 'list',
            'options'   => array
            (
                'frequenz1'     => 158849,
                'frequenz2'     => 158850,
                'frequenz3'     => 158851,
                'frequenz4'     => 158852
            )
        ),

        'week_hours_sport' => array
        (
            'id'        => 158855,
            'type'      => 'integer'
        ),

        'current_ski' => array
        (
            'id'        => 158858,
            'type'      => 'string'
        ),

        'current_ski_like' => array
        (
            'id'        => 158870,
            'type'      => 'string'
        ),

        'current_ski_dont-like' => array
        (
            'id'        => 158873,
            'type'      => 'string'
        ),

        'geheimnisse' => array
        (
            'id'        => 158876,
            'type'      => 'string'
        )

    );



    /**
     * Wood Core, available item number ranges
     *
     * @var array
     */
    protected $arrWoodCore = ['EP', 'PP'];


    protected $configFilePath   = 'assets/shop_tmp/';
    protected $configFilePrefix = 'v3-shop-config-';
//    protected $configFileLifetime = (60*120);
    protected $configFileLifetime = (7*24*60*60);



    /**
     * JSON, Config / Products file lifetime
     *
     * @var float|int
     */
//    protected $fileLifetime = (60*120);
    protected $fileLifetime = (7*24*60*60);



    public function __construct()
    {
    }



    public function getProductList( $itemNumber = '', $detailPageId = 0, $returnMainProducts = false, $groupedPer = '')
    {
        $arrProducts    = array();
        $productCount   = 0; //$this->runApiUrl( 'article/count' );
        $apiProducts    = ''; //$this->runApiUrl( 'article/?pageSize=1000' );
        $objDetailPage  = \PageModel::findByPk( $detailPageId );

        if( $groupedPer )
        {
            $this->groupedPer = $groupedPer;
        }

        $arrItemNumbers = explode(",", $itemNumber);

        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
        {
            $arrAllProducts = $this->getProductsFromItemNumbers( $arrItemNumbers );

            foreach($arrAllProducts as $arrProduct)
            {
                $arrCurrItemNumber = explode(".", $arrProduct['articleNumber']);

                if( count($arrCurrItemNumber) === 1 )
                {
                    $this->arrMainProducts[ $arrCurrItemNumber[0] ] = $arrProduct;
                }

                $objProduct = $this->getProductItem( $arrProduct, $objDetailPage );

                if( $objProduct )
                {
                    $addProduct = false;

                    if( $this->groupedPer === "colorAndGender" && count($arrCurrItemNumber) < 3 )
                    {
                        $this->groupedPer = 'color';
                    }

                    if( $this->groupedPer === 'color' )
                    {
                        $addProduct = true;
                        $itemNumber = $arrCurrItemNumber[0] . '.' . $arrCurrItemNumber[1];

                        if( in_array($itemNumber, $arrProducts) )
                        {
                            $addProduct = false;
                        }
                    }
                    elseif( $this->groupedPer === 'colorAndGender' )
                    {
                        $addProduct = true;
                        $itemNumber = $arrCurrItemNumber[0] . '.' . $arrCurrItemNumber[1] . '.' . $arrCurrItemNumber[2];

                        if( in_array($itemNumber, $arrProducts) )
                        {
                            $addProduct = false;
                        }
                    }
                    elseif( $this->groupedPer === 'article' )
                    {
                        $itemNumber = $arrCurrItemNumber[0];

                        if( count($arrCurrItemNumber) === 1 )
                        {
                            $addProduct = true;
                        }
                    }
                    else
                    {
                        $addProduct = true;
                    }

                    if( $addProduct )
                    {
                        $arrProducts[ $itemNumber ] = $objProduct;
                    }
                }
            }
        }

        return $returnMainProducts ? array($arrProducts, $this->arrMainProducts) : $arrProducts;
    }


    public function getProductListV2( $itemNumber = '', $detailPageId = 0, $returnMainProducts = false, $groupedPer = '', $returnConfig = false, $liveRun = false)
    {
        $arrProducts    = array();
        $arrConfig      = array('designs'=>[],'genders'=>[],'sizes'=>[]);

        $objDetailPage  = \PageModel::findByPk( $detailPageId );

        if( $groupedPer )
        {
            $this->groupedPer = $groupedPer;
        }

        $arrItemNumbers = explode(",", $itemNumber);

        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
        {
            $arrAllApiProducts = $this->getProductsItemNumbersFromFile( $arrItemNumbers );

            foreach($arrAllApiProducts as $arrProduct)
            {
                $arrCurrItemNumber = explode(".", $arrProduct['articleNumber']);

                if( count($arrCurrItemNumber) === 1 )
                {
                    $this->arrMainProducts[ $arrCurrItemNumber[0] ] = $arrProduct;
                }

                $objProduct = $this->getProductItem( $arrProduct, $objDetailPage );

                if( $objProduct )
                {
                    $addProduct = false;

                    if( $this->groupedPer === "colorAndGender" && count($arrCurrItemNumber) < 3 )
                    {
                        $this->groupedPer = 'color';
                    }

                    if( $this->groupedPer === 'color' )
                    {
                        $addProduct = true;
                        $itemNumber = $arrCurrItemNumber[0] . '.' . $arrCurrItemNumber[1];

                        if( in_array($itemNumber, $arrProducts) )
                        {
                            $addProduct = false;
                        }
                    }
                    elseif( $this->groupedPer === 'colorAndGender' )
                    {
                        $addProduct = true;
                        $itemNumber = $arrCurrItemNumber[0] . '.' . $arrCurrItemNumber[1] . '.' . $arrCurrItemNumber[2];

                        if( in_array($itemNumber, $arrProducts) )
                        {
                            $addProduct = false;
                        }
                    }
                    elseif( $this->groupedPer === 'article' )
                    {
                        $itemNumber = $arrCurrItemNumber[0];

                        if( count($arrCurrItemNumber) === 1 )
                        {
                            $addProduct = true;
                        }
                    }
                    else
                    {
                        $addProduct = true;
                    }

                    if( $addProduct )
                    {
                        $arrProducts[ $itemNumber ] = $objProduct;
                    }
                }
            }
        }

        return $returnMainProducts ? array($arrProducts, $this->arrMainProducts) : $arrProducts;
    }



    public function getProduct( $productIdOrAliasOrItemNumber )
    {
        $objProduct = false;

        if( strlen($productIdOrAliasOrItemNumber) )
        {
            $objProduct = IidoShopProductModel::findByIdOrAlias( $productIdOrAliasOrItemNumber );

            if( !$objProduct )
            {
                $objProduct = IidoShopProductModel::findByItemNumber( $productIdOrAliasOrItemNumber );
            }
        }

        if( !$objProduct )
        {
            $arrProduct = $this->runApiUrl('article/id/' . $productIdOrAliasOrItemNumber);
//echo "<pre>"; print_r( $arrProduct ); exit;
            if( $arrProduct )
            {
                $objProduct = $objProduct = IidoShopProductModel::findByItemNumber( $arrProduct['articleNumber'] );
            }

            if( !$objProduct )
            {
                $objProduct = new \stdClass();
            }
        }

        if( $objProduct )
        {
            if( !$arrProduct )
            {
                $arrProduct = $this->runApiUrl('article/?articleNumber-eq=' . $objProduct->itemNumber);
            }

            if( $arrProduct['active'] === "1" || $arrProduct['active'] === 1 || $arrProduct['active'] )
            {
//                $objProduct->imageTag       = ImageHelper::getImageTag( $objProduct->overviewSRC );
//                $objProduct->imageDetailTag = ($objProduct->detailSRC ? ImageHelper::getImageTag( $objProduct->detailSRC ) : '');
//                $objProduct->price          = ShopHelper::renderPrice( $arrProduct['articlePrices'][0]['price'] );
//                $objProduct->apiProduct     = $arrProduct;
////                $objProduct->detailsLink    = $detailLink;

                $objItemProduct = $this->getProductItem( $arrProduct );

                $arrItemNumber  = explode(".", $arrProduct['articleNumber']);
                $arrMainProduct = array();

                if( count($arrItemNumber) > 1 )
                {
                    $arrMainProduct = $this->runApiUrl('article?articleNumber-eq=' . $arrItemNumber[0] );
                }
//echo "<pre>";
//                print_r( $this->runApiUrl('article?articleNumber-eq=' . $arrItemNumber[0], 'HUU') );
//                echo "<br>";
//                print_r( $objItemProduct );
//                exit;
                if( $objProduct instanceof \stdClass )
                {
                    $objProduct = (object) $objItemProduct;

                    $objProduct->itemNumber     = $objItemProduct['articleNumber'];
                    $objProduct->apiProduct     = $objItemProduct;

                    $objProduct->name           = $arrMainProduct['name'] ?:$objItemProduct['name'];

                    $objProduct->shortDescription    = $arrMainProduct['shortDescription1']?:$objItemProduct['shortDescription1'];
                    $objProduct->shortDescription2   = $arrMainProduct['shortDescription2']?:$objItemProduct['shortDescription2'];
                }
                else
                {
                    $objProduct->imageTag       = $objItemProduct['imageTag'];
                    $objProduct->imagePath      = $objItemProduct['imagePath'];

                    $objProduct->imageTagOverview       = $objItemProduct['imageTagOverview'];
                    $objProduct->imagePathOverview      = $objItemProduct['imagePathOverview'];

                    $objProduct->price          = $objItemProduct['price'];
                    $objProduct->detailsLink    = $objItemProduct['detailsLink'];

                    $objProduct->imageDetailTag = $objItemProduct['imageDetailTag'];
                    $objProduct->images         = $objItemProduct['images'];
                    $objProduct->apiProduct     = $objItemProduct;

                    $objProduct->name           = $arrMainProduct['name'] ?:$objItemProduct['name'];

                    $objProduct->shortDescription    = $arrMainProduct['shortDescription1']?:$objItemProduct['shortDescription1'];
                    $objProduct->shortDescription2   = $arrMainProduct['shortDescription2']?:$objItemProduct['shortDescription2'];
                }

//                echo "<pre>";
//                print_r( $objProduct );
//                echo "<br>";
//                print_r( $objItemProduct );
//                exit;
            }
            else
            {
                $objProduct = false;
            }
        }

        return $objProduct;
    }



    public function getProductItem( $arrProduct, $objDetailPage = false )
    {
        $strLang        = BasicHelper::getLanguage();

        if( $arrProduct['active'] === "1" || $arrProduct['active'] === 1 || $arrProduct['active'] )
        {
            $arrItemNumber = explode(".", $arrProduct['articleNumber']);

            if( strlen($arrItemNumber[0]) <= 4 )
            {
                return false;
            }

            \Controller::loadLanguageFile("iido_shop");

            $arrLabel   = $GLOBALS['TL_LANG']['iido_shop']['label'];

            $imageTag   = '';
            $imagePath  = '';

            $imageTagOverview   = '';
            $imagePathOverview  = '';

            $detailLink = '';

            $arrImages  = array();

            $objProduct = IidoShopProductModel::findByItemNumber( $arrProduct['articleNumber'] );
//
//                if( $objProduct )
//                {
//                    $imageTag   = ImageHelper::getImageTag( $objProduct->overviewSRC );
//                }

            if( is_array($arrProduct['articleImages']) && count($arrProduct['articleImages']) )
            {
                $strFirstPath   = '';
                $mainImagePath  = '';

                foreach( $arrProduct['articleImages'] as $artImage )
                {
                    $strPath = $this->downloadArticleImage( $arrProduct['id'], $artImage['id'], $artImage['fileName'] );

                    if( !$strFirstPath )
                    {
                        $strFirstPath = $strPath;
                    }

                    if( $artImage['mainImage'] )
                    {
                        $imagePathOverview = ImageHelper::getImagePath( $strPath, array('', 150, 'proportional') );
                        $imageTagOverview   = $this->getImageTag( $imagePathOverview );

                        $imageTag   = $this->getImageTag( $strPath );
                        $imagePath  = ImageHelper::renderImagePath( $strPath );

                        $mainImagePath  = ImageHelper::renderImagePath( $strPath );
                    }
                    else
                    {
                        $arrImages[] = ImageHelper::renderImagePath( $strPath );
                    }
                }

                if( !$imageTag && $strFirstPath )
                {
                    $imageTag   = $this->getImageTag( $strFirstPath );
                    $imagePath  = ImageHelper::renderImagePath( $strFirstPath );
                }

                if( $mainImagePath )
                {
                    $arrImages[] = $mainImagePath;
                }
            }

            if( $objDetailPage )
            {
                $productItemUrlPath = ($objProduct ? $objProduct->alias : ShopHelper::renderUrlPath( $arrProduct['name'] ) . '-' . $arrProduct['id']);

                $detailLink = $objDetailPage->getFrontendUrl((\Config::get('useAutoItem') ? '' : '/' . $this->productUrlPath[ $strLang ]) . '/' . $productItemUrlPath);
            }

            $mainProduct    = $this->arrMainProducts[ $arrItemNumber[0] ];
            $strMainName    = $mainProduct['name'];
            $strSubName     = '';

            if( $this->groupedPer === 'color' )
            {
                $strSubName = $this->getColorLabel( $arrItemNumber[1] );
            }
            elseif( $this->groupedPer === 'colorAndGender' )
            {
                $strMode = 'ski';

                if( !preg_match('/^C/', $arrItemNumber[0]) && !preg_match('/^S/', $arrItemNumber[0]) )
                {
                    if( preg_match('/^A3/', $arrItemNumber[0]) )
                    {
                        $strMode = 'shirt';
                    }
                }

                $strSubName = $this->getColorLabel( $arrItemNumber[1], $strMode );

                if( count($arrItemNumber) > 2 )
                {
                    $strSubName .= ', ' . $arrLabel['gender'][ $arrItemNumber[2] ];
                }
            }

            $objCategory = ShopHelper::getProductCategory( $arrProduct['articleNumber'] );

            $arrProduct['name']                 = $strMainName ?: $arrProduct['name'];
            $arrProduct['subName']              = $strSubName;

            $arrProduct['imageTag']             = $imageTag;
            $arrProduct['imagePath']            = $imagePath;

            $arrProduct['imageTagOverview']     = $imageTagOverview;
            $arrProduct['imagePathOverview']    = $imagePathOverview;

            $arrProduct['price']            = ShopHelper::renderPrice( ShopHelper::getCurrentPrice( $arrProduct ), true );
            $arrProduct['detailsLink']      = $detailLink;

            $arrProduct['imageDetailTag']   = ($objProduct->detailSRC ? ImageHelper::getImageTag( $objProduct->detailSRC ) : $imageTag);
            $arrProduct['images']           = $arrImages;

            $arrProduct['catColor']         = ColorHelper::compileColor( \StringUtil::deserialize($objCategory->color, TRUE) );

            return $arrProduct;
        }

        return false;
    }



    public function getColorCode( $colorKey )
    {
        return $this->colorCodes[ $colorKey ]['alias'];
    }



    public function getColorLabel( $colorKey, $mode = 'ski' )
    {
        return $this->colorCodes[ $colorKey ]['label'][ $mode ];
    }



    public function getColor( $colorKey )
    {
        return $this->colorCodes[ $colorKey ];
    }



    public function getFlex()
    {
        return $this->flexRange;
    }



    public function getFlexName( $flexKey )
    {
        foreach($this->flexRange as $key => $arrRange )
        {
            if( (int) $arrRange['range']['num'] === (int) $flexKey )
            {
                return $arrRange['label'];
            }
        }

        return false;
    }



    public function runApiUrl( $urlParams, $returnVar = '', $method = 'GET', $postFields = '' )
    {
        $auth = [
            'Content-Type: application/json',
            'AuthenticationToken: '. $this->getToken()
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $this->getURL() . $urlParams);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if( $method === "POST" || $method === "PUT" )
        {
            curl_setopt($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields );
        }

        $out = curl_exec($ch);
        curl_close($ch);
//if( $returnVar === "HUU" || $method === "POST" ) { echo "<pre>"; print_r( $out ); exit; }
        if( preg_match('/downloadArticleImage/', $urlParams) )
        {
            return $out;
        }

        $arrOutput = json_decode($out, TRUE);

        if( $arrOutput['error'] )
        {
//            echo "<pre>";
//            print_r( $this->runApiUrl('customer/?customerNumber-eq=C1328') );
//            exit;
//            echo "<pre>ERROR:<br>"; print_r($postFields); echo "<br>"; print_R( $arrOutput ); exit;
            $objEmail = new \Email();

            $objEmail->from     = 'website@original.plus';
            $objEmail->fromName = 'ORIGNAL+ WEBSHOP';

            $objEmail->subject  = 'Original+ - Weclapp Error';

            $objEmail->html     = ShopHelper::renderErrorEmailContent($arrOutput, $urlParams, $postFields );

            $objEmail->sendTo("mail@stephanpressl.at");
            
            return false; //$arrOutput['error'];
        }

        $arrReturn = $returnVar ? $arrOutput[ $returnVar ] : ((isset($arrOutput['result'])) ? $arrOutput['result'] : $arrOutput);

        if( count($arrReturn) === 1 && preg_match('/\-eq/', $urlParams) )
        {
            $arrReturn = $arrReturn[0];
        }

//        if( trim($arrReturn) === "not found" )
//        {
//            $arrReturn = false;
//        }

        return $arrReturn;
    }



    public function downloadArticleImage( $articleId, $imageId, $imageName, $imageCreated = 0 )
    {
        $articleImagePath = BasicHelper::getRootDir() . '/' . $this->localImagePath . $articleId . '/';

        if( $imageCreated > 0 && strlen($imageCreated) > 10 )
        {
            $imageCreated = substr($imageCreated, 0, -(strlen($imageCreated) - 10));
        }

        if( !file_exists( $articleImagePath .  $imageName) || filemtime($articleImagePath . $imageName) < $imageCreated )
        {
            if( !is_dir($articleImagePath) )
            {
                mkdir( $articleImagePath );
            }

            $objImage = $this->runApiUrl('article/id/' . $articleId . '/downloadArticleImage?articleImageId=' . $imageId, false );

            file_put_contents( $articleImagePath . $imageName, $objImage);

            $strLocalPath = $this->localImagePath . $articleId . '/' . $imageName;

            if (\Dbafs::shouldBeSynchronized( $strLocalPath ))
            {
                $objModel = \FilesModel::findByPath( $strLocalPath );

                if ($objModel === null)
                {
                    $objModel = \Dbafs::addResource( $strLocalPath );
                }

                // Update the hash of the target folder
                \Dbafs::updateFolderHashes( $this->localImagePath . $articleId );
            }
        }

        return $this->localImagePath . $articleId . '/' . $imageName;
    }



    public function getItemImage( $arrItem, $runTwice = false )
    {

        if( preg_match('/^C./', $arrItem['articleNumber']) || preg_match('/^S/', $arrItem['articleNumber']) )
        {
            $arrItemNumber = explode(".", $arrItem['articleNumber']);

            if( $arrItemNumber[0] === 'C' )
            {
                if( $arrItemNumber[7] )
                {
                    $shortSkiNumber = $arrItemNumber[0] . '.' . $arrItemNumber[1] . '.' . $arrItemNumber[2];
                    list($arrSizes, $shortestSize) = ShopHelper::getSizeNumbers($shortSkiNumber, new self());
                    $arrItemNumber[3] = $shortestSize;

                    $bindingNumber = $arrItemNumber[7];

                    unset( $arrItemNumber[5] );
                    unset( $arrItemNumber[6] );
                    unset( $arrItemNumber[7] );

                    $skiNumber = implode(".", $arrItemNumber);
                    list($arrFlexs, $arrItems) = ShopHelper::getFlexNumbers($skiNumber, new self(), $bindingNumber);

                    $minFlex = ShopHelper::getMaxMinFlexNum($arrFlexs, 'min');

                    $arrNewItemNumber = explode(".", $arrItem['articleNumber']);

                    $arrNewItemNumber[3] = $shortestSize;
                    $arrNewItemNumber[5] = $minFlex;
                    $arrNewItemNumber[6] = '__';

                    $newItemNumber = implode(".", $arrNewItemNumber);

                    foreach($arrItems as $objItem)
                    {
                        $objItem = ShopHelper::getProductObject( (array) $objItem);

                        if( $objItem && is_array($objItem->articleImages) && count($objItem->articleImages) )
                        {
                            $arrObjItemNumber = explode(".", $objItem->articleNumber);
                            $arrObjItemNumber[6] = '__';

                            if( trim(implode(".", $arrObjItemNumber)) === trim($newItemNumber) )
                            {
                                $arrItem = (array) $objItem;
                                break;
                            }
                        }
                    }
                }
            }
        }
//        echo "<pre>"; print_r( $arrItem ); exit;

        if( is_array($arrItem['articleImages']) && count($arrItem['articleImages']) )
        {
            $strFirstPath   = '';
            $mainImagePath  = '';

            foreach( $arrItem['articleImages'] as $artImage )
            {
                $strPath = $this->downloadArticleImage( $arrItem['id'], $artImage['id'], $artImage['fileName'] );

//                if( !$strFirstPath )
//                {
//                    $strFirstPath = $strPath;
//                    break;
//                }

                if( $artImage['mainImage'] )
                {
                    $imagePath      = ImageHelper::renderImagePath( $strPath );
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
                $imagePath = ImageHelper::renderImagePath( $strFirstPath );
            }

            if( $mainImagePath )
            {
                $arrImages[] = $mainImagePath;

                $imagePath = $mainImagePath;
            }

            return $imagePath;
        }

        if( $runTwice )
        {
            return $this->getItemImageTwice( $arrItem );
        }
        else
        {
            $itemNumber     = $arrItem['articleNumber'];
            $arrItemNumber  = explode(".", $itemNumber);

            $key = 3;

            if( $arrItemNumber[0] === 'C' )
            {
                $key = 4;
                array_shift($arrItemNumber);
            }

            unset($arrItemNumber[ $key ]); // TODO: ohne Flex Key = 3
            unset($arrItemNumber[ ($key - 1) ]); // Flex
            unset($arrItemNumber[ ($key - 2) ]); // Length

            $newItemNumber = implode(".", $arrItemNumber) . '.___.___';

            $arrNewItems = $this->runApiUrl("article/?articleNumber-ilike=" . $newItemNumber);

            if( is_array($arrNewItems) && count($arrNewItems) )
            {
                foreach($arrNewItems as $newItem)
                {
                    if( is_array($newItem['articleImages']) && count($newItem['articleImages']) )
                    {
                        $imagePath = $this->getItemImage( $newItem, true );

                        if( $imagePath )
                        {
                            return $imagePath;
                        }
                    }
                }
            }
        }

        return $this->getItemImageTwice( $arrItem );
    }



    public function getSkiImage( array $arrItem, $realItemNumber = '' )
    {
        $arrItemNumber  = explode(".", $arrItem['articleNumber'] );
        $skiNumber      = $arrItemNumber[0];
        $skiNumberRange = $skiNumber;
        $indexAdd       = 0;

        if( $arrItemNumber[0] === "C" )
        {
            $skiNumber  = $arrItemNumber[1];
            $indexAdd   = 1;

            $skiNumberRange = $skiNumberRange . '.' . $arrItemNumber[1];

            unset( $arrItemNumber[0] );
            unset( $arrItemNumber[1] );

            unset( $arrItemNumber[6] );
        }
        else
        {
            $skiNumberRange = 'C.' . $skiNumber;

            unset( $arrItemNumber[0] );
            unset( $arrItemNumber[5] );
        }

        $flexIndex      = (4 + $indexAdd);
        $lengthIndex    = (2 + $indexAdd);

        $bindingIndex   = 7;

        $skiItemNumber      = false;
        $binding            = $arrItemNumber[ $bindingIndex ];
        unset( $arrItemNumber[ $bindingIndex ] );

        $configFileName     = 'shop-configurator-config-' . $skiNumber . '.json';
        $productFileName    = 'shop-configurator-products-' . $skiNumberRange . '.json';

        $objConfigFile      = new \File( 'assets/shop_tmp/' . $configFileName );
        $objProductFile     = new \File( 'assets/shop_tmp/' . $productFileName );

        if( $objConfigFile->exists() )
        {
            $arrConfig      = json_decode( $objConfigFile->getContent(), TRUE );

            $shortestFlex   = ShopHelper::getMaxMinFlexNum( $arrConfig['flexs'], 'min');
            $shortestLength = ShopHelper::getShortestSize( $arrConfig['lengths'] );

            $arrItemNumber[ $flexIndex ]    = $shortestFlex;
            $arrItemNumber[ $lengthIndex ]  = $shortestLength;

            $skiItemNumber = implode(".", $arrItemNumber);
        }

        if( $objProductFile->exists() && $skiItemNumber )
        {
            $arrProducts        = json_decode( $objProductFile->getContent(), TRUE );
            $checkBinding       = false;
            $objProduct         = false;

            if( $binding && $binding !== "none" )
            {
                $skiItemNumber  = $skiNumberRange . '.' . $skiItemNumber;
                $checkBinding   = true;
            }
            else
            {
                $skiItemNumber = $skiNumber . '.' . $skiItemNumber;
            }

            foreach($arrProducts as $arrProduct)
            {
                if( preg_match('/^' . $skiItemNumber . '/', $arrProduct['articleNumber']) )
                {
                    if( $checkBinding )
                    {
                        if( preg_match('/' . $binding . '$/', $arrProduct['articleNumber']) )
                        {
                            $objProduct = $arrProduct;
                            break;
                        }
                    }
                    else
                    {
                        $objProduct = $arrProduct;
                        break;
                    }
                }
            }

            if( $objProduct )
            {
                $strImage = $this->getItemImage( (array) $objProduct );

                if( $strImage )
                {
                    return $strImage;
                }
            }
        }
    }



    protected function getItemImageTwice( $arrItem )
    {
        $itemNumber     = $arrItem['articleNumber'];
        $arrItemNumber  = explode(".", $itemNumber);

        if( $arrItemNumber[0] === 'C' )
        {
            array_shift($arrItemNumber);
        }

        $arrNewItem = $this->runApiUrl("article/?articleNumber-eq=" . $arrItemNumber[0]);

        if( is_array($arrNewItem) && !empty($arrNewItem) )
        {
            if( is_array($arrNewItem['articleImages']) && count($arrNewItem['articleImages']) )
            {
                $imagePath = $this->getItemImage( $arrNewItem, true );

                if( $imagePath )
                {
                    return $imagePath;
                }
            }
        }

        return false;
    }



    public function getImageTag( $strPath, $arrSize = array() )
    {
        if( file_exists($strPath) )
        {
            if( count($arrSize) )
            {
                $arrSize = getimagesize($strPath);
            }

            return '<img src="' . $strPath . '" ' . $arrSize[3] . '>';
        }

        return false;
    }



    protected function getURL()
    {
        $prefix = BundleConfig::getTableFieldPrefix();
        $tenant = \Config::get( $prefix . 'weclappTenant' );

        return 'https://' . $tenant . '.weclapp.com/webapp/api/' . $this->version . '/';
    }



    protected function getToken()
    {
        $prefix = BundleConfig::getTableFieldPrefix();
        return \Config::get( $prefix . 'weclappToken' );
    }



    public function getProductID( $articleNumber )
    {
        $product = $this->runApiUrl("article?articleNumber-eq=" . $articleNumber . '&properties=id');

        return $product['id'];
    }



    public function getProductArray( $articleNumber )
    {
        $product = $this->runApiUrl("article?articleNumber-eq=" . $articleNumber);

        return $product;
    }



    public function getProductPrice( $articleNumber )
    {
        $product = $this->runApiUrl("articlePrice?articleNumber-eq=" . $articleNumber );

        return ShopHelper::getCurrentPrice( (array) $product );
    }



    protected function getProductsFromItemNumbers( $arrItemNumbers )
    {
        $arrProducts = array();

        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
        {
            foreach($arrItemNumbers as $intItemNumber)
            {
                if( $intItemNumber )
                {
                    $intItemNumber  = preg_replace('/\*/', '%25', $intItemNumber);
                    $apiProducts    = $this->getApiProducts( $intItemNumber );

                    if( count($apiProducts) )
                    {
                        foreach($apiProducts as $arrApiProduct)
                        {
                            $arrProducts[ $arrApiProduct['articleNumber'] ] = $arrApiProduct;
                        }

                        ksort( $arrProducts, SORT_REGULAR );
                    }
                }
            }
        }

        return $arrProducts;
    }



    protected function getProductsItemNumbersFromFile( $arrItemNumbers )
    {
        $arrProducts    = array();

        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
        {
            foreach($arrItemNumbers as $intItemNumber)
            {
                $intItemNumber  = preg_replace('/\*/', '%25', $intItemNumber);
                $apiProducts    = $this->getApiProductsFromFile( $intItemNumber );

                if( count($apiProducts) )
                {
                    $arrProducts = array_merge($arrProducts, $apiProducts);
                }
            }
        }

        return $arrProducts;


//        $fileName       = 'shop-products-' . $intItemNumber . '.json';
//        $objFile        = new \File( 'assets/shop_tmp/' . $fileName );
//        $writeToFile    = true;
//
//        if( $objFile->exists() )
//        {
//            if( (time() - $this->fileLifetime) < $objFile->mtime )
//            {
//                $writeToFile = false;
//                $arrApiProducts = json_decode( $objFile->getContent(), TRUE );
//            }
//        }
//
//        if( $writeToFile )
//        {
//            $arrProducts = $this->getApiProducts( $intItemNumber . '%25' );
//
//            $objFile->write( json_encode($arrProducts) );
//            $objFile->close();
//        }
//
//
//        $arrProducts = array();
//
//        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
//        {
//            foreach($arrItemNumbers as $intItemNumber)
//            {
//                if( $intItemNumber )
//                {
//                    $intItemNumber  = preg_replace('/\*/', '%25', $intItemNumber);
//                    $apiProducts    = $this->getApiProducts( $intItemNumber );
//
//                    if( count($apiProducts) )
//                    {
//                        foreach($apiProducts as $arrApiProduct)
//                        {
//                            $arrProducts[ $arrApiProduct['articleNumber'] ] = $arrApiProduct;
//                        }
//
//                        ksort( $arrProducts, SORT_REGULAR );
//                    }
//                }
//            }
//        }
//
//        return $arrProducts;
    }



    protected function getApiProducts( $itemNumber, $page = 1, $properties = '' )
    {
        $addon = '';

        if( $page > 1 )
        {
            $addon = '&page=' . $page;
        }

        if( $properties )
        {
            $addon .= '&properties=' . $properties;
        }

        $arrProducts = $this->runApiUrl('article/?articleNumber-ilike=' . $itemNumber . '&pageSize=1000' . $addon);

        if( count($arrProducts) === 1000 )
        {
            $page = ($page + 1);

            $arrProducts2   = $this->getApiProducts( $itemNumber, $page, $properties);
            $arrProducts    = array_merge($arrProducts, $arrProducts2);
        }

        return $arrProducts;
    }



    public function getApiProductsFromFile( $itemNumber )
    {
        $fileName       = 'shop-products-' . $itemNumber . '.json';
        $objFile        = new \File( 'assets/shop_tmp/' . $fileName );
        $arrProducts    = array();
        $writeToFile    = true;

        if( $objFile->exists() )
        {
            if( (time() - $this->fileLifetime) < $objFile->mtime )
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



    public function addNewOrder( $objOrder )
    {
        $orderItems = array();
        $totalPrice = 0;

        // CUSTOMER SEARCH
        $search = 'customer?firstName-like=' . urlencode($objOrder->firstname);
        $search .= '&lastName-like=' . urlencode($objOrder->lastname);
        $search .= '&email-like=' . $objOrder->email;

        $addNewCustomer = false;
        $objCustomer    = false;
        $arrCustomers   = $this->runApiUrl( $search );

        if( count($arrCustomers) )
        {
//                    echo "<pre>"; print_r("EXITS CUSTOMER"); echo "</pre>";
            $sameAddress = false;

            foreach($arrCustomers as $arrCustomer)
            {
                $arrCustomerAddresses = $arrCustomer['addresses'];

                foreach($arrCustomerAddresses as $arrCustomerAddress)
                {
                    if( $arrCustomerAddress['street1'] === $objOrder->street )
                    {
                        $sameAddress = true;
                        $objCustomer = $arrCustomer;
                        break;
                    }
                }

                if( $sameAddress )
                {
                    break;
                }
            }

            if( !$sameAddress )
            {
                $objCustomer = $arrCustomers[0];

                $objCustomer['addresses'][] = array
                (
                    'street1'       => $objOrder->street,
                    'zipcode'       => $objOrder->postal,
                    'city'          => $objOrder->city,
                    'countryCode'   => strtoupper($objOrder->country),

                    'invoiceAddress'    => true,
                    'deliveryAddress'   => !$objOrder->otherShippingAddress,
                );

                if( $objOrder->otherShippingAddress )
                {
                    $objCustomer['addresses'][] = array
                    (
                        'street1'       => $objOrder->shipping_street,
                        'zipcode'       => $objOrder->shipping_postal,
                        'city'          => $objOrder->shipping_city,
                        'countryCode'   => strtoupper($objOrder->shipping_country),

                        'deliveryAddress'   => true
                    );
                }
//                        echo "<pre>"; print_r("UPDATE CUSTOMER"); echo "</pre>";
//                echo "<pre>"; print_r( $objCustomer );
//                echo "<br>";
//                print_r( json_encode($objCustomer) );
//                exit;
                $objCustomer = $this->runApiUrl('customer/id/' . $objCustomer['id'], '', 'PUT', json_encode($objCustomer));
//                echo "<pre>"; print_r( $objCustomer ); exit;
            }
            else
            {
//                echo "<pre>"; print_r( $objCustomer );
//                echo "<br>";
//                print_r( !$objCustomer );
//                exit;
                if( !$objCustomer )
                {
                    $addNewCustomer = true;
                }
            }

//            echo "<pre>";
//            print_r( $arrCustomers );
//            echo "<br>";
//            print_r( $sameAddress );
//            echo "<br>";
//            print_r( $addNewCustomer );
//            echo "<br>";
//            print_r( $objCustomer );
//            echo "<br>";
//            exit;
        }
        else
        {
            $addNewCustomer = true;
        }

        if( $addNewCustomer )
        {
//                    echo "<pre>"; print_r("NEW CUSTOMER"); echo "</pre>";
            $arrCustomerData = array
            (
                'firstName' => ShopOrderHelper::getFirstname( $objOrder ),
                'lastName'  => ShopOrderHelper::getLastname( $objOrder ),
                'email'     => $objOrder->email,
                'phone'     => $objOrder->phone,

                'salesChannel' => 'GROSS1',
                'partyType' => 'PERSON',

                'addresses' => array
                (
                    array
                    (
                        'street1'       => $objOrder->street,
                        'zipcode'       => $objOrder->postal,
                        'city'          => $objOrder->city,
                        'countryCode'   => $objOrder->country,

                        'primeAddress'      => true,
                        'invoiceAddress'    => true,
                        'deliveryAddress'   => !$objOrder->otherShippingAddress,
                    )
                )
            );

            if( $objOrder->otherShippingAddress )
            {
                $arrCustomerData['addresses'][] = array
                (
                    'street1'       => $objOrder->shipping_street,
                    'zipcode'       => $objOrder->shipping_postal,
                    'city'          => $objOrder->shipping_city,
                    'countryCode'   => $objOrder->shipping_country,

                    'deliveryAddress'   => true
                );
            }

            $objCustomer = $this->runApiUrl('customer', '', 'POST', json_encode($arrCustomerData));
        }

//                $arrCustomer = $objApi->runApiUrl('customer', '', 'POST', json_encode($arrCustomerData));
//                $objCustomer = $objApi->runApiUrl('customer?customerNumber-eq=C1328');

//exit;
        foreach( \StringUtil::deserialize($objOrder->items, TRUE) as $item )
        {
            $orderItems[] = array
            (
                'articleId'         => $item['apiID'],
                'articleNumber'     => preg_replace('/.none$/', '', ($item['realArticleNumber']?:$item['articleNumber'])),
                'quantity'          => $item['quantity'],
                'title'             => $item['name'],

                'grossAmount'         => $item['singlePrice'],
                'grossAmountInCompanyCurrency' => $item['singlePrice'],

//                        'unitPrice'         => $item['singlePrice'],
//                        'unitPriceInCompanyCurrency' => $item['singlePrice'],

//                        'netAmount'         => $item['singlePrice'],
//                        'netAmountInCompanyCurrency' => $item['singlePrice'],
            );

            $totalPrice = ($totalPrice + $item['totalPrice']);
        }

        $arrName            = explode(" ", $objOrder->name);
        $arrShippingName    = explode(" ", $objOrder->shipping_name);

        $firstname          = $arrName[0];
        $shippingFirstname  = count($arrShippingName) ? $arrShippingName[0] : $firstname;

        array_shift($arrName);

        if( count($arrShippingName) )
        {
            array_shift($arrShippingName);
        }

        $lastname           = implode(" ", $arrName);
        $shippingLastname   = count($arrShippingName) ? implode(" ", implode(" ", $arrShippingName)) : $lastname;

        $recordAddress = array
        (
            'firstName'     => $firstname,
            'lastName'      => $lastname,
            'city'          => $objOrder->city,
            'street1'       => $objOrder->street,
            'zipcode'       => $objOrder->postal,
            'countryCode'   => strtoupper($objOrder->country)
        );

        $arrShippingItems = array();

        $objPayment     = PaymentHelper::getObject( $objOrder->paymentMethod );
        $objShipping    = ShippingHelper::getObject( $objOrder->shippingMethod );
//echo "<pre>"; print_r( $objOrder ); exit;
        foreach( \StringUtil::deserialize($objOrder->shippingItems, TRUE) as $shippingItem )
        {
            $arrShippingItems[] = array
            (
                'articleId' => $shippingItem['apiID'],
                'grossAmount' => $shippingItem['price'],
                'grossAmountInCompanyCurrency' => $shippingItem['price']
            );
        }
//echo "<pre>";
//        print_r( $this->runApiUrl('article/id/' . $shippingItem['apiID']) );

//        print_R( $arrShippingItems ); exit;
        $arrApiOrder = array
        (
            'createdDate'           => time() . '000',
            'commercialLanguage'    => 'de',

            'customerId'            => $objCustomer['id'],
            'customerNumber'        => $objCustomer['customerNumber'], //'C1328',

//                    'deliveryAddress' => array
//                    (
////                        'firstName'     => $objOrder->shipping_name     ?   : $objOrder->name,
//                        'firstName'     => $shippingFirstname,
//                        'lastName'      => $shippingLastname,
//                        'city'          => $objOrder->shipping_city     ?   : $objOrder->city,
//                        'street1'       => $objOrder->shipping_street   ?   : $objOrder->street,
//                        'zipcode'       => $objOrder->shipping_postal   ?   : $objOrder->postal,
//                        'countryCode'   => strtoupper($objOrder->shipping_country ?: $objOrder->country),
//                    ),

//                    'invoiceAddress'    => $recordAddress,
//                    'recordAddress'     => $recordAddress,

            'orderItems'            => $orderItems,
            'orderDate'             => ($objOrder->tstamp?:time()) . '000',

            'netAmount'                     => $totalPrice,
            'netAmountInCompanyCurrency'    => $totalPrice,

            'paid'                  => $objPayment->type === "paypal" ? '1' : '',
            'paymentMethodId'       => $objPayment->apiMethod,

            'recordCurrencyId'      => 248,
            'recordCurrencyName'    => 'EUR',

            'salesChannel'          => 'GROSS1',
            'salesOrderPaymentType' => 'STANDARD',

            'termOfPaymentId'       => 1878,
            'termOfPaymentName'     => 'net sofort',

            'shipmentMethodId'      => $objShipping->apiMethod,

            'shippingCostItems'     => $arrShippingItems,

            'headerDiscount'        => 0 // RABATT - GUTSCHEINE > ShopOrderHelper::getVoucherDiscount( $voucherCode, $cartPrice);
        );


        $newDeliveryAddress = ShopOrderHelper::checkDeliveryAddress($objCustomer, $objOrder);

        if( $newDeliveryAddress && is_array($newDeliveryAddress) )
        {
            $arrApiOrder['deliveryAddress'] = $newDeliveryAddress;
        }

        $newBillingAddress = ShopOrderHelper::checkBillingAddress($objCustomer, $objOrder);

        if( $newBillingAddress && is_array($newBillingAddress) )
        {
            $arrApiOrder['invoiceAddress'] = $newBillingAddress;
        }

        $objApiOrder = $this->runApiUrl("salesOrder", '', 'POST', json_encode($arrApiOrder));

        $this->addQuestionnaireDataToWeclapp( $objCustomer );

        return $objApiOrder;
    }



    public function addQuestionnaireDataToWeclapp( $arrCustomer )
    {
        $arrQuestData   = array();
//        $arrQuests      = array();

        foreach( $_COOKIE as $cookieKey => $cookieValue)
        {
            if( preg_match('/^iido_shopQuestionnaire_/', $cookieKey) )
            {
                $questID = (int) preg_replace('/^iido_shopQuestionnaire_/', '', $cookieKey);

                $objQuests = IidoShopStatisticQuestionnaireModel::findOneBy(array('questionnaire=?', 'userID=?'), array($questID, $cookieValue));

                if( $objQuests->language === BasicHelper::getLanguage() )
                {
                    $arrQuestData = json_decode($objQuests->questionnaireData, TRUE);
                    break;
                }

//                $arrQuests[ $questID ] = $cookieValue;
            }
        }

        $changed = FALSE;

        if( count($arrQuestData) )
        {
            foreach($arrQuestData as $fieldName => $fieldValue)
            {
                if( !$fieldValue )
                {
                    continue;
                }

                $attrType       = $this->questionnaireAttributeIDs[ $fieldName ]['type'];
                $attrID         = $this->questionnaireAttributeIDs[ $fieldName ]['id'];
                $attrOptions    = $this->questionnaireAttributeIDs[ $fieldName ]['options'];

                $key            = $this->checkCustomerAttributeKey($attrID, $arrCustomer);

                $attributeData = array
                (
                    'attributeDefinitionId' => $attrID
                );

                switch( $attrType )
                {
                    case "integer":
                        $attributeData['numberValue'] = (int) $fieldValue;
                        break;

                    case "list":
                        $attributeData['selectedValueId'] = $attrOptions[ $fieldValue ];
                        break;

                    case "multiselect_list":
                        $arrValue           = explode(",", $fieldValue);
                        $arrSelectedValues  = array();

                        foreach($arrValue as $valueKey)
                        {
                            $arrSelectedValues[] = array('id'=>$attrOptions[ $valueKey ]);
                        }

                        $attributeData['selectedValues'] = $arrSelectedValues;
                        break;

                    default:
                    case "string":
                    $attributeData['stringValue'] = $fieldValue;
                        break;
                }

                if( $key === 'XXX' )
                {
                    $arrCustomer['customAttributes'][] = $attributeData;
                }
                else
                {
                    $arrCustomer['customAttributes'][ $key ] = $attributeData;
                }

                $changed = TRUE;
            }
        }

        if( $changed )
        {
            $this->runApiUrl('customer/id/' . $arrCustomer['id'], '', 'PUT', json_encode($arrCustomer));
        }
    }



    protected function checkCustomerAttributeKey( $attributeID, $arrCustomer )
    {
        $key = 'XXX';

        foreach( $arrCustomer['customAttributes'] as $attrKey => $attrValue)
        {
            if( (int) $attrValue['attributeDefinitionId'] === (int) $attributeID )
            {
                $key = $attrKey;
                break;
            }
        }

        return $key;
    }



    public function getConfiguratorConfig( $skiNumber )
    {
        $arrConfig      = ['products'=>[],'tunings'=>[],'bindings'=>[],'default'=>['design'=>'','length'=>'','flex'=>'','binding'=>'','woodCore'=>'','keil'=>'','tuning'=>''],'config'=>['designs'=>[],'bindings'=>[],'lengths'=>[],'flexs'=>[],'keils'=>[],'woodCores'=>[],'tunings'=>[]]];
        $fileName       = preg_replace('/shop/', 'shop-configurator', $this->configFilePrefix) . $skiNumber . '.json';
        $objFile        = new \File( $this->configFilePath . $fileName );
        $writeToFile    = true;

        if( $objFile->exists() )
        {
            if( (time() - $this->configFileLifetime) < $objFile->mtime )
            {
                $writeToFile = false;
                $arrConfig = json_decode( $objFile->getContent(), TRUE );

//                echo "<pre>"; print_r( filesize( $objFile->path ) ); exit;
            }
        }

        if( $writeToFile )
        {
            $arrProducts    = array();
            $arrApiProducts = $this->getApiProducts( '%25' . $skiNumber . '%25', 1, 'id,active,name,articlePrices,articleNumber,description,articleImages,longText,shortDescription1,shortDescription2,matchCode' );

            if( count($arrApiProducts) )
            {
                $arrDesigns     = array();
                $arrLengths     = array();
                $arrFlexs       = array();
                $arrKeils       = array();
                $arrWoodCores   = array();
                $arrAllBindings = array();
                $arrBindings    = array();
                $arrAllTunings  = array();
                $arrTunings     = array();

                $arrApiTuningProducts = $this->getApiProducts( 'K000_', 1, 'id,name,active,articlePrices,articleNumber,description' );

                if( count($arrApiTuningProducts) )
                {
                    foreach($arrApiTuningProducts as $apiTuningProduct)
                    {
                        $arrTuning = array
                        (
                            'id'                => $apiTuningProduct['id'],
                            'name'              => $apiTuningProduct['name'],
                            'active'            => $apiTuningProduct['active'],
                            'articleNumber'     => $apiTuningProduct['articleNumber'],
                            'price'             => ShopHelper::getCurrentPrice( $apiTuningProduct ),
                            'description'       => $apiTuningProduct['description'],
                            'meta'          => $this->getMetaData( $apiTuningProduct['articleNumber'] )
                        );

                        $isActive = ($apiTuningProduct['active'] === "1" || $apiTuningProduct['active'] === 1 || $apiTuningProduct['active']);

                        $arrAllTunings[ $apiTuningProduct['articleNumber'] ] = $arrTuning;

                        if( $isActive )
                        {
                            $arrTunings[] = $arrTuning;
                        }
                    }

                    if( count($arrTunings) )
                    {
                        $arrConfig['config']['tunings'] = $arrTunings;
                        $arrConfig['default']['tuning'] = $arrTunings[0]['articleNumber'];
                    }

                    if( count($arrAllTunings) )
                    {
                        $arrConfig['tunings'] = $arrAllTunings;
                    }
                }

                $arrApiBindingProducts = $this->getApiProducts( 'B0%25', 1, 'id,name,active,articlePrices,articleNumber,description' );

                if( count($arrApiBindingProducts) )
                {
                    foreach($arrApiBindingProducts as $apiBindingProduct)
                    {
                        $arrAllBindings[ $apiBindingProduct['articleNumber'] ] = array
                        (
                            'id'            => $apiBindingProduct['id'],
                            'name'          => $apiBindingProduct['name'],
                            'active'        => $apiBindingProduct['active'],
                            'price'         => ShopHelper::getCurrentPrice( $apiBindingProduct ),
                            'articleNumber' => $apiBindingProduct['articleNumber'],
                            'description'   => $apiBindingProduct['description'],
                            'meta'          => $this->getMetaData( $apiBindingProduct['articleNumber'] )
                        );
                    }

                    if( count($arrAllBindings) )
                    {
                        $arrConfig['bindings'] = $arrAllBindings;
                    }
                }

                foreach($arrApiProducts as $apiProduct )
                {
                    $arrProducts[] = array
                    (
                        'id'                => $apiProduct['id'],
                        'active'            => $apiProduct['active'],
                        'name'              => $apiProduct['name'],
                        'articleNumber'     => $apiProduct['articleNumber'],
                        'price'             => ShopHelper::getCurrentPrice( $apiProduct ),
                        'images'            => $apiProduct['articleImages'],
                        'image'             => ShopHelper::getApiProductMainImage( $apiProduct, $this ),
                        'description'       => $apiProduct['description'],
                        'longText'          => $apiProduct['longText'],
                        'shortDescription1' => $apiProduct['shortDescription1'],
                        'shortDescription2' => $apiProduct['shortDescription2'],
                        'matchCode'         => $apiProduct['matchCode'],
                        'meta'              => $this->getMetaData( $apiProduct['articleNumber'] )
                    );

                    $isActive = ($apiProduct['active'] === "1" || $apiProduct['active'] === 1 || $apiProduct['active']);

                    if( preg_match('/^C./', $apiProduct['articleNumber']) && $isActive )
                    {
                        $arrItemNumber = explode(".", $apiProduct['articleNumber']);

                        $designNumber   = $arrItemNumber[2];
                        $lengthNumber   = $arrItemNumber[3];
                        $woodCoreNumber = $arrItemNumber[4];
                        $flexNumber     = $arrItemNumber[5];
                        $keilNumber     = $arrItemNumber[6];
                        $bindingNumber  = $arrItemNumber[7];

                        $arrDesigns[ $designNumber ]        = $designNumber;
                        $arrLengths[ $lengthNumber ]        = $lengthNumber;
                        $arrFlexs[ $flexNumber ]            = $flexNumber;
                        $arrKeils[ $keilNumber ]            = $keilNumber;
                        $arrWoodCores[ $woodCoreNumber ]    = $woodCoreNumber;

                        if( $bindingNumber )
                        {
                            $sortingNumber = ShopHelper::renderBindingOrderNumber( $arrAllBindings[ $bindingNumber ]['name'] );
                            $arrBindings[ $sortingNumber ]  = $bindingNumber;
                        }
                    }
                }

                if( count($arrProducts) )
                {
                    $arrConfig['products'] = $arrProducts;

                    if( count($arrDesigns) )
                    {
                        $arrConfig['config']['designs'] = array_values($arrDesigns);
                        $arrConfig['default']['design'] = array_shift($arrDesigns );
                    }

                    if( count($arrLengths) )
                    {
                        $arrConfig['config']['lengths'] = array_values($arrLengths);
                        $arrConfig['default']['length'] = array_shift($arrLengths);
                    }

                    if( count($arrFlexs) )
                    {
                        $arrConfig['config']['flexs'] = array_values($arrFlexs);
                        $arrConfig['default']['flex'] = array_shift($arrFlexs );
                    }

                    if( count($arrKeils) )
                    {
                        $arrConfig['config']['keils'] = array_values($arrKeils);
                        $arrConfig['default']['keil'] = array_shift($arrKeils);
                    }

                    if( count($arrWoodCores) )
                    {
                        $arrConfig['config']['woodCores'] = array_values($arrWoodCores);
                        $arrConfig['default']['woodCore'] = $this->arrWoodCore[0];
                    }

                    if( count($arrBindings) )
                    {
                        krsort($arrBindings);

                        $arrConfig['config']['bindings'] = array_values($arrBindings);
                        $arrConfig['default']['binding'] = 'none';
                    }
                }
            }

            $objFile->write( json_encode($arrConfig) );
            $objFile->close();
        }

        return $arrConfig;
    }



    protected function getMetaData( $itemNumber )
    {
        $arrMeta = array();

        $row = 1;
        if( ($handle = fopen("files/original-plus/Uploads/Shop/EN-produkttexte-website.csv", "r")) !== FALSE )
        {
            while( ($data = fgetcsv($handle, 1000, ";")) !== FALSE )
            {
                if( $row > 1 )
                {
                    if( $data[0] === $itemNumber )
                    {
                        $arrMeta['en'] = array
                        (
                            'shortDescription1' => (($data[1] === '-') ? '' : $data[1]),
                            'shortDescription2' => (($data[2] === '-') ? '' : $data[2]),
                            'description'       => (($data[3] === '-') ? '' : $data[3]),
                            'longText'          => (($data[4] === '-') ? '' : $data[4]),
                        );
                    }
                }

                $row++;
            }

            fclose($handle);
        }

        return $arrMeta;
    }



    public function getApiProductFromFile( $itemNumber, $tuningNumber = '' )
    {
        $arrItemNumber  = explode(".", $itemNumber);
        $searchNumber   = $arrItemNumber[0];
        $objTuning      = false;

        if( $searchNumber === "C" )
        {
            $searchNumber = $arrItemNumber[1];
        }

        if( preg_match('/^C./', $searchNumber) || preg_match('/^S/', $searchNumber) )
        {
            $arrConfig = $this->getConfiguratorConfig( $searchNumber );

            if( $tuningNumber )
            {
                foreach($arrConfig['tunings'] as $configTuning)
                {
                    if( $configTuning['articleNumber'] === $tuningNumber )
                    {
                        $objTuning = $configTuning;
                        break;
                    }
                }
            }

            foreach($arrConfig['products'] as $configProduct)
            {
                if( $configProduct['articleNumber'] === $itemNumber )
                {
                    if( $tuningNumber && $objTuning)
                    {
                        $configProduct['tuning'] = $objTuning;
                    }

                    return $configProduct;
                }
            }
        }
        else
        {
            $arrProducts = $this->getApiProductsFromFile( $searchNumber );

            foreach( $arrProducts as $arrProduct)
            {
                if( $arrProduct['articleNumber'] === $itemNumber )
                {
                    return $arrProduct;
                }
            }
        }

        return false;
    }


}