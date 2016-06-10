<?php

$filePath = "/home/binhtd/PhpstormProjects/parsetext/Price_FactSheet_MOJ129854SR.html";

$htmlContent = file_get_contents($filePath);

$retailer = $offerName = $offerNo = $customerType =	$fuelType =	$distributor = $tariffType = $offerType = $releaseDate = "";
$contractTerm = $contractExpiryDetails	= $billFrequency = $allUsagePrice = $dailySupplyChargePrice = $firstUsagePrice = "";
$secondUsagePrice = $thirdUsagePrice =	$fourthUagePrice = $fifthUsagePrice =	$balanceUsagePrice = $firstStep =	$secondStep ="";
$thirdStep = $fourthStep  =	$fifthStep = $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice ="";
$conditionalDiscount = $discountPercent = $discountApplicableTo =	$areThesePricesFixed =	$eligibilityCriteria =	$chequeDishonourPaymentFee = "";
$directDebitDishonourPaymentFee =	$paymentProcessingFee = $disconnectionFee =	$reconnectionFee =	$otherFee1 = $latePaymentFee = "";
$creditCardPayment = $processingFee = $otherFee2 =	$voluntaryFiT = "";

$htmlContent = str_replace("\n", "", $htmlContent);
$htmlContent = str_replace("\r", "", $htmlContent);
$htmlContent = str_replace("&#160;", " ", $htmlContent);

$retailerPattern = "/body.+?<div.+?<img.+?<p.+?<p.+?>(.*?)<p/i";
preg_match_all($retailerPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $retailer = $out[1][0];
    $retailer = preg_replace("|</?.+?>|", "", $retailer);
}

$offerNamePattern = "/body.+?<div.+?<img.+?<p.+?<p.*?<p.*?>(.+?)<p/i";
preg_match_all($offerNamePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $offerName = $out[1][0];
    $offerName = preg_replace("|</?.+?>|", "", $offerName);

    $offerNameNoArray = split("-", $offerName);

    if (count($offerNameNoArray) > 1){
        $offerName = trim($offerNameNoArray[0]);
        $offerNo = trim($offerNameNoArray[1]);
    }
}

$customerTypePattern = "|body.+?<b>Release date<\/b><\/p><p.*?>(.+?)<\/p>|i";
preg_match_all($customerTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $customerType = $out[1][0];
    $customerType = preg_replace("|</?.+?>|", "", $customerType);
}


$fuelTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){2}(.+?)<\/p>|i";
preg_match_all($fuelTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $fuelType = $out[2][0];
    $fuelType = preg_replace("|</?.+?>|", "", $fuelType);
}

$distributorPattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){3}(.+?)<\/p>|i";
preg_match_all($distributorPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $distributor = $out[2][0];
    $distributor = preg_replace("|</?.+?>|", "", $distributor);
}

$tariffTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){4}(.+?)<\/p>|i";
preg_match_all($tariffTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $tariffType = $out[2][0];
    $tariffType = preg_replace("|</?.+?>|", "", $tariffType);
}

$offerTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){5}(.+?)<\/p>|i";
preg_match_all($offerTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $offerType = $out[2][0];
    $offerType = preg_replace("|</?.+?>|", "", $offerType);
}

$releaseDatePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){6}(.+?)<\/p>|Ui";
preg_match_all($releaseDatePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $releaseDate = $out[2][0];
    $releaseDate = preg_replace("|</?.+?>|", "", $releaseDate);
}

$contracTermPattern = "|body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($contracTermPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $contracTerm = $out[2][0];
    $contracTerm = preg_replace("|</?.+?>|", "", $contracTerm);
}

$contractExpiryDetailsPattern = "|body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){4}(.+?)<p|i";
preg_match_all($contractExpiryDetailsPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $contractExpiryDetails = $out[2][0];
    $contractExpiryDetails = preg_replace("|</?.+?>|", "", $contractExpiryDetails);
}

$billFrequencyPattern = "|body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){6}(.+?)<p|i";
preg_match_all($billFrequencyPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $billFrequency = $out[2][0];
    $billFrequency = preg_replace("|</?.+?>|", "", $billFrequency);
}

$allUsagePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($allUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $allUsagePrice = $out[2][0];
    $allUsagePrice = preg_replace("|</?.+?>|", "", $allUsagePrice);
    $allUsagePrice = preg_replace("|[^\d,.]|", "", $allUsagePrice);
}

$dailySupplyChargePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
preg_match_all($dailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $dailySupplyChargePrice = $out[2][0];
    $dailySupplyChargePrice = preg_replace("|</?.+?>|", "", $dailySupplyChargePrice);
    $dailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $dailySupplyChargePrice);
}

$offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Off peak - Controlled load.+?<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
preg_match_all($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $out[2][0];
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
}

$areThesePricesFixedPattern = "|body.+?>Are these prices fixed\?<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($areThesePricesFixedPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $areThesePricesFixed = $out[2][0];
    $areThesePricesFixed = preg_replace("|</?.+?>|", "", $areThesePricesFixed);
}



$eligibilityCriteriaPattern = "|body.+?>Eligibility Criteria<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($eligibilityCriteriaPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $eligibilityCriteria = $out[2][0];
    $eligibilityCriteria = preg_replace("|</?.+?>|", "", $eligibilityCriteria);
}

$directDebitDishonourPaymentFeePattern = "|body.+?>Direct debit dishonour payment fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($directDebitDishonourPaymentFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $directDebitDishonourPaymentFee = $out[2][0];
    $directDebitDishonourPaymentFee = preg_replace("|</?.+?>|", "", $directDebitDishonourPaymentFee);
    $directDebitDishonourPaymentFee = preg_replace("|[^$\d,.]|", "", $directDebitDishonourPaymentFee);

}

$disconnectionFeePattern = "|body.+?>Disconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($disconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $disconnectionFee = $out[2][0];
    $disconnectionFee = preg_replace("|</?.+?>|", "", $disconnectionFee);
    $disconnectionFee = preg_replace("|[^$\d,. ]|", "", $disconnectionFee);
    $disconnectionFee = trim($disconnectionFee);
    $disconnectionFeeArray = split(" ", $disconnectionFee);
    $reconnectionFee = $disconnectionFeeArray[0];

}

$reconnectionFeePattern = "|body.+?>Reconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($reconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $reconnectionFee = $out[2][0];
    $reconnectionFee = preg_replace("|</?.+?>|", "", $reconnectionFee);
    $reconnectionFee = preg_replace("|[^$\d,. ]|", "", $reconnectionFee);
    $reconnectionFee = trim($reconnectionFee);
    $reconnectionFeeArray = split(" ", $reconnectionFee);
    $reconnectionFee = $reconnectionFeeArray[0];
}

$creditCardPaymentPattern = "|body.+?>Credit card payment processing fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($creditCardPaymentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $creditCardPayment = $out[2][0];
    $creditCardPayment = preg_replace("|</?.+?>|", "", $creditCardPayment);
}


$voluntaryFiTPattern = "#body.+?FiT \(Voluntary\).+?<\/p>(<p.+?>){1}(.+?)<p#";
preg_match_all($voluntaryFiTPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $voluntaryFiT = $out[2][0];
    $voluntaryFiT = preg_replace("|</?.+?>|", "", $voluntaryFiT);
}

var_dump($voluntaryFiT);