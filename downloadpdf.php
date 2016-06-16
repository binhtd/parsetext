<?php
$url = "https://www.energymadeeasy.gov.au/offer-search";
$fencingCharacter = "\n############################################################################################################################\n";

//Download electricity for home and business user
$fuelTypes = array("E", "G");
$postCodes = array(5000, 2000, 2576, 2340, 2614, 4000, 7000);
$customeTypes = array("HOME-ELECTRICITY" => "R", "BUSINESS-ELECTRICITY" => "S");
$globalLinks = array();
foreach ($fuelTypes as $fuel) {
    switch($fuel){
        case "E":
            $customeTypes = array("HOME-ELECTRICITY" => "R", "BUSINESS-ELECTRICITY" => "S");
            break;
        case "G":
            $customeTypes = array("HOME-GAS" => "R", "BUSINESS-GAS" => "S");
            break;
        default:
            break;
    }
    foreach ($customeTypes as $folderName => $type) {
        switch($fuel){
            case "E":
                $postCodes = array(5000, 2000, 2576, 2340, 2614, 4000, 7000);
                break;
            case "G":
                $postCodes = array(5000, 2000, 2614, 4000);
                break;
            default:
                break;
        }

        foreach ($postCodes as $code) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            $postdata = array(
                "customerType" => "$type",
                "postcode" => $code,
                "distributorId_E" => "",
                "distributorId_G" => "",
                "household_residents" => 1,
                "electricity_usage[usage][0][start][date]" => "",
                "electricity_usage[usage][0][end][date]" => "",
                "electricity_usage[usage][0][peak]" => "",
                "electricity_usage[usage][0][off_peak]" => "",
                "electricity_usage[usage][0][shoulder_1]" => "",
                "electricity_usage[usage][0][shoulder_2]" => "",
                "electricity_usage[usage][0][controlled_load]" => "",
                "gas_usage[usage][0][start][date]" => "",
                "gas_usage[usage][0][end][date]" => "",
                "gas_usage[usage][0][usage]" => "",
                "terms_conditions" => 1,
                "form_id" => "accc_offer_search_form",
                "op" => "Show energy offers"
            );

            if ($fuel == "E") {
                $postdata = array_merge($postdata, array(
                    "fuelType" => "E",
                    "tariff_type" => "NotSure",
                    "controlled_load" => 1,
                    "has_electricity_usage" => 0,
                    "estimate_electricity_usage" => 0
                ));
            }

            if ($fuel == "G"){
                $postdata = array_merge($postdata, array(
                    "fuelType" => "G",
                    "has_gas_usage"=>0
                ));
            }

            if (($fuel == "G") && ($folderName == "BUSINESS-GAS")){
                $postdata = array_merge($postdata, array(
                    "estimate_electricity_usage" => 0,
                ));
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookieFileName');
            curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookieFileName');

            $timeStart = microtime(true);
            $htmlContent = curl_exec($ch);
            $timeEnd = microtime(true);
            $time = number_format($timeEnd - $timeStart, 2);
            echo $fencingCharacter;
            echo "Consume $time s to processing form (postcode=$code, customertype=$type)";
            echo $fencingCharacter;
            $formID = getFormId($htmlContent);

            if (curl_error($ch)) {
                echo curl_error($ch);
            }

            //in some case when we change per page 100 we will get error so we will try to parse link before we switch per page 100
            $linkArray = getElectricityLinks($htmlContent);
            downloadFile($linkArray, ($fuel == "E" ?  "electricity": "gas"), $folderName . "/" . $code . "/");


            //get data for electronic tab with per_page 100
            $postdata = array(
                "controlled_load" => 1,
                "green_power" => 0,
                "per_page" => 100,
                "op" => 1,
                "form_build_id" => $formID,
                "form_id" => "accc_offer_search_form"
            );

            if ($fuel == "E") {
                $postdata = array_merge($postdata, array("current_tab" => "E"));
            }

            if ($fuel == "G"){
                $postdata = array_merge($postdata, array("current_tab" => "G"));
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            $timeStart = microtime(true);
            $htmlContent = curl_exec($ch);


            if (curl_error($ch)) {
                echo curl_error($ch);
            }

            if ($fuel == "E") {
                $linkArray = getElectricityLinks($htmlContent);
                downloadFile($linkArray, ($fuel == "E" ? "electricity" : "gas"), $folderName . "/" . $code . "/");
            }

            if ($fuel == "G"){
                $linkArray = getGasLinks($htmlContent);
                downloadFile($linkArray, ($fuel == "E" ? "electricity" : "gas"), $folderName . "/" . $code . "/");
            }

            $htmlContent = str_replace("\n", "", $htmlContent);
            $htmlContent = str_replace("\r", "", $htmlContent);

            //in case there are more than 100 records we need to loop until we didn't see next button
            while (preg_match('|class=\"fuel-type-pager.+?\".+?id=\"edit-pager-next\"|i', $htmlContent)) {
                $formID = getFormId($htmlContent);
                $postdata = array(
                    "controlled_load" => 1,
                    "green_power" => 0,
                    "per_page" => 100,
                    "op" => "next",
                    "form_build_id" => $formID,
                    "form_id" => "accc_offer_search_form"
                );

                if ($fuel == "E") {
                    $postdata = array_merge($postdata, array("current_tab" => "E"));
                }

                if ($fuel == "G"){
                    $postdata = array_merge($postdata, array("current_tab" => "G"));
                }

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                $htmlContent = curl_exec($ch);

                if (curl_error($ch)) {
                    echo curl_error($ch);
                }

                 if ($fuel == "E"){
                     $electricityLinkArray = getElectricityLinks($htmlContent);
                     downloadFile($electricityLinkArray, "electricity", $folderName . "/" . $code . "/");
                 }

                if ($fuel == "G"){
                    $gasLinkArray = getGasLinks($htmlContent);
                    downloadFile($gasLinkArray, "gas", $folderName . "/" . $code . "/");
                }
            }

            $timeEnd = microtime(true);
            $time = number_format($timeEnd - $timeStart, 2);
            echo $fencingCharacter;
            echo "Consume $time s to processing " . ($fuel == "E" ?  "electricity": "gas") . " data";
            echo $fencingCharacter;
            curl_close($ch);
        }
    }
}

function getElectricityLinks($electricityResult)
{
    global $globalLinks;
    $electricityLinkArray = array();
    $electricityResult = str_replace("\n", "", $electricityResult);
    $electricityResult = str_replace("\r", "", $electricityResult);

    $electronicLinkPattern = '|<tr.+?class=\"fuel-type-E.+?<td.+?<td><a(.+?)data-ga-event=\"PDF\"(.+?)>|i';
    preg_match_all($electronicLinkPattern, $electricityResult, $out, PREG_PATTERN_ORDER);

    if (isset($out[1]) && is_array($out[1])) {
        foreach ($out[1] as $data) {
            preg_match_all('|href="(.+?)"|i', $data, $out1, PREG_PATTERN_ORDER);
            if (isset($out1[1][0]) && !(in_array($out1[1][0], $globalLinks))) {
                $globalLinks[] = $out1[1][0];
                $electricityLinkArray[] = $out1[1][0];
            }
        }
    }

    return $electricityLinkArray;
}

function getGasLinks($gasResult){
    global $globalLinks;
    $gasLinkArray = array();
    $gasResult = str_replace("\n", "", $gasResult);
    $gasResult = str_replace("\r", "", $gasResult);
    $gasLinkPattern = '|<tr.+?class=\"fuel-type-G.+?<td.+?<td><a(.+?)data-ga-event=\"PDF\"(.+?)>|i';
    preg_match_all($gasLinkPattern, $gasResult, $out, PREG_PATTERN_ORDER);

    if (isset($out[1]) && is_array($out[1])) {
        foreach ($out[1] as $data) {
            preg_match_all('|href="(.+?)"|i', $data, $out1, PREG_PATTERN_ORDER);
            if (isset($out1[1][0]) && !(in_array($out1[1][0], $globalLinks))) {
                $globalLinks[] = $out1[1][0];
                $gasLinkArray[] = $out1[1][0];
            }
        }
    }

    return $gasLinkArray;
}


function getFormId($htmlContent)
{
    $formID = "";
    preg_match_all("|name=\"form_build_id\"\s*value=\"(.+?)\"|i", $htmlContent, $out, PREG_PATTERN_ORDER);
    if ((count($out) > 0) && isset($out[0][0])) {
        preg_match_all("|value=\"(.+?)\"|i", $out[0][0], $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $formID = $out[1][0];
        }
    }

    return $formID;
}


function downloadFile($linksArray, $pdfPrefix, $folderSubfix)
{
    $domain = "https://www.energymadeeasy.gov.au";
    $folderPath = __DIR__ . "/pdf/$folderSubfix";
    exec("mkdir -p $folderPath");

    foreach ($linksArray as $link) {
        $parts = explode("/", $link);
        $fileName = "$pdfPrefix" . implode("-", $parts) . ".pdf";
        $fullPath = $domain . $link;
        if (file_exists($folderPath . $fileName)) {
            continue;
        }
        $timeStart = microtime(true);
        exec("wget $fullPath -O " . $folderPath . $fileName . " -q ");
        $timeEnd = microtime(true);
        $time = number_format($timeEnd - $timeStart, 2);
        echo "Finished download file $fullPath -> $fileName took $time s\n";
    }
}