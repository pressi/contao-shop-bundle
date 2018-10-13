<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

namespace IIDO\ShopBundle\AI;



use IIDO\ShopBundle\Config\BundleConfig;


class ShopAI
{
    public static $url          = 'https://typs.fact.ai/api/';

    public static $getParam     = 'profile/get';

    public static $postParam    = 'suggest';



    public static function setUrl( $url )
    {
        self::$url = $url;
    }



    public static function submitQuestionnaireForm( $objQuestionnaire )
    {
        $itemNumber = 0;
        $arrProfile = json_decode(self::runAction( self::$getParam ), TRUE);

        foreach( $arrProfile['questions'] as $questNum => $arrQuestion )
        {
            $answer = \Input::postRaw( self::getPostName( $arrQuestion['id'] ) );

            if( $answer )
            {
                switch( $arrQuestion['id'] )
                {
                    case "anwendungsbereich":
                    case "skifahrertyp":
                    case "speedcheck":
                    case "technikanalyse":
                    case "frequenzcheck":
                    case "radius":
                    case "geschlecht":
                    case "location":

                        if( $arrQuestion['id'] === "geschlecht" )
                        {
                            $answer = (($answer === "female" || $answer === "weiblich") ? 'weiblich' : 'männlich');
                        }

                        if( !is_array($answer) )
                        {
                            foreach( $arrQuestion['needs'] as $needNum => $arrNeed )
                            {
                                if( $arrNeed['id'] === $answer )
                                {
                                    $arrProfile['questions'][ $questNum ]['needs'][ $needNum ]['selected'] = true;
                                }
                            }
                        }
                        else
                        {
                            foreach($answer as $strAnswer )
                            {
                                foreach( $arrQuestion['needs'] as $needNum => $arrNeed )
                                {
                                    if( $arrNeed['id'] === $strAnswer )
                                    {
                                        $arrProfile['questions'][ $questNum ]['needs'][ $needNum ]['selected'] = true;
                                        break;
                                    }
                                }
                            }
                        }
                        break;

                    case "gewicht":
                    case "größe":
                    case "alter":
                        $arrProfile['questions'][ $questNum ]['needs'][ 0 ]['value']    = $answer;
                        $arrProfile['questions'][ $questNum ]['needs'][ 0 ]['selected'] = true;
                        break;

                }
            }
        }

        $profile    = json_encode( $arrProfile );
        $arrResult  = json_decode(self::runAction( self::$postParam, 'POST', $profile), TRUE);

        $objRedirectPage = \PageModel::findByPk( $objQuestionnaire->redirectPage );

        if( $objRedirectPage && count($arrResult) )
        {
//            \Input::setPost('FORM_SUBMIT', 'questionnaire');
//            \Input::setPost('itemNumber', $arrResult[0]);

            \Session::getInstance()->set('FORM_SUBMIT', 'questionnaire');
            \Session::getInstance()->set('itemNumber', $arrResult[0]);

            \Controller::redirect( $objRedirectPage->getFrontendUrl() );
        }
        else
        {
            $itemNumber = $arrResult[0];
        }

//        echo "<pre>";
//        print_r("TODO");
//        print_r( $arrResult );
//        echo "<br>";
//        print_r( $objQuestionnaire->redirectPage );
//        print_r( self::runAction( self::$postParam, 'POST', $profile) );
//        echo "<br>";
//        print_r( $profile );
//        echo "<br>";
//        print_r( $arrProfile );
//        echo "<br>";
//        print_r( $arrResult );
//        exit;

        return $itemNumber;
    }



    public static function runAction( $actionUrl, $method = 'GET', $postParams = '' )
    {
        $auth =
        [
            'Content-Type: application/json',
            'authorization: Basic ' . self::getAuthCode()
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, self::$url . $actionUrl );

        if( $postParams && $method === "POST" )
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $out = curl_exec($ch);
        curl_close($ch);

        return $out;
    }



    protected static function getPostName( $aiName )
    {
        switch( $aiName )
        {
//            case "anwendungsbereich":
//                $return = 'gelaende';
//                break;

            case "gewicht":
                $return = 'weight';
                break;

//            case "geschlecht":
//                $return = 'gender';
//                break;

            case "größe":
                $return = 'size';
                break;

//            case "skifahrertyp":
//                $return = 'gut';
//                break;

//            case "location":
//                $return = 'country';
//                break;

            default:
                $return = $aiName;
        }

        return $return;
    }



    protected static function getAuthCode()
    {
        $fieldPrefix = BundleConfig::getTableFieldPrefix();
        return base64_encode( \Config::get($fieldPrefix . 'aiUsername') . ':' . \Config::get($fieldPrefix . 'aiPassword') );
    }
}