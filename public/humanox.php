<?php
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://live.humanox.com:5357/partnerlogin",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "login=jogo&pin=JOGO112333",
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/x-www-form-urlencoded",
        "Cookie: connect.sid=s%3AhAApfxcuMij98LNDWKhKaqK76rYufB7S.UEtOFdohGIHg70MtoJj5P%2FT5pdkK0RYheNHcx4MxQlE"
    ),
));

$response = curl_exec($curl);
$token = json_decode($response);
echo $token->token;

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://live.humanox.com:5357/stats/alldata/1538/862549047780542",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlhdCI6MTYwMzU5NzIwOSwiZXhwIjoxNjA0ODA2ODA5fQ.n_L6QXFJwzNN1HhZyf97TmV0q6QvASUnYXmjY4-LuiQ",
        "Content-Type: application/x-www-form-urlencoded",
        "Cookie: connect.sid=s%3AhAApfxcuMij98LNDWKhKaqK76rYufB7S.UEtOFdohGIHg70MtoJj5P%2FT5pdkK0RYheNHcx4MxQlE"
    ),
));
echo '<hr>';
$response = curl_exec($curl);
print_r(json_decode($response));

curl_close($curl);