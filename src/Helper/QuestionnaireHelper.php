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
use IIDO\BasicBundle\Helper\ImageHelper;
use IIDO\ShopBundle\API\DefaultApi;
use IIDO\ShopBundle\Config\BundleConfig;
use IIDO\ShopBundle\Model\IidoShopProductCategoryModel;
use IIDO\ShopBundle\Model\IidoShopProductModel;


class QuestionnaireHelper
{

    static $statisticTable = 'tl_iido_shop_statistic_questionnaire';



    public static function renderEmailText( $objClass )
    {
        $strContent = '';

        foreach( $objClass->pages as $arrPage )
        {
            echo "<pre>";
            print_r( $arrPage );
            exit;
        }

        return $strContent;
    }



    public static function generateSaveID( $twice = false)
    {
        $ip      = \Environment::get("ip") . ($twice ? '.' . rand(0, 200) : '');
        $hash    = md5($ip);
        $chars16 = array
        (
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            'a' => 10,
            'b' => 11,
            'c' => 12,
            'd' => 13,
            'e' => 14,
            'f' => 15
        );

        $base10 = '0';

        for ($i = strlen($hash) - 1; $i > 0; $i--)
        {
            $base10 = bcadd($base10, bcmul($chars16[$hash[$i]], bcpow(16, $i)));
        }

        $chars   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,;.:-_+*?!$%&@#~^=/<>[](){}`';
        $base    = (string)strlen($chars);
        $baseX   = '';

        while (bccomp($base10, $base) === 1 || bccomp($base10, $base) === 0)
        {
            $baseX  = substr($chars, bcmod($base10, $base), 1) . $baseX;
            $base10 = preg_replace('/\.\d*$/', '', bcdiv($base10, $base));
        }

        $baseX = substr($chars, $base10, 1) . $baseX;

        if( self::checkIfUserIdIsUnique( $baseX ) )
        {
            return $baseX;
        }

        return self::generateSaveID( true );
    }



    public static function checkIfUserIdIsUnique( $userID )
    {
        $objResult = \Database::getInstance()->prepare("SELECT * FROM " . self::$statisticTable . " WHERE userID=?")->execute( $userID );

        if( $objResult && $objResult->count() > 0 )
        {
            return false;
        }
        
        return true;
    }



    public static function saveData( $userID, $questionnaireID )
    {
        $objResult = \Database::getInstance()->prepare("SELECT * FROM " . self::$statisticTable . " WHERE userID=? AND questionnaire=?")->execute( $userID, $questionnaireID );

        $arrSet = array();
        $objQuestionnaire = \ContentModel::findByPk( $questionnaireID );

        if( $objQuestionnaire )
        {
            $arrData = json_decode($objQuestionnaire->rsce_data, TRUE);
            
            foreach( $arrData['pages'] as $arrPage )
            {
                if( $arrPage['hidePage'] )
                {
                    continue;
                }

                foreach( $arrPage['questions'] as $arrQuestion )
                {
                    if( $arrQuestion['maxAnswers'] > 1 )
                    {
                        $arrAnswers = \Input::postRaw( $arrQuestion['questionAlias']);

                        if( is_array($arrAnswers) && count($arrAnswers) )
                        {
                            $arrSet[ $arrQuestion['questionAlias'] ] = implode(",", $arrAnswers );
                        }
                        else
                        {
                            $arrSet[ $arrQuestion['questionAlias'] ] = \Input::postRaw( $arrQuestion['questionAlias'] );
                        }
                    }
                    else
                    {
                        $arrSet[ $arrQuestion['questionAlias'] ] = \Input::postRaw( $arrQuestion['questionAlias'] );
                    }

//                    $arrAnswers = $arrQuestion['answers'];

//                    if( count($arrAnswers) > 1 )
//                    {
//                    }
//                    elseif( count($arrAnswers) === 1 )
//                    {
//                        $arrSet[ $arrQuestion['questionAlias'] ] = \Input::postRaw( $arrQuestion['questionAlias'] );
//                    }
                }
            }
        }

        $arrSaveData = array
        (
            'tstamp'            => time(),
            'questionnaire'     => $questionnaireID,
            'userID'            => $userID,
            'questionnaireData' => json_encode( $arrSet ),
            'language'          => BasicHelper::getLanguage()
        );

//        echo "<pre>";
//        print_r( $userID );
//        echo "<br>";
//        print_r( $questionnaireID );
//        echo "<br>";
//        print_r( $arrSet );
//        echo "<br>";
//        print_r( $arrData );
//        echo "<pre>";
//        print_r( $objResult );
//        exit;

        if( count($arrSet) )
        {
            if( $objResult && $objResult->count() > 0 )
            {
                \Database::getInstance()->prepare("UPDATE " . self::$statisticTable . " %s WHERE userID=? AND questionnaire=?")->set( $arrSaveData )->execute( $userID, $questionnaireID );
            }
            else
            {
                \Database::getInstance()->prepare("INSERT INTO " . self::$statisticTable . " %s")->set( $arrSaveData )->execute();
            }
        }
    }



    public static function checkIfQuestionnaireHasData( $questionnaireID )
    {
        $objResult = \Database::getInstance()->prepare("SELECT * FROM " . self::$statisticTable . " WHERE questionnaire=?")->execute( $questionnaireID );

        if( $objResult && $objResult->count() > 0 )
        {
            return true;
        }

        return false;
    }



    public static function getQuestionnaireResult( $questionnaireID )
    {
        return \Database::getInstance()->prepare("SELECT * FROM " . self::$statisticTable . " WHERE questionnaire=?")->execute( $questionnaireID );
    }

}