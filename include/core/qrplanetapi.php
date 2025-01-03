<?php
namespace LWS\WOOREWARDS\PRO\Core;

// don't call the file directly
if( !defined( 'ABSPATH' ) ) exit();

/** Manage requests to QR Planet API */
class QrPlanetApi
{
    function issueOneTimeCoupon($apiDomain='', $secretKey='', $shorturl='')
    {
        $action      = "issue";
        $jsonurl     = "$apiDomain/api/coupon/$action?secretkey=$secretKey&shorturl=$shorturl";
        $json        = file_get_contents($jsonurl, 0, null);
        $json_output = json_decode($json);

        $redeem_url = $json_output->result->url;

        return $redeem_url;
    }
}