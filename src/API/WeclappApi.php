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
            'label'     => 'Aqua on Black'
        ),
        'BA'    => array
        (
            'alias'     => 'black_aqua',
            'label'     => 'Black on Aqua'
        ),

        'WB'    => array
        (
            'alias'     => 'white_black',
            'label'     => 'White on Black'
        ),
        'BW'    => array
        (
            'alias'     => 'black_white',
            'label'     => 'Black on White'
        ),

        'BB'    => array
        (
            'alias'     => 'black_black',
            'label'     => 'Black on Black'
        ),

        'CB'    => array
        (
            'alias'     => 'cblue_black',
            'label'     => 'Crystal-Blue on Black'
        ),
        'BC'    => array
        (
            'alias'     => 'black_cblue',
            'label'     => 'Black on Crystal-Blue'
        ),

        'YB'    => array
        (
            'alias'     => 'yellow_black',
            'label'     => 'Yellow on Black'
        ),
        'MB'    => array
        (
            'alias'     => 'magenta_black',
            'label'     => 'Magenta on Black'
        ),

        'IB'    => array
        (
            'alias'     => 'ired_black',
            'label'     => 'Indian-Red on Black'
        ),
        'BI'    => array
        (
            'alias'     => 'black_ired',
            'label'     => 'Black on Indian-Red'
        ),

        'CBB'    => array
        (
            'alias'     => 'cblue_black',
            'label'     => 'Crystal-Blue on Black'
        ),
        'BCB'    => array
        (
            'alias'     => 'black_cblue',
            'label'     => 'Black on Crystal-Blue'
        ),
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
            'label' => 'Hard',
            'range' => 100
        )
    );



    protected $flexRange = array
    (
        'superhard' => array
        (
            'label' => 'Superhard',
            'range' => array
            (
                'min'   => 0,
                'max'   => 65
            )
        ),

        'hard' => array
        (
            'label' => 'Hard',
            'range' => array
            (
                'min'   => 66,
                'max'   => 72
            )
        ),

        'medium' => array
        (
            'label' => 'Medium',
            'range' => array
            (
                'min'   => 73,
                'max'   => 77
            )
        ),

        'soft' => array
        (
            'label' => 'Soft',
            'range' => array
            (
                'min'   => 78,
                'max'   => 85
            )
        ),

        'supersoft' => array
        (
            'label' => 'Supersoft',
            'range' => array
            (
                'min'   => 82,
                'max'   => 0
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
        return $this->colorCodes[ $colorKey ]['alias'];
    }



    public function getColorLabel( $colorKey )
    {
        return $this->colorCodes[ $colorKey ]['label'];
    }



    public function getColor( $colorKey )
    {
        return $this->colorCodes[ $colorKey ];
    }



    public function getFlex()
    {
        return $this->flexRange;
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



    public function runApiUrl( $urlParams, $returnVar = '', $method = 'GET' )
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

        if( $method === "POST" )
        {
//            curl_setopt($ch, CURLOPT_POST, 1 );
//            curl_setopt($ch, CURLOPT_POSTFIELDS, "body goes here" );
        }

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
        if( is_array($arrItem['articleImages']) && count($arrItem['articleImages']) )
        {
            $strFirstPath   = '';
            $mainImagePath  = '';

            foreach( $arrItem['articleImages'] as $artImage )
            {
                $strPath = $this->downloadArticleImage( $arrItem['id'], $artImage['id'], $artImage['fileName'] );

                if( !$strFirstPath )
                {
                    $strFirstPath = $strPath;
                    break;
                }

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



//    public function
}