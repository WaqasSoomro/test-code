<?php

namespace App\Helpers;

class HumanOx
{
//    private static $base_url = '';
//
//    public static function partnerLogin()
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:5357/partnerlogin",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "login=jogo&pin=JOGO112333",
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: application/x-www-form-urlencoded",
//                "Cookie: connect.sid=s%3AhAApfxcuMij98LNDWKhKaqK76rYufB7S.UEtOFdohGIHg70MtoJj5P%2FT5pdkK0RYheNHcx4MxQlE"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        return json_decode($response);
//    }
//
//    public static function createAccount($cred)
//    {
//        $auth_credential = self::partnerLogin();
//
//        if ($auth_credential == null) {
//            return -1;
//        }
//
//        $cred['partnerid'] = $auth_credential->partnerid;
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:5357/players/puc",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "email=$cred[email]&name=$cred[name]&fullname=$cred[fullname]&partnerid=$cred[partnerid]&imei=$cred[imei]",
//            CURLOPT_HTTPHEADER => array(
//                "Authorization: Bearer $auth_credential->token",
//                "Content-Type: application/x-www-form-urlencoded",
//                "Cookie: connect.sid=s%3AS1PFsnWgHjwsgabEz5lmgyc_2prC6PCN.YCIngVkRKDAt4dSp8pXBJRK5FekrDXuEVQ4b6xvP3pY"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        return json_decode($response);
//    }
//
//    public static function mountSensor()
//    {
//        return true;
//    }
//
//    public static function authenticate($args)
//    {
//        $base_url = self::$base_url = 'https://live.humanox.com:5357/';
//        $api = $base_url . 'login';
//        $post_fields = 'login=' . $args['login'] . '&pin=' . $args['pin'];
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => $api,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => $post_fields,
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: application/x-www-form-urlencoded",
//                "Cookie: connect.sid=s%3AdI8C5ATVIrpw2IptUH6DLySvhlvUn6Q7.SQ9dlML5ejYleTiWD383yLWw0hcns9m2vigMgeQp8nk"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//        return json_decode($response);
//    }
//
//    public static function quickStart($imei)
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:10357/quickstart",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "imei=$imei",
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: application/x-www-form-urlencoded"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        json_decode($response);
//    }
//
//    public static function quickStop($imei)
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:10357/quickstop",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => "imei=$imei",
//            CURLOPT_HTTPHEADER => array(
//                "Content-Type: application/x-www-form-urlencoded"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        json_decode($response);
//    }
//
//    public static function getMatch($imei, $auth_token)
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:5357/matches/currentlyplaying/" . $imei,
//            //CURLOPT_URL => "https://live.humanox.com:5357/matches/currentlyplaying/862549047780542",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_HTTPHEADER => array(
//                "Authorization: Bearer " . $auth_token,
//                "Cookie: connect.sid=s%3ALT6PrCt9wWz5smyL4keCPy2y_JKqTdSo.Y3nHxivnY1jSFQ68oLRFvnlv6XToNZ6UNyGYBIAi%2Fnw"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        return json_decode($response);
//    }
//
//    public static function getMatchStats($match_id, $imei, $auth_token)
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => "https://live.humanox.com:5357/stats/all/$match_id/$imei",
//            //CURLOPT_URL => "https://live.humanox.com:5357/stats/match/1428/862549047787117",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_HTTPHEADER => array(
//                "Authorization: Bearer " . $auth_token,
//                "Cookie: connect.sid=s%3AVbk8p8garpC5k1XDVJG_sL6e6fP4l_8p.lBq6HYHpA1noblKrR2BOxo55n00n5FANR3AqSQlzPxA"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        return json_decode($response);
//    }
//
//
//    public static function getMatchData($match_id, $imei, $auth_token)
//    {
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            //CURLOPT_URL => "https://live.humanox.com:5357/matches/getmatchdata/1428/862549047787117",
//            CURLOPT_URL => "https://live.humanox.com:5357/matches/getplayerlastpos/$match_id/$imei",
//            //CURLOPT_URL => "https://live.humanox.com:5357/matches/getmatchdata/$match_id/$imei",
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => "",
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => "GET",
//            CURLOPT_HTTPHEADER => array(
//                "Authorization: Bearer " . $auth_token,
//                "Cookie: connect.sid=s%3AVbk8p8garpC5k1XDVJG_sL6e6fP4l_8p.lBq6HYHpA1noblKrR2BOxo55n00n5FANR3AqSQlzPxA"
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        $err = curl_error($curl);
//
//        curl_close($curl);
//        if ($err) {
//            activity()->log("cURL Error #:" . $err);
//            return 0;
//        }
//
//        return json_decode($response);
//    }


}

