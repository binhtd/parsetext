<?php

$filePath = "/home/binhtd/PhpstormProjects/parsetext/Price_FactSheet_ORI145997MR.html";

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


$offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Off peak - Controlled load.+?<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $out[2][0];
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
}


$offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Off peak - Controlled load.+?<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
preg_match_all($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = $out[2][0];
    $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
    $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
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

    if (isset($disconnectionFeeArray)){
        $disconnectionFee = $disconnectionFeeArray[0];
    }
}

$reconnectionFeePattern = "|body.+?>Reconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($reconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $reconnectionFee = $out[2][0];
    $reconnectionFee = preg_replace("|</?.+?>|", "", $reconnectionFee);
    $reconnectionFee = preg_replace("|[^$\d,. ]|", "", $reconnectionFee);
    $reconnectionFee = trim($reconnectionFee);
    $reconnectionFeeArray = split(" ", $reconnectionFee);
    if (isset($reconnectionFeeArray[0])){
        $reconnectionFee = $reconnectionFeeArray[0];
    }
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


//second pdf form parsing
$firstUsagePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($firstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $firstUsagePrice = $out[2][0];
    $firstUsagePrice = preg_replace("|</?.+?>|", "", $firstUsagePrice);
    $firstUsagePrice = preg_replace("|[^\d,.]|", "", $firstUsagePrice);
}

$balanceUsagePricePattern = "|body.+?Remaining usage per day<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($balanceUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $balanceUsagePrice = $out[2][0];
    $balanceUsagePrice = preg_replace("|</?.+?>|", "", $balanceUsagePrice);
    $balanceUsagePrice = preg_replace("|[^\d,.]|", "", $balanceUsagePrice);
}

$firstStepPattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}(First.+?)<p|i";
preg_match_all($firstStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $firstStep = $out[2][0];
    $firstStep = preg_replace("|</?.+?>|", "", $firstStep);
    $firstStep = preg_replace("|[^\d,.]|", "", $firstStep);
}

$conditionalDiscountPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($conditionalDiscountPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $conditionalDiscount = $out[2][0];
    $conditionalDiscount = preg_replace("|</?.+?>|", "", $conditionalDiscount);
}

$discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>2) && (isset($out[2][0]))){
    $discountPercent = $out[2][0];
    $discountPercent = preg_replace("|</?.+?>|", "", $discountPercent);
    $discountPercent = preg_replace("/[^0-9,.%]/", "", $discountPercent);

    $discountPercentArray = split("%", $discountPercent);
    if (isset($discountPercentArray[0])){
        $discountPercent = $discountPercentArray[0] . "%";
    }
}

$discountApplicableToPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
preg_match_all($discountApplicableToPattern, $htmlContent, $out, PREG_PATTERN_ORDER);
if ( (count($out)>2) && (isset($out[2][0])) && preg_match("/Usage charges/i", $out[2][0])){
    $discountApplicableTo = "Usage charges";
}

$chequeDishonourPaymentFeePattern = "|body.+?<p.*?>Cheque Dishonour payment fee<\/p><p.*?>(.*?)<\/p>|i";
preg_match_all($chequeDishonourPaymentFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $chequeDishonourPaymentFee = $out[1][0];
    $chequeDishonourPaymentFee = preg_replace("/[^0-9,.$]/", "", $chequeDishonourPaymentFee);
    $chequeDishonourPaymentFeeArray = explode("$", $chequeDishonourPaymentFee);
    if (isset($chequeDishonourPaymentFeeArray[1])){
        $chequeDishonourPaymentFee = "$".$chequeDishonourPaymentFeeArray[1];
    }
}

$paymentProcessingFeePattern = "|body.+?<p.*?>Payment processing fee<\/p><p.*?>(.*?)<\/p>|i";
preg_match_all($paymentProcessingFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if ( (count($out)>1) && (isset($out[1][0]))){
    $paymentProcessingFee = $out[1][0];
    $paymentProcessingFee = preg_replace("/[^0-9,.%]/", "", $paymentProcessingFee);
    $paymentProcessingFeeArray = explode("%", $paymentProcessingFee);
    if ($paymentProcessingFeeArray[0]){
        $paymentProcessingFee = $paymentProcessingFeeArray[0]."%";
    }
}

$otherFee1Pattern = "|body.+?fees.+?>Other fee<\/p>(<p.+?>){1}(.+?)<p|i";
preg_match_all($otherFee1Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if (count($out)>2 && isset($out[2][0])){
    $otherFee1 = $out[2][0];

    $otherFee1 = preg_replace("/[^0-9,.$]/", "", $otherFee1);
    $otherFee1Array = explode("$", $otherFee1);
    if (isset($otherFee1Array[1])){
        $otherFee1 = "$".$otherFee1Array[1];
    }
}

$otherFee2Pattern = "|body.+?fees.+?>Other fee<\/p>.+?Other fee<\/p>(.+?)<p|i";
preg_match_all($otherFee2Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

if (count($out)>1 && isset($out[1][0])){
    $otherFee2  = $out[1][0];
    $otherFee2 = preg_replace("|</?.+?>|", "", $otherFee2);

    $otherFee2 = preg_replace("/[^0-9,.$]/", "", $otherFee2);
    $otherFee2Array = explode("$", $otherFee2);
    if (isset($otherFee2Array[1])){
        $otherFee2 = "$".$otherFee2Array[1];
    }
}

$htmlContentFormat2 = preg_replace( "#(<[a-zA-Z0-9]+)[^\>]+>#", "\\1>", $htmlContent );
$voluntaryFiTPattern = "|<p>inclusive \(if any\).+?<p>(.+?)<p|i";
preg_match_all($voluntaryFiTPattern, $htmlContentFormat2, $out, PREG_PATTERN_ORDER);


if ( (count($out)>1) && (isset($out[1][0]))){
    $voluntaryFiT = $out[1][0];
    $voluntaryFiT = preg_replace("|</?.+?>|", "", $voluntaryFiT);
}