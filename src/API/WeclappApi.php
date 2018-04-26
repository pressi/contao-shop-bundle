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
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Helper\ShopHelper;
use IIDO\ShopBundle\Model\IidoShopProductModel;


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
    protected $localImagePath = 'files/weclapp/article/';



    /**
     * Color Codes
     *
     * @var array
     */
    protected $colorCodes = array
    (
        'AB'    => 'aqua_black',
        'BA'    => 'black_aqua',

        'BB'    => 'black_black',
        'BC'    => 'black_cblue',

        'BW'    => 'black_white',
        'WB'    => 'white_black',

        'BCB'   => 'black_cblue',
        'CBB'   => 'cblue_black',

        'BY'    => 'black_yellow',
        'YB'    => 'yellow_black',

        'MB'    => 'magenta_black',
        'BM'    => 'black_magenta',
    );


    /**
     * Flex Codes
     *
     * @var array
     */
    protected $flexCode = array
    (
        'XXX'   => array
        (
            'label' => 'Soft',
            'range' => 0
        ),

        'YYY'   => array
        (
            'label' => 'Medium',
            'range' => 50
        ),

        'ZZZ'   => array
        (
            'label' => 'Stiff',
            'range' => 100
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



    public function __construct()
    {
    }



    public function getProductList( $itemNumber = '', $detailPageId = 0)
    {
        $arrProducts    = array();
        $productCount   = 0; //$this->runApiUrl( 'article/count' );
        $apiProducts    = ''; //$this->runApiUrl( 'article/?pageSize=1000' );
        $objDetailPage  = \PageModel::findByPk( $detailPageId );

        $arrItemNumbers = explode(",", $itemNumber);

        if( is_array($arrItemNumbers) && count($arrItemNumbers) )
        {
            foreach($arrItemNumbers as $arrItemNumber)
            {
                if( $arrItemNumber )
                {
                    $arrItemNumber     = preg_replace('/\*/', '%25', $arrItemNumber);
                    $apiProducts    = $this->runApiUrl('article/?articleNumber-ilike=' . $arrItemNumber . '&pageSize=1000');
                }

                foreach($apiProducts as $arrProduct)
                {
                    $objProduct = $this->getProductItem( $arrProduct, $objDetailPage );

                    if( $objProduct )
                    {
                        $arrProducts[] = $objProduct;
                    }
                }
            }
        }

        return $arrProducts;
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

                if( $objProduct instanceof \stdClass )
                {
                    $objProduct = (object) $objItemProduct;

                    $objProduct->itemNumber     = $objItemProduct['articleNumber'];
                    $objProduct->apiProduct     = $objItemProduct;
                }
                else
                {
                    $objProduct->imageTag       = $objItemProduct['imageTag'];
                    $objProduct->imagePath      = $objItemProduct['imagePath'];
                    $objProduct->price          = $objItemProduct['price'];
                    $objProduct->detailsLink    = $objItemProduct['detailsLink'];

                    $objProduct->imageDetailTag = $objItemProduct['imageDetailTag'];
                    $objProduct->images         = $objItemProduct['images'];
                    $objProduct->apiProduct     = $objItemProduct;
                }
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
            $imageTag   = '';
            $imagePath  = '';
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

            $arrProduct['imageTag']     = $imageTag;
            $arrProduct['imagePath']    = $imagePath;
            $arrProduct['price']        = ShopHelper::renderPrice( $arrProduct['articlePrices'][0]['price'] );
            $arrProduct['detailsLink']  = $detailLink;

            $arrProduct['imageDetailTag']   = ($objProduct->detailSRC ? ImageHelper::getImageTag( $objProduct->detailSRC ) : $imageTag);
            $arrProduct['images']           = $arrImages;

            return $arrProduct;
        }

        return false;
    }



    public function getColorCode( $colorKey )
    {
        return $this->colorCodes[ $colorKey ];
    }



    public function getFlexCodes()
    {
        return array_keys($this->flexCode);
    }



    public function getFlexName( $flexKey )
    {
        return $this->flexCode[ $flexKey ]['label'];
    }



    public function getFlexRange( $flexKey )
    {
        return $this->flexCode[ $flexKey ]['range'];
    }



    public function getFlexKey( $flexRange )
    {
        foreach( $this->flexCode as $key => $arrFlex)
        {
            if( (int) $flexRange === (int) $arrFlex['range'] )
            {
                return $key;
            }
        }

        $flexKey = false;

//        foreach( $this->flexCode as $key => $arrFlex)
//        {
            //TODO: get range key
//        }

        return $flexKey;
    }



    public function runApiUrl( $urlParams, $returnVar = '' )
    {
        $auth = [
            'Content-Type: application/json',
            'AuthenticationToken: '. $this->getToken()
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, $this->getURL() . $urlParams);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $out = curl_exec($ch);
        curl_close($ch);

        if( preg_match('/downloadArticleImage/', $urlParams) )
        {
            return $out;
        }

        $arrOutput = json_decode($out, TRUE);

        if( $arrOutput['error'] )
        {
            return $arrOutput['error'];
        }

        $arrReturn = $returnVar ? $arrOutput[ $returnVar ] : ((isset($arrOutput['result'])) ? $arrOutput['result'] : $arrOutput);

        if( count($arrReturn) === 1 && preg_match('/\-eq/', $urlParams) )
        {
            $arrReturn = $arrReturn[0];
        }

        return $arrReturn;
    }



    public function downloadArticleImage( $articleId, $imageId, $imageName )
    {
        $articleImagePath = BasicHelper::getRootDir() . '/' . $this->localImagePath . $articleId . '/';

        if( !file_exists( $articleImagePath .  $imageName) )
        {
            if( !is_dir($articleImagePath) )
            {
                mkdir( $articleImagePath );
            }

            $objImage = $this->runApiUrl('article/id/' . $articleId . '/downloadArticleImage?articleImageId=' . $imageId, false );

            file_put_contents( $articleImagePath . $imageName, $objImage);
        }

        return $this->localImagePath . $articleId . '/' . $imageName;
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
}