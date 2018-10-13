<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\Helper;


use IIDO\BasicBundle\Helper\BasicHelper;
use IIDO\BasicBundle\Helper\ContentHelper;
use IIDO\ShopBundle\Config\BundleConfig;

use IIDO\ShopBundle\Model\IidoShopPaymentModel;


class PaymentHelper
{

    public static function get( $payment, $varName )
    {
        $objPayment = IidoShopPaymentModel::findByIdOrAlias( $payment );

        return $objPayment->$varName;
    }



    public static function getObject( $paymentID )
    {
//        $objPayment = \Database::getInstance()->prepare("SELECT * FROM " . IidoShopPaymentModel::getTable() . " WHERE id=?")->limit(1)->execute( $paymentID );
        $objPayment = IidoShopPaymentModel::findByIdOrAlias( $paymentID );

        if( !$objPayment )
        {
            $objPayment = IidoShopPaymentModel::findOneBy("type", $paymentID);
        }

        if( $objPayment )
        {
            $arrPayments = self::getAllTypes();

            $objPayment->title      = $arrPayments[ $objPayment->type ];
            $objPayment->active     = self::get( $objPayment->type, 'active');
            $objPayment->info       = ContentHelper::renderText( $objPayment->info );

            return $objPayment;
        }

        return false;
    }



    public static function getAllTypes()
    {
        $arrPayments    = array();
        $objPayments    = self::getAllFromFile();

        if( is_array($objPayments) && count($objPayments) )
        {
            foreach( $objPayments as $paymentAlias => $payment )
            {
                if( $payment['active'] || $payment['active'] === "1" || $payment['active'] === 1 )
                {
                    $arrPayments[ $paymentAlias ] = $payment['name'];
                }
            }
        }

        return $arrPayments;
    }



    public static function getAllFromFile()
    {
        return json_decode( file_get_contents(BasicHelper::getRootDir() . '/' . BundleConfig::getBundlePath() . '/src/Resources/config/payments.json'), TRUE );
    }



    public static function getAll( $getOnlyLocal = false )
    {
        $strTable       = IidoShopPaymentModel::getTable();
        $arrPayments    = array();
        $objPayments    = self::getAllFromFile();

        if( is_array($objPayments) && count($objPayments) )
        {
            foreach( $objPayments as $paymentAlias => $payment )
            {
                if( $payment['active'] || $payment['active'] === "1" || $payment['active'] === 1 )
                {
                    $objPayment = IidoShopPaymentModel::findByIdOrAlias( $paymentAlias );

                    if( !$objPayment )
                    {
                        $objPayment = \Database::getInstance()->prepare("SELECT * FROM " . $strTable . " WHERE type=?")->limit(1)->execute( $paymentAlias );

                        if( $objPayment && $objPayment->count() )
                        {
                            $objPayment = $objPayment->first();
                        }
                    }

                    if( ($objPayment && $objPayment->published) || $getOnlyLocal )
                    {
                        $arrPayments[] = array
                        (
                            'name'      => $objPayment->name ?: $payment['name'],
                            'alias'     => $paymentAlias,
                            'active'    => $payment['active'],
                            'fields'    => $payment['fields'],
                            'info'      => $objPayment->info
                        );
                    }
                }
            }
        }

        return $arrPayments;
    }



    public static function getOverview( $payment )
    {
        $strContent = '';

        if( !is_object($payment) )
        {
            $payment = self::getObject( $payment );
        }

        if( $payment )
        {
//            $arrPayments = self::getAllTypes();
            $strContent = ($payment->name?:$payment->title);
        }

        return $strContent;
    }



    public static function getMethod( $objPayment, $arrParams = array() )
    {
        $method = '\\IIDO\ShopBundle\\Payment\\' . $objPayment->title;

        if( !class_exists($method) )
        {
            $method = $method = '\\IIDO\ShopBundle\\Payment\\' . ucfirst($objPayment->type);
        }

        if( class_exists($method) )
        {
            return (count($arrParams) ? new $method(...$arrParams) : new $method());
        }

        return false;
    }
}