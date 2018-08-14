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
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class ShopHelper
{

    protected static $priceUnit = '&euro;';


    protected static $questionnaireTemplateFile = 'rsce_shop_questonaire.html5';
    protected static $questionnaireConfigFile   = 'rsce_shop_questonaire_config.php';



    /**
     * @param float $intPrice
     * @param boolean $useDecimals
     *
     * @return float|string
     */
    public static function renderPrice( $intPrice, $useDecimals = false )
    {
        $intPrice = (float) $intPrice;

        if( !$useDecimals && (!preg_match('/\./', $intPrice) || preg_match('/\.00$/', $intPrice)) )
        {
            $intPrice = preg_replace('/\.00$/', '', $intPrice) . ',-';
        }

        if( $useDecimals )
        {
            $intPrice = number_format($intPrice, 2, ',', '.');
        }

        return $intPrice;
    }



    public static function getPayments()
    {
        return PaymentHelper::getAll();
    }



    public static function getProduct( $item, array $strLang = array(), $object = false )
    {
        //TODO: check if api is active // else use contao product details not from api!!
        if( !count($strLang) )
        {
            \Controller::loadLanguageFile("iido_shop_cart");

            $strLang = $GLOBALS['TL_LANG']['iido_shop_cart'];
        }

        $strLangSize    = $strLang['label']['size'];
        $strLangFlex    = $strLang['label']['flex'];
        $strLangBinding = $strLang['label']['binding'];
        $strLangTuning  = $strLang['label']['tuning'];

        $itemNumber     = $item['itemNumber'];

        $sizeNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$3', $itemNumber);
        $flexNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$4', $itemNumber);
        $bindingNumber  = preg_replace('/([A-Za-z0-9.]{1,}).B([0-9]{1,})$/', 'B$2', $itemNumber);
        $tuningNumber   = $item['tuning']; //TODO: Tuning Number, include in Price!!
        $flexKeyNumber  = '';

        $objApi         = ApiHelper::getApiObject();

        if( !in_array($flexNumber, $objApi->getFlexCodes()) )
        {
            $flexKey        = $objApi->getFlexKey( $flexNumber );
            $itemNumber     = preg_replace('/\.' . $flexNumber  . '\./', '.' . $flexKey . '.', $itemNumber);

            $flexKeyNumber  = $flexNumber;
            $flexNumber     = $flexKey;
        }

        $objProduct     = $objApi->runApiUrl('article/?articleNumber-eq=' . $itemNumber);
        $objBinding     = $objApi->runApiUrl('article/?articleNumber-eq=' . $bindingNumber);
        $objTuning      = false; // $objApi->runApiUrl('article/?articleNumber-eq=' . $tuningNumber );
        $arrCategories  = self::getProductCategories( $itemNumber );
        $strClass       = '';

        if( preg_match('/^C/', $itemNumber) )
        {
            $objShopProduct = self::findShopProduct( $itemNumber );
        }
        else
        {
            $objShopProduct = IidoShopProductModel::findByItemNumber( $itemNumber );
        }

        $intPrice       = $objProduct['articlePrices'][0]['price'];
        $imageTag       = '';
        $detailInfos    = '';
        $detailLink     = '';

        if( $objProduct )
        {
            $strImage = $objApi->getItemImage( (array) $objProduct );

            if( $strImage )
            {
                $imageTag = ImageHelper::getImageTag( $strImage );
            }
        }

        if( $object && $object->iidoShopEditPage )
        {
            $detailLink = \PageModel::findByPk( $object->iidoShopEditPage )->getFrontendUrl('/' . (\Config::get("useAutoItem") ? '' : self::getUrlPath() . '/') . $objShopProduct->alias);
        }

        $strLabel = 'ORIGINAL+';

        if( preg_match('/^C/', $itemNumber) || preg_match('/^S/', $itemNumber) )
        {
            $strLabel       = 'ORIGINAL+ SKI';
            $detailInfos    = $strLangSize . ': ' . $sizeNumber;

            if( count($objBinding) )
            {
                $detailInfos .= ' / ' . $strLangBinding . ': ' . $objBinding['name'];
            }

            $detailInfos .= ' / ' . $strLangFlex . ': ' . $objApi->getFlexName( $flexNumber );

            if( $objTuning )
            {
                $detailInfos .= ' / ' . $strLangTuning . ': ' . $objTuning['name'];
            }

            $strClass .= ' product-item-ski';


            $arrItemNumber  = explode(".", $itemNumber);

            if( $arrItemNumber[0] === "C" )
            {
                array_shift( $arrItemNumber );
            }

            $skiNumber  = $arrItemNumber[0];
            $objSki     = $objApi->runApiUrl('article/?articleNumber-eq=' . $skiNumber);

            $item['name'] = $objSki['name'];
        }

        $itemNumber     = preg_replace('/\.' . $flexKey  . '\./', '.' . $flexKeyNumber . '.', $itemNumber);

        return array
        (
            'id'            => $objShopProduct->id,

            'name'          => rawurldecode($item['name']),
            'realName'      => $item['name'],

            'itemNumber'    => $itemNumber,
            'quantity'      => $item['quantity'],
            'tuning'        => $item['tuning'],

            'label'         => $strLabel,
            'detailInfos'   => $detailInfos,
            'detailLink'    => $detailLink,
            'imageTag'      => $imageTag,

            'price'         => self::renderPrice($intPrice),
            'intPrice'      => $intPrice,

            'categories'    => $arrCategories,
            'class'         => trim($strClass),

            'internProject' => $objShopProduct
        );
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
                while( $objAllCategories->next() )
                {
                    if( $objAllCategories->published && strlen($objAllCategories->itemNumbers) )
                    {
                        $arrItemCats = explode(",", $objAllCategories->itemNumbers);

                        foreach( $arrItemCats as $itemCat )
                        {
                            $itemCat = trim(str_replace('*', '', $itemCat));

                            if( preg_match('/^' . $itemCat . '/', trim($itemNumber)) )
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
        if( !$objApi )
        {
            $objApi = ApiHelper::getApiObject();
        }

        $flexNumber     = preg_replace('/^C.S([0-9]{4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9]{1,4}).([A-Za-z0-9.]{1,})/', '$4', $itemNumber);
        $flexKey        = $objApi->getFlexKey( $flexNumber );
        $newItemNumber  = preg_replace('/\.' . $flexNumber . '\./', '.' . $flexKey . '.', $itemNumber);

        return $newItemNumber;
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

            $arrUnit    = $GLOBALS['TL_LANG']['tl_iido_shop_configuration']['options'][ $strTablePrefix . 'currency'];
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

}