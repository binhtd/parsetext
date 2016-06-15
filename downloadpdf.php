<?php
$url = "https://www.energymadeeasy.gov.au/offer-search";
$fencingCharacter = "\n###########################################################################\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);

$postdata = array(
    "customerType" => "S",
    "postcode" => 5000,
    "distributorId_E" => "",
    "distributorId_G" => "",
    "fuelType" => "D",
    "tariff_type" => "SingleRateOffer",
    "controlled_load" => 1,
    "has_electricity_usage" => 1,
    "household_residents" => 1,
    "electricity_usage[usage][0][start][date]" => "01/03/2016",
    "electricity_usage[usage][0][end][date]" => "08/06/2016",
    "electricity_usage[usage][0][peak]" => 5500,
    "electricity_usage[usage][0][off_peak]" => "",
    "electricity_usage[usage][0][shoulder_1]" => "",
    "electricity_usage[usage][0][shoulder_2]" => "",
    "electricity_usage[usage][0][controlled_load]" => 3000,
    "gas_usage[usage][0][start][date]" => "",
    "gas_usage[usage][0][end][date]" => "",
    "gas_usage[usage][0][usage]" => "",
    "terms_conditions" => 1,
    "form_id" => "accc_offer_search_form",
    "op" => "Show energy offers",
    "has_gas_usage" => 1,
    "gas_usage[usage][0][start][date]" => "01/01/2016",
    "gas_usage[usage][0][end][date]" => "09/06/2016",
    "gas_usage[usage][0][usage]" => 333,
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookieFileName');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookieFileName');

$timeStart = microtime(true);
$htmlContent = curl_exec($ch);
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo $fencingCharacter;
echo "Consume ($time)s to processing form";
echo $fencingCharacter;

preg_match_all("|<input(.+?)name=\"form_build_id\"(.+?)>|i", $htmlContent, $out, PREG_PATTERN_ORDER);

$formID = "";
if ( (count($out) > 0) && isset($out[0][0])){
    preg_match_all("|value=\"(.+?)\"|i", $out[0][0], $out, PREG_PATTERN_ORDER);

    if ( (count($out)>1) && isset($out[1][0])){
        $formID =  $out[1][0];
    }
}

if (curl_error($ch)) {
    echo curl_error($ch);
}

//get data for electronic tab
$postdata = array(
    "controlled_load" => 1,
    "green_power" => 0,
    "estimate_period" => 1,
    "per_page" => 100,
    "current_tab" => "E",
    "op" => 1,
    "form_build_id" => $formID,
    "form_id" => "accc_offer_search_form"
);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

$timeStart = microtime(true);
$electricityResult = curl_exec($ch);
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo $fencingCharacter;
echo "Consume ($time)s to processing electronic data";
echo $fencingCharacter;


if (curl_error($ch)) {
    echo curl_error($ch);
}
$electricityResult = str_replace("\n", "", $electricityResult);
$electricityResult = str_replace("\r", "", $electricityResult);

$electronicLinkPattern = '|<tr.+?class=\"fuel-type-E.+?<td.+?<td><a(.+?)data-ga-event=\"PDF\"(.+?)>|i';
preg_match_all($electronicLinkPattern, $electricityResult, $out, PREG_PATTERN_ORDER);


$electricityLinkArray = array();

if ( isset($out[1]) && is_array($out[1])){
    foreach($out[1] as $data){
        preg_match_all('|href="(.+?)"|i', $data, $out, PREG_PATTERN_ORDER);
        if (isset($out[1])){
            $electricityLinkArray[] = $out[1][0];
        }
    }
}

downloadFile($electricityLinkArray, "electricity");

//get data for gas tab
$postdata = array(
    "controlled_load" => 1,
    "green_power" => 0,
    "estimate_period" => 1,
    "per_page" => 100,
    "current_tab" => "G",
    "op" => 1,
    "form_build_id" => $formID,
    "form_id" => "accc_offer_search_form"
);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
$timeStart = microtime(true);
$gasResult = curl_exec($ch);
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo $fencingCharacter;
echo "Consume ($time)s to processing Gas data";
echo $fencingCharacter;

if (curl_error($ch)) {
    echo curl_error($ch);
}

$gasResult = str_replace("\n", "", $gasResult);
$gasResult = str_replace("\r", "", $gasResult);
$gasLinkPattern = '|<tr.+?class=\"fuel-type-G.+?<td.+?<td><a(.+?)data-ga-event=\"PDF\"(.+?)>|i';
preg_match_all($gasLinkPattern, $gasResult, $out, PREG_PATTERN_ORDER);
$gasLinkArray = array();

if ( isset($out[1]) && is_array($out[1])){
    foreach($out[1] as $data){
        preg_match_all('|href="(.+?)"|i', $data, $out, PREG_PATTERN_ORDER);
        if (isset($out[1])){
            $gasLinkArray[] = $out[1][0];
        }
    }
}
downloadFile($gasLinkArray, "gas");

//get data for electricity & gas
$postdata = array(
    "controlled_load" => 1,
    "green_power" => 0,
    "estimate_period" => 1,
    "per_page" => 100,
    "current_tab" => "D",
    "op" => 1,
    "form_build_id" => $formID,
    "form_id" => "accc_offer_search_form"
);

curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
$timeStart = microtime(true);
$electricityAndGasResult = curl_exec($ch);
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo $fencingCharacter;
echo "Consume ($time)s to processing electronic & gas data";
echo $fencingCharacter;

if (curl_error($ch)) {
    echo curl_error($ch);
}

$electricityAndGasResult = str_replace("\n", "", $electricityAndGasResult);
$electricityAndGasResult = str_replace("\r", "", $electricityAndGasResult);
$electricityAndGasLinkPattern = '|<tr.+?class=\"fuel-type-D.+?<td.+?<td><a(.+?)data-ga-event=\"PDF\"(.+?)>|i';
preg_match_all($electricityAndGasLinkPattern, $electricityAndGasResult, $out, PREG_PATTERN_ORDER);
$electricityAndGasLinkArray = array();

if ( isset($out[1]) && is_array($out[1])){
    foreach($out[1] as $data){
        preg_match_all('|href="(.+?)"|i', $data, $out, PREG_PATTERN_ORDER);
        if (isset($out[1])){
            $electricityAndGasLinkArray[] = $out[1][0];
        }
    }
}

downloadFile($electricityAndGasLinkArray, "electricityAndGas");
curl_close($ch);


function downloadFile($linksArray, $prefix){
    $domain = "https://www.energymadeeasy.gov.au";

    foreach($linksArray as $link){
        $parts = explode("/", $link);
        $fileName = "$prefix". implode("-", $parts) . ".pdf";
        $fullFilePath = __DIR__ . "/pdf/" . $fileName;
        $fullPath = $domain . $link;
        if (file_exists($fullFilePath)){
            continue;
        }
        $timeStart = microtime(true);
        exec("wget $fullPath -O $fullFilePath -q ");
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        echo "Finished download file $fullPath ($time)s\n";
    }
}