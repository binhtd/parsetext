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

$retailerPattern = "/body.+?<div.+?<img.+?<p.+?<p.+?>(.*?)<p/";
preg_match_all($retailerPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $retailer = $out[1][0];
    $retailer = preg_replace("|</?.+?>|", "", $retailer);
}

$offerNamePattern = "/body.+?<div.+?<img.+?<p.+?<p.*?<p.*?>(.+?)<p/";

var_dump($retailer);

