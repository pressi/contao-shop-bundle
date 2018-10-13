<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\BasicBundle\Helper\BasicHelper;

use IIDO\ShopBundle\API\DefaultApi;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Config\ShopConfig;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class ShopHelper
{

    protected static $priceUnit = '&euro;';


    protected static $questionnaireTemplateFile = 'rsce_shop_questonaire.html5';
    protected static $questionnaireConfigFile   = 'rsce_shop_questonaire_config.php';


    public static $skiItemNumberParts = '##SKI##.##COLOR##.##SIZE##.##WOODCORE##.##FLEX##.##KEIL##';

    protected static $woodCore = ['PP', 'EP'];



    protected static $sizeOrder = array
    (
        'xxs'   => 0,
        'xs'    => 5,
        's'     => 10,
        'm'     => 15,
        'l'     => 20,
        'xl'    => 25,
        'xxl'   => 30
    );



    /**
     * @param float $intPrice
     * @param boolean $useDecimals
     *
     * @return float|string
     */
    public static function renderPrice( $intPrice, $useDecimals = false, $renderDecimalNull = false )
    {
        $intPrice = (float) $intPrice;

        if( !$useDecimals && (!preg_match('/\./', $intPrice) || preg_match('/\.00$/', $intPrice)) )
        {
            if(!$renderDecimalNull )
            {
                $intPrice = preg_replace('/\.00$/', ',-', $intPrice);
            }
        }

        if( $useDecimals )
        {
            $intPrice = number_format($intPrice, 2, ',', '.');

            if(!$renderDecimalNull )
            {
                $intPrice = preg_replace('/\,00$/', ',-', $intPrice);
            }
        }

        return $intPrice;
    }



    public static function getPayments()
    {
        return PaymentHelper::getAll();
    }



    public static function getProduct( $item, array $strLang = array(), $object = false, $withoutTuningPrice = false )
    {
        //TODO: check if api is active // else use contao product details not from api!!
        if( !count($strLang) )
        {
            \Controller::loadLanguageFile("iido_shop_cart");

            $strLang = $GLOBALS['TL_LANG']['iido_shop_cart'];
        }

        $strLabel       = '';
        $strLangSize    = $strLang['label']['size'];

        $itemNumber     = $item['realItemNumber']?:$item['itemNumber'];
        $arrItemNumber  = explode(".", $itemNumber);
        $realItemNumber = $item['realItemNumber'];

        $objShopProduct = false;
        $skiItem        = false;
        $objApi         = ApiHelper::getApiObject();
        /* @var $objApi \IIDO\ShopBundle\API\WeclappApi */

        $detailInfos    = '';
        $detailLink     = '';
        $strClass       = '';
        $strAttributes  = '';
        $imageTag       = '';
        $intPrice       = 0;
        $arrCategories  = self::getProductCategories( $itemNumber );

        if( preg_match('/^C/', $itemNumber) )
        {
            $objShopProduct = self::findShopProduct( $itemNumber );
        }
        else
        {
            $objShopProduct = IidoShopProductModel::findByItemNumber( $itemNumber );
        }

        if( $object && $object->iidoShopEditPage )
        {
            $detailLink = \PageModel::findByPk( $object->iidoShopEditPage )->getFrontendUrl('/' . (\Config::get("useAutoItem") ? '' : self::getUrlPath() . '/') . $objShopProduct->alias);
        }

        if( preg_match('/^C/', $itemNumber) || preg_match('/^S/', $itemNumber) )
        {

            $skiItem        = true;

            $strLangFlex    = $strLang['label']['flex'];
            $strLangBinding = $strLang['label']['binding'];
            $strLangTuning  = $strLang['label']['tuning'];

            $add = 0;

            if( $arrItemNumber[0] === 'C' )
            {
                $add = 1;
            }

            $designNumber   = $arrItemNumber[ (1 + $add) ];
            $sizeNumber     = $arrItemNumber[ (2 + $add) ];
            $flexNumber     = $arrItemNumber[ (4 + $add) ];
            $bindingNumber  = $arrItemNumber[ 7 ];
            $tuningNumber   = $item['tuning']; //TODO: Tuning Number, include in Price!!
//echo "<pre>"; print_r( $tuningNumber ); exit;
            $itemNumber     = self::renderItemNumber($itemNumber);

//            $objProduct     = false;
//            $arrProducts    = $objApi->runApiUrl('article/?articleNumber-like=' . $itemNumber);

//            if( !$realItemNumber && (!$bindingNumber || $bindingNumber === "none") )
//            {
//                $arrNewItemNumber = $arrItemNumber;
//
//                unset( $arrNewItemNumber[7] );
//
//                $realItemNumber = implode(".", $arrNewItemNumber);
//                echo "<pre>"; print_r( $realItemNumber ); exit;
//            }

            if( !$bindingNumber || $bindingNumber === "none" )
            {
                $realItemNumber = preg_replace('/.none$/', '', $realItemNumber);
            }

            $objProduct     = $objApi->runApiUrl('article/?articleNumber-eq=' . $realItemNumber?:$itemNumber);

//            foreach( $arrProducts as $arrProduct )
//            {
//                $objProduct = self::getProductObject( $arrProduct );
//
//                if( $objProduct )
//                {
//                    $realItemNumber = $objProduct->articleNumber;
//                    break;
//                }
//            }
//
//            if( !$objProduct )
//            {
//                $objProduct = self::getNextFlexSki( $itemNumber, $objApi);
//
//                if( $objProduct )
//                {
//                    $realItemNumber = $objProduct->articleNumber;
//                }
//            }

            $objBinding     = $objApi->runApiUrl('article/?articleNumber-eq=' . $bindingNumber);
            $objTuning      = $tuningNumber ? $objApi->runApiUrl('article/?articleNumber-eq=' . $tuningNumber ) : false;

            $strLabel       = 'ORIGINAL+ SKI';

            $detailInfos = $objApi->getColorLabel( $designNumber );
            $detailInfos .= ', ' . $sizeNumber . 'cm';
            $detailInfos .= ', ' . $strLangFlex . ': ' . $objApi->getFlexName( $flexNumber );

            if( count($objBinding) )
            {
                $detailInfos .= ', ' . $strLangBinding . ': ' . $objBinding['name'];
            }

            if( $objTuning )
            {
                $detailInfos .= ', ' . $strLangTuning . ': ' . $objTuning['name'];
            }

            $strClass .= ' product-item-ski';

            if( $arrItemNumber[0] === "C" )
            {
                array_shift( $arrItemNumber );
            }

            $skiNumber  = $arrItemNumber[0];
            $objSki     = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);

            $item['name'] = $objSki['name'];
            $item['itemNumber'] = self::getFullItemNumber($item['itemNumber']);
        }
        else
        {
            $detailInfos = $objApi->getColorLabel( $arrItemNumber[1] );

            if( isset($arrItemNumber[2]) )
            {
                \Controller::loadLanguageFile("iido_shop");

                $detailInfos    .= (strlen($detailInfos) ? ', ' : '') . $GLOBALS['TL_LANG']['iido_shop']['label']['gender'][ $arrItemNumber[2] ];
            }

            if( isset($arrItemNumber[3]) )
            {
                $detailInfos    .= (strlen($detailInfos) ? ', ' : '') . $arrItemNumber[3];
            }

            $objProduct = $objApi->runApiUrl('article/?articleNumber-eq=' . $itemNumber);
        }

        if( $objProduct )
        {
            $intPrice = self::getCurrentPrice( $objProduct );

            if( $skiItem )
            {
                $strImage = $objApi->getSkiImage( (array) $objProduct );

                if( $objTuning && !$withoutTuningPrice)
                {
                    $tunintPrice = self::getCurrentPrice( $objTuning );

                    if( $tunintPrice > 0 )
                    {
                        $intPrice = ($intPrice + $tunintPrice);
                    }
                }
            }
            else
            {
                $strImage = $objApi->getItemImage( (array) $objProduct );
            }

            if( $strImage )
            {
                $imageTag = ImageHelper::getImageTag( $strImage );
            }


        }

        return array
        (
            'id'            => (($objShopProduct) ? $objShopProduct->id : 0),

            'name'          => rawurldecode($item['name']),
            'realName'      => $item['name'],

            'itemNumber'        => $item['itemNumber'],
            'realItemNumber'    => $realItemNumber,
            'quantity'          => $item['quantity'],
            'tuning'        => $item['tuning'],

            'label'         => $strLabel,
            'detailInfos'   => $detailInfos,
            'detailLink'    => $detailLink,
            'imageTag'      => $imageTag,

            'price'         => self::renderPrice($intPrice),
            'intPrice'      => $intPrice,

            'categories'    => $arrCategories,
            'class'         => trim($strClass),
            'attributes'    => $strAttributes,

            'internProject' => $objShopProduct
        );
    }



    public static function getProductObject( $arrProduct )
    {
        if( $arrProduct['active'] === "1" || $arrProduct['active'] === 1 || $arrProduct['active'] )
        {
            return (object) $arrProduct;
        }

        return false;
    }



    protected static function findShopProduct( $itemNumber )
    {
        $productTable   = IidoShopProductModel::getTable();

        $skiNumber      = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9.]{1,})/', 'S$1', $itemNumber);
        $colorNumber    = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$2', $itemNumber);

        $objProducts = \Database::getInstance()->prepare("SELECT * FROM " . $productTable ." WHERE itemNumber LIKE ?")->execute($skiNumber . '.' . $colorNumber . '%');

        if( $objProducts && $objProducts->count() )
        {
            while( $objProduct = $objProducts->next() )
            {
                if( $objProduct->overviewSRC )
                {
                    return $objProduct;
                }
            }
        }

        return null;
    }



    protected static function getProductCategories( $itemNumber )
    {
        $objProduct     = IidoShopProductModel::findBy("itemNumber", $itemNumber);
        $arrCategories  = \StringUtil::deserialize($objProduct->categories, TRUE);

        if( !count($arrCategories) )
        {
            $skiNumber      = preg_replace('/C.S([A-Za-z0-9]{1,}).([A-Za-z0-9\.]{1,})/', 'C.S$1', $itemNumber);
            $productTable   = IidoShopProductModel::getTable();
            $objProducts    = \Database::getInstance()->prepare("SELECT * FROM " . $productTable ." WHERE itemNumber LIKE ?")->execute($skiNumber . '%');

            if( $objProducts )
            {
                while( $objProducts->next() )
                {
                    $arrProductCategories = \StringUtil::deserialize($objProducts->categories, TRUE);

                    if( count($arrProductCategories) )
                    {
                        $arrCategories = $arrProductCategories;
                        break;
                    }
                }
            }
        }

        if( count($arrCategories) )
        {
            foreach($arrCategories as $num => $categoryID)
            {
                $objCategory = IidoShopProductCategoryModel::findByPk( $categoryID );

                if( $objCategory )
                {
                    $arrCategories[ $num ] = $objCategory;
                }
            }
        }

        if( !count($arrCategories) )
        {
            $objAllCategories = IidoShopProductCategoryModel::findAll();

            if( $objAllCategories )
            {
                $arrSkiNumber   = explode(".", $itemNumber);
                $skiNumber      = $arrSkiNumber[0];

                if( $arrSkiNumber[0] === "C" )
                {
                    $skiNumber = $skiNumber . '.' . $arrSkiNumber[1];
                }
                else
                {
                    if( preg_match('/^S/', $arrSkiNumber[0]) )
                    {
                        $skiNumber = 'C.' . $skiNumber;
                    }
                }

                while( $objAllCategories->next() )
                {
                    if( $objAllCategories->published && strlen($objAllCategories->itemNumbers) )
                    {
                        $arrItemCats = explode(",", $objAllCategories->itemNumbers);

                        foreach( $arrItemCats as $itemCat )
                        {
                            $itemCat = trim(str_replace('*', '', $itemCat));

                            if( preg_match('/^' . $itemCat . '/', trim($skiNumber)) )
                            {
                                $arrCategories[] = $objAllCategories->current();
                            }
                        }
                    }
                }
            }
        }

        return $arrCategories;
    }



    public static function getUrlPath()
    {
        $objApi = new DefaultApi();

        return $objApi->getUrlPath();
    }



    public static function getRealItemNumber( $itemNumber, $objApi = false)
    {
        return $itemNumber;

//        if( !$objApi )
//        {
//            $objApi = ApiHelper::getApiObject();
//        }

//        $flexNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,}).([A-Za-z0-9.]{1,4})/', '$5', $itemNumber);
//        $flexKey        = $objApi->getFlexKey( $flexNumber );
//        $newItemNumber  = preg_replace('/\.' . $flexNumber . '\./', '.' . $flexKey . '.', $itemNumber);

//        return $newItemNumber;
    }



    public static function getPriceUnit( $includeWrapper = false )
    {
        $strTablePrefix = BundleConfig::getTableFieldPrefix();
        $priceUnit      = \Config::get($strTablePrefix . 'currency');

        if( !$priceUnit )
        {
            $priceUnit = self::$priceUnit;
        }
        else
        {
            \Controller::loadLanguageFile( 'tl_iido_shop_configuration' );

            $arrUnit    = $GLOBALS['TL_LANG']['tl_iido_shop_configuration']['options']['currency'];

            $priceUnit  = trim( preg_replace('/\(([A-Za-z0-9]{0,})\)$/', '', trim($arrUnit[ $priceUnit ])) );
        }

        return ($includeWrapper ? '<span class="price-unit">' . $priceUnit . '</span>' : $priceUnit);
    }



    public static function renderUrlPath( $pathName )
    {
        $pathName = preg_replace(array('/ /'), array('_'), $pathName);
        return strtolower($pathName);
    }



    public static function questionnaireTemplateExists()
    {
        $useTemplate    = false;
        $useConfig      = false;
        $rootDir        = BasicHelper::getRootDir();

        $arrTemplates   = scan( $rootDir .  '/templates' );

        if( is_array($arrTemplates) && count($arrTemplates) )
        {
            foreach($arrTemplates as $strTemplate)
            {
                if( is_dir( $rootDir . '/templates/' . $strTemplate) )
                {
                    $arrTemplateFiles = scan( $rootDir .  '/templates/' . $strTemplate );

                    foreach( $arrTemplateFiles as $strTemplateFile )
                    {
                        if( $strTemplateFile === self::$questionnaireTemplateFile )
                        {
                            $useTemplate = true;

                            if( $useConfig )
                            {
                                return true;
                            }

                            continue;
                        }

                        if( $strTemplateFile === self::$questionnaireConfigFile )
                        {
                            $useConfig = true;

                            if( $useTemplate )
                            {
                                return true;
                            }

                            continue;
                        }

                        if( $useTemplate && $useConfig )
                        {
                            return true;
                        }
                    }
                }
                else
                {
                    if( $strTemplate === self::$questionnaireTemplateFile )
                    {
                        $useTemplate = true;

                        if( $useConfig )
                        {
                            return true;
                        }

                        continue;
                    }

                    if( $strTemplate === self::$questionnaireConfigFile )
                    {
                        $useConfig = true;

                        if( $useTemplate )
                        {
                            return true;
                        }

                        continue;
                    }

                    if( $useTemplate && $useConfig )
                    {
                        return true;
                    }
                }
            }
        }

        if( $useTemplate && $useConfig )
        {
            return true;
        }

        return false;
    }



    public static function renderVariantItemNumber( $itemNumber, $parentItemNumber )
    {
        $newItemNumber = '';

        if( $itemNumber )
        {
            $newItemNumber = $itemNumber;

            if( preg_match('/\*/', $itemNumber) )
            {
                $newItemNumber = preg_replace('/\*/', $parentItemNumber, $itemNumber);
            }
        }

        return $newItemNumber;
    }



    public static function getCurrentPrice( $arrItem )
    {
        $intPrice = 0;

        $arrItem = (array) $arrItem;

        if( isset($arrItem['articlePrices']) && is_array($arrItem['articlePrices']) && count($arrItem['articlePrices']) )
        {
            foreach( $arrItem['articlePrices'] as $arrPrice)
            {
                if( is_array($arrPrice) )
                {
                    $price      = $arrPrice['price'];
                    $startDate  = $arrPrice['startDate'];
                    $endDate    = $arrPrice['endDate'];
                    $createDate = $arrPrice['createdDate'];
                }
                else
                {
                    $price      = $arrPrice->price;
                    $startDate  = $arrPrice->startDate;
                    $endDate    = $arrPrice->endDate;
                    $createDate = $arrPrice->createdDate;
                }

                $startDate  = (int) substr($startDate, 0, -3);
                $endDate    = (int) substr($endDate, 0, -3);

                if( !$startDate || $startDate === 0 )
                {
                    $startDate = (int) substr($createDate, 0, -3);
                }

                if( $startDate < time() )
                {
                    if( $endDate && $endDate >= time() )
                    {
                        $intPrice = $price;
                    }
                    elseif( !$endDate || $endDate === 0 )
                    {
                        $intPrice = $price;
                    }
                }
            }
        }
//        if(count($arrItem['articlePrices']) > 1) { echo "<pre>"; print_R( $arrItem['articlePrices'] ); echo "<br>"; print_r( $intPrice ); exit; }
        return $intPrice;
//        return $arrItem['articlePrices'][0]['price'];
    }



    public static function getSearchSkiItemNumber($skiItemNumberRange, $strMode = '')
    {
        if( $strMode === "like" )
        {
            $arrRealNumberParts = explode(".", self::$skiItemNumberParts);
            $arrNumberParts     = explode(".", $skiItemNumberRange);
            $skiItemNumberRange = $arrNumberParts[0];

            $skiNumber          = self::$skiItemNumberParts;

            if( $arrNumberParts[0] === 'C' )
            {
                $skiItemNumberRange .= '.' . $arrNumberParts[1];

                unset( $arrNumberParts[1] );
            }

            unset( $arrNumberParts[0] );

            foreach($arrNumberParts as $key => $number)
            {
                $strKey = $arrRealNumberParts[ $key ];

                $skiNumber = preg_replace('/' . $strKey . '/', $number, $skiNumber);
            }

            $skiItemNumberRange = str_replace('##SKI##', $skiItemNumberRange, $skiNumber);
            $skiItemNumberRange = preg_replace('/##([A-Z]{1,})##/', '%25', $skiItemNumberRange);
        }

        return $skiItemNumberRange;
    }



    protected static function getNextFlexSki( $itemNumber, $objApi )
    {
        $arrItemNumber = explode(".", $itemNumber);
        $add = 0;

        if( $arrItemNumber[0] === 'C' )
        {
            $add = 1;
        }

        $nums           = $nums = (4 + $add);
        $skiNumber      = '';

        for($num=0; $num<$nums; $num++)
        {
            $skiNumber .= (strlen($skiNumber) ? '.' : '') . $arrItemNumber[ $num ];
        }

        $currFlexNumber = self::getSkiFlexNum( $itemNumber );
        list($arrFlexNumbers, $arrFlexProducts) = self::getFlexNumbers( $skiNumber, $objApi, array_pop($arrItemNumber) );

        $maxFlexNum     = self::getMaxMinFlexNum($arrFlexNumbers, "max");
        $minFlexNum     = self::getMaxMinFlexNum($arrFlexNumbers, "min");

        $rangeToMax     = ($maxFlexNum - $currFlexNumber);
        $rangeToMin     = ($currFlexNumber - $minFlexNum);
        $skiPrice       = 0;
        $objItem        = false;

//        echo "<pre>";
//        print_r( $itemNumber );
//        echo "<br>";
//        print_r( $currFlexNumber );
//        echo "<br>";
//        print_r( $arrFlexNumbers );
//        echo "<br>MAX: ";
//        print_r( $maxFlexNum );
//        echo "<br>MIN: ";
//        print_r( $minFlexNum );
//        echo "<br>R-MAX: ";
//        print_r( $rangeToMax );
//        echo "<br>R-MIN: ";
//        print_r( $rangeToMin );
//        echo "<br>";
//        print_r( $arrFlexProducts );
//        echo "<br>";
//        exit;


        if( $rangeToMax < $rangeToMin )
        {
            if( $rangeToMax > 0 )
            {
                for($numTop=1; $numTop<$rangeToMax; $numTop++)
                {
                    $newFlexNum      = ($currFlexNumber + $numTop);
                    $newItemNumber   = str_replace('.' . $currFlexNumber . '.', '.' . $newFlexNum . '.', $itemNumber);

                    list($skiPrice, $arrItem) = self::getCurrentPriceFromProduct( $newItemNumber, $arrFlexProducts );

                    if( $skiPrice > 0 )
                    {
                        $objItem = self::getProductObject( $arrItem );
                        break;
                    }
                }
            }

            if( $skiPrice === 0 && $rangeToMin > 0 )
            {
                for($numBottom=1; $numBottom<$rangeToMin; $numBottom++)
                {
                    $newFlexNum      = ($currFlexNumber - $numBottom);
                    $newItemNumber   = str_replace('.' . $currFlexNumber . '.', '.' . $newFlexNum . '.', $itemNumber);

                    list($skiPrice, $arrItem) = self::getCurrentPriceFromProduct( $newItemNumber, $arrFlexProducts );

                    if( $skiPrice > 0 )
                    {
                        $objItem = self::getProductObject( $arrItem );
                        break;
                    }
                }
            }
        }
        else
        {
            if( $rangeToMin > 0 )
            {
                for($numBottom=1;$numBottom<$rangeToMin; $numBottom++)
                {
                    $newFlexNum      = ($currFlexNumber - $numBottom);
                    $newItemNumber   = str_replace('.' . $currFlexNumber . '.', '.' . $newFlexNum . '.', $itemNumber);

                    list($skiPrice, $arrItem) = self::getCurrentPriceFromProduct( $newItemNumber, $arrFlexProducts );

                    if( $skiPrice > 0 )
                    {
                        $objItem = self::getProductObject( $arrItem );
                        break;
                    }
                }
            }

            if( $skiPrice === 0 && $rangeToMax > 0 )
            {
                for($numTop=1; $numTop<$rangeToMax; $numTop++)
                {
                    $newFlexNum      = ($currFlexNumber + $numTop);
                    $newItemNumber   = $newItemNumber   = str_replace('.' . $currFlexNumber . '.', '.' . $newFlexNum . '.', $itemNumber);

                    list($skiPrice, $arrItem) = self::getCurrentPriceFromProduct( $newItemNumber, $arrFlexProducts );

                    if( $skiPrice > 0 )
                    {
                        $objItem = self::getProductObject( $arrItem );
                        break;
                    }
                }
            }
        }

        if( $skiPrice > 0 )
        {
            return $objItem;
        }

        return false;
    }



    public static function getFlexNumbers( $skiNumber, $objApi, $bindingNumber )
    {
        $arrFlexs = array();
        $arrItems = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumber . '.__.__.' . $bindingNumber );

        if( count($arrItems) )
        {
            foreach($arrItems as $arrItem)
            {
//                $objProduct = self::getProductObject( $arrItem );

//                if( $objProduct )
//                {
                    $flexNum = self::getSkiFlexNum( $arrItem['articleNumber'] );
                    $arrFlexs[ $flexNum ] = $flexNum;
//                }
            }
        }

        return array($arrFlexs, $arrItems);
    }



    public static function getSizeNumbers( $skiNumber, $objApi )
    {
        $arrSizes = array();
        $shortest = 300;

        $arrItems = $objApi->runApiUrl('article/?articleNumber-ilike=' . $skiNumber . '.___.__.__.__.%25' );

        if( count($arrItems) )
        {
            foreach( $arrItems as $arrItem)
            {
                $objProduct = self::getProductObject( $arrItem );

                if( $objProduct )
                {
                    $arrItemNumber = explode(".", $objProduct->articleNumber);

                    $size = $arrItemNumber[3];

                    $arrSizes[ $size ] = $size;

                    if( $size < $shortest )
                    {
                        $shortest = $size;
                    }
                }
            }
        }

        return array( $arrSizes, $shortest );
    }



    public static function getSkiFlexNum( $itemNumber )
    {
        $flexIndex      = 4;
        $arrItemNumber  = explode(".", $itemNumber);

        if( $arrItemNumber[0] === "C" )
        {
            $flexIndex = 5;
        }

        return $arrItemNumber[ $flexIndex ];
    }



    public static function getMaxMinFlexNum( $arrFlexs, $mode )
    {
        $flexNum = (($mode === "max") ? 50 : 60);

//        for($flexKey=0; $flexKey<count($arrFlexs); $flexKey++)
        foreach($arrFlexs as $flex )
        {
//            $flex = (int) $arrFlexs[ $flexKey ];

            if( $mode === "max" && $flex > $flexNum )
            {
                $flexNum = $flex;
            }
            elseif( $mode === "min" && $flex < $flexNum )
            {
                $flexNum = $flex;
            }
        }

        return $flexNum;
    }



    public static function getCurrentPriceFromProduct( $itemNumber, $arrItems )
    {
        foreach($arrItems as $arrItem)
        {
            $objItem = self::getProductObject( $arrItem );

            if( $objItem )
            {
                $itemNumber = preg_replace('/__/', '([A-Za-z0-9]{2})', $itemNumber);

                if( preg_match('/' . $itemNumber . '/', $objItem->articleNumber) )
                {
                    return array(self::getCurrentPrice( (array) $objItem ), (array) $objItem);
                }
            }
        }
    }



    public static function getCartPrice()
    {
        $cartPrice      = 0;
        $arrCartList    = ShopConfig::getCartList();
        $objApi         = ApiHelper::getApiObject();
        /* @var $objApi \IIDO\ShopBundle\API\WeclappApi */

        if( count($arrCartList) )
        {
            foreach($arrCartList as $cartItem)
            {
                $objItem        = $objApi->getApiProductFromFile( $cartItem['realItemNumber']?:$cartItem['itemNumber'], $cartItem['tuning'] );

                if( $objItem['price'] )
                {
                    $price = ( (float) $objItem['price'] * (int) $cartItem['quantity'] );
                }
                else
                {
                    $price = ( (float) self::getCurrentPrice( $objItem ) * (int) $cartItem['quantity'] );
                }

                $cartPrice      = ($price + $cartPrice);

                if( $cartItem['tuning'] )
                {
                    $tuningPrice = ( (float) $objItem['tuning']['price'] * ($cartItem['quantity']));

                    $cartPrice  = ($tuningPrice + $cartPrice);
                }
            }
        }

        return $cartPrice;
    }



    public static function getShortestSize( $arrSizes )
    {
        $size = 300;

        foreach($arrSizes as $arrSize)
        {
            if( is_array($arrSize) )
            {
                if( (int) $arrSize['articleNumber'] < $size )
                {
                    $size = (int) $arrSize['articleNumber'];
                }
            }
            else
            {
                if( (int) $arrSize < $size )
                {
                    $size = (int) $arrSize;
                }
            }
        }

        return $size;
    }



    public static function getMinFlex( $arrFlexs )
    {
        $flex = 60;

        foreach($arrFlexs as $arrFlex)
        {
            if( (int) $arrFlex['range']['num'] < $flex )
            {
                $flex = (int) $arrFlex['range']['num'];
            }
        }

        return $flex;
    }



    public static function renderItemNumber( $itemNumber )
    {
        $itemNumber = str_replace('##KEIL##', '__', $itemNumber);
        $itemNumber = preg_replace('/.none$/', '', $itemNumber, -1, $replaceBindingCounter);

        if( $replaceBindingCounter )
        {
            $itemNumber = preg_replace('/^C./', '', $itemNumber);
        }

        $arrItemNumber  = explode(".", $itemNumber);

        $keilIndex = 5;

        if( $arrItemNumber[0] === 'C' )
        {
            $keilIndex = 6;
        }

        $arrItemNumber[ $keilIndex ] = '__';

        return implode(".", $arrItemNumber);
    }

    public static function getFullItemNumber( $itemNumber )
    {
        $itemNumber = str_replace('##KEIL##', '__', $itemNumber);

        $numberBefore   = '';
        $arrItemNumber  = explode(".", $itemNumber);

        $bindingIndex = 6;

        if( $arrItemNumber[0] === 'C' )
        {
            $bindingIndex = 7;
        }
        else
        {
            $numberBefore = 'C.';
        }

        if( !isset($arrItemNumber[ $bindingIndex ]) )
        {
            $arrItemNumber[ $bindingIndex ] = 'none';
        }

        return $numberBefore . implode(".", $arrItemNumber);
    }



    public static function getProductCategory( $itemNumber )
    {
        $strLang        = BasicHelper::getLanguage();
        $objCategory    = false;
        $objCategories  = IidoShopProductCategoryModel::findAll();

        if( $objCategories )
        {
            while( $objCategories->next() )
            {
                if( $strLang === "en" )
                {
                    if( !preg_match('/^en-/', $objCategories->alias) )
                    {
                        continue;
                    }
                }
                else
                {
                    if( preg_match('/^en-/', $objCategories->alias) )
                    {
                        continue;
                    }
                }

                $arrItemNumbers = explode(",", $objCategories->itemNumbers);

                if( count($arrItemNumbers) )
                {
                    foreach( $arrItemNumbers as $strItemNumber )
                    {
                        if( trim($strItemNumber) )
                        {
                            $strItemNumber = preg_replace('/\*/', '', trim($strItemNumber));

                            if( preg_match('/' . $strItemNumber . '/', $itemNumber) )
                            {
                                $objCategory = $objCategories->current();
                                break;
                            }
                        }
                    }

                    if( $objCategory )
                    {
                        break;
                    }
                }
            }
        }

        return $objCategory;
    }



    public static function getShortestProductSize( $arrSizes )
    {
        $size           = '';
        $currentOrder   = 1000;

        foreach($arrSizes as $arrSize)
        {
            $arrSizeNum = self::$sizeOrder[ strtolower($arrSize) ];

            if( $arrSizeNum < $currentOrder )
            {
                $currentOrder = $arrSizeNum;
                $size = $arrSize;
            }
        }

        return $size;
    }



    public static function getSizeOrderNumber( $size )
    {
        return self::$sizeOrder[ strtolower($size) ];
    }



    public static function renderBindingOrderNumber( $strName, $itemNumber = '' )
    {
        if( $itemNumber )
        {
            return $itemNumber;
        }

        $orderNumber = $strName;

        preg_match_all('/([A-Za-zöäüÖÄÜß\s\-,;.:\\_\(\)\/]{0,})([0-9]{0,})([A-za-z0-9öäüÖÄÜß\s\-,;.:\\_\(\)\/]{0,})/', $strName, $arrMatches);

        if( count($arrMatches) > 1 )
        {
            $orderNumber = trim($arrMatches[2][0]);
        }

        return $orderNumber;
    }



    public static function renderErrorEmailContent( $arrOutput, $urlParams, $postFields )
    {
        $strContent = 'Weclapp Error: ' . $arrOutput['error'] . '<br>';
        $strContent .= 'Search path: ' . $urlParams . '<br>';
        $strContent .= 'URL: ' . \Environment::get("request") . '<br><br>';

        if( $postFields )
        {
            $postFields = json_decode($postFields, TRUE);

            $strContent .= 'POSTFIELDS: <br>';

            foreach( $postFields as $field => $value )
            {
                if( is_array($value) )
                {
                    $strContent .= '<br>' . $field . ':<br>';

                    foreach( $value as $vKey => $vValue )
                    {
                        if( is_array($vValue) )
                        {
                            $strContent .= ' - ' . $vKey . ':<br>';

                            foreach( $vValue as $itemKey => $itemValue )
                            {
                                $strContent .= ' -- ' . $itemKey . ' -> ' . $itemValue . '<br>';
                            }
                        }
                        else
                        {
                            $strContent .= ' - ' . $vKey . ' > ' . $vValue . '<br>';
                        }
                    }
                }
                else
                {
                    $strContent .= '<br>' . $field . ': ' . $value;
                }
            }
        }

        return $strContent;
    }



    public static function getApiProductMainImage( $arrItem, $objApi )
    {
        if( is_array($arrItem['articleImages']) && count($arrItem['articleImages']) )
        {
            $usedImage      = false;
            $arrImage       = array();
            $arrImages      = array();

            foreach( $arrItem['articleImages'] as $artImage )
            {
                if( $artImage['mainImage'] )
                {
                    $usedImage  = true;
                    $arrImage   = $artImage ;
                    break;
                }
                else
                {
                    $arrImages[] = $artImage;
                }
            }

            if( !$usedImage && count($arrImages) )
            {
                $imagePath = $objApi->downloadArticleImage( $arrItem['id'], $arrImages[0]['id'], $arrImages[0]['fileName'] );
            }
            else
            {
                $imagePath = $objApi->downloadArticleImage( $arrItem['id'], $arrImage['id'], $arrImage['fileName'] );
            }

            return $imagePath;
        }

        return '';
    }

}