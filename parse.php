<?php

exec("which pdftohtml", $output, $returnStatus);

if (!((count($output) > 0) && ($returnStatus == 0))) {
    echo "If your distro base on redhat you can use 'sudo yum install poppler*'\n";
    echo "If your distro base on debian you can use 'sudo apt-get install poppler-utils'\n";
    die("Please install pdftohtml\n");
}

if (!is_dir(__DIR__ . "/pdf")) {
    die("Folder holds pdf does not exist inside " . __DIR__ . "\n");
}

if (file_exists(__DIR__ . "/html")) {
    system("rm -rf " . escapeshellarg(__DIR__ . "/html"));
}
mkdir(__DIR__ . "/html");
echo "##################################################################################################\n";
echo "Convert pdf to html\n";
$timeStart = microtime(true);
$totalPdfFile = 0;

exec(" find " . __DIR__ . "/pdf/" . " -name *.pdf ", $filesWithFullPath, $returnStatus);

if ((count($filesWithFullPath) < 0) || ($returnStatus != 0)) {
    die("the pdf folder didn't have any pdf file to processing");
}

foreach ($filesWithFullPath as $filename) {
    $without_extension = pathinfo($filename, PATHINFO_FILENAME);

    echo "Convert $filename\n";
    try {
        exec("pdftohtml -noframes -s $filename " . __DIR__ . "/html/$without_extension.html");
    } catch (Exception $ex) {

    }

    $totalPdfFile++;
}

$timeEnd = microtime(true);
echo "Finish convert $totalPdfFile files\n";
$time = number_format($timeEnd - $timeStart, 2);
echo "Total time convert pdf->html: {$time}s\n";
echo "##################################################################################################\n";

$csvHeader = array("PDF File Name", "Postcode", "Retailer", "Offer Name", "Offer No.", "Customer type", "Fuel type", "Distributor(s)", "Tariff type", "Offer type", "Release Date",
    "Contract term", "Contract expiry details", "Bill frequency", "All usage Price (exc. GST)", "Daily supply charge Price (exc. GST)", "First usage Price (exc. GST)",
    "Second usage Price (exc. GST)", "Third Usage Price (exc. GST)", "Fourth Uage Price (exc. GST)", "Fifth Usage Price (exc. GST)", "Balance Usage Price", "First Step",
    "Second Step", "Third Step", "Fourth Step", "Fifth Step", "Off peak - Controlled load 1 All controlled load 1 ALL USAGE Price (exc. GST)",
    "Off peak - Controlled load 1 All controlled load 1 Daily Supply Charge Price (exc. GST)", "Off peak - Controlled load 2 All controlled load 1 ALL USAGE Price (exc. GST)",
    "Off peak - Controlled load 2 All controlled load 1 Daily Supply Charge Price (exc. GST)", "Conditional Discount", "Discount %", "Discount applicable to",
    "Are these prices fixed?", "Eligibility Criteria", "Cheque Dishonour payment fee", "Direct debit dishonour payment fee", "Payment processing fee", "Disconnection fee",
    "Reconnection fee", "Other fee", "Late payment fee", "Credit card payment processing fee", "Other fee", "Voluntary FiT"
);

if (file_exists(__DIR__ . "/parse_result.csv")) {
    unlink(__DIR__ . "/parse_result.csv");
}

if (file_exists(__DIR__ . "/duplicate_files.txt")) {
    unlink(__DIR__ . "/duplicate_files.txt");
}

$handle = fopen(__DIR__ . "/parse_result.csv", "a+");
fputcsv($handle, $csvHeader);

chdir(__DIR__ . "/html/");
echo "\n\n";
echo "##################################################################################################\n";
echo "Start parse document\n";
$timeStart = microtime(true);
$totalHtmlFile = 0;

$globalContentHtmlHashes = array();
$globalDuplicateFileNames = array();
foreach (glob("*.html") as $filename) {
    $filePath = __DIR__ . "/html/$filename";
    if (!file_exists($filePath)) {
        continue;
    }

    $totalHtmlFile++;

    $htmlContent = file_get_contents($filePath);
    $htmlContentHash = md5($htmlContent);

    $globalDuplicateFileNames[$htmlContentHash][] = pathinfo($filename, PATHINFO_FILENAME) . ".pdf";
    if (!in_array($htmlContentHash, $globalContentHtmlHashes)) {
        $globalContentHtmlHashes[] = $htmlContentHash;
    } else {
        continue;
    }

    $parts = explode("-", pathinfo($filename, PATHINFO_FILENAME));
    $postCode = $parts[count($parts) - 1];
    $pdfFileName = pathinfo($filename, PATHINFO_FILENAME) . ".pdf";
    $retailer = $offerName = $offerNo = $customerType = $fuelType = $distributor = $tariffType = $offerType = $releaseDate = "";
    $contractTerm = $contractExpiryDetails = $billFrequency = $allUsagePrice = $dailySupplyChargePrice = $firstUsagePrice = "";
    $secondUsagePrice = $thirdUsagePrice = $fourthUagePrice = $fifthUsagePrice = $balanceUsagePrice = $firstStep = $secondStep = "";
    $thirdStep = $fourthStep = $fifthStep = $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = "";
    $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = $conditionalDiscount = $discountPercent = $discountApplicableTo = "";
    $areThesePricesFixed = $eligibilityCriteria = $chequeDishonourPaymentFee = $directDebitDishonourPaymentFee = $paymentProcessingFee = $disconnectionFee = $reconnectionFee = $otherFee1 = $latePaymentFee = "";
    $creditCardPaymentProcessingFee = $otherFee2 = $voluntaryFiT = "";

    $htmlContent = str_replace("\n", "", $htmlContent);
    $htmlContent = str_replace("\r", "", $htmlContent);
    $htmlContent = str_replace("&#160;", " ", $htmlContent);
    $htmlContent = str_replace("&#34;", '"', $htmlContent);


    $retailerPattern = "/body.+?<div.+?<img.+?<p.+?<p.+?>(.*?)<p/i";
    preg_match_all($retailerPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $retailer = $out[1][0];
        $retailer = preg_replace("|</?.+?>|", "", $retailer);
    }

    $offerNamePattern = "/body.+?<div.+?<img.+?<p.+?<p.*?<p.*?>(.+?)<p/i";
    preg_match_all($offerNamePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $offerName = $out[1][0];
        $offerName = preg_replace("|</?.+?>|", "", $offerName);

        $offerNameNoArray = split("-", $offerName);

        if (count($offerNameNoArray) > 1) {
            $offerName = trim($offerNameNoArray[0]);
            $offerNo = trim($offerNameNoArray[count($offerNameNoArray)-1]);
        }
    }

    $customerTypePattern = "|body.+?<b>Release date<\/b><\/p><p.*?>(.+?)<\/p>|i";
    preg_match_all($customerTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $customerType = $out[1][0];
        $customerType = preg_replace("|</?.+?>|", "", $customerType);
    }


    $fuelTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){2}(.+?)<\/p>|i";
    preg_match_all($fuelTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $fuelType = $out[2][0];
        $fuelType = preg_replace("|</?.+?>|", "", $fuelType);
    }

    $distributorPattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){3}(.+?)<\/p>|i";
    preg_match_all($distributorPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $distributor = $out[2][0];
        $distributor = preg_replace("|</?.+?>|", "", $distributor);
    }

    $tariffTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){4}(.+?)<\/p>|i";
    preg_match_all($tariffTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $tariffType = $out[2][0];
        $tariffType = preg_replace("|</?.+?>|", "", $tariffType);
    }

    $offerTypePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){5}(.+?)<\/p>|i";
    preg_match_all($offerTypePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offerType = $out[2][0];
        $offerType = preg_replace("|</?.+?>|", "", $offerType);
    }

    $releaseDatePattern = "|body.+?<b>Release date<\/b><\/p>(<p.+?>){6}(.+?)<\/p>|i";
    preg_match_all($releaseDatePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $releaseDate = $out[2][0];
        $releaseDate = preg_replace("|</?.+?>|", "", $releaseDate);
    }


    $contractTermPattern = "#body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){2}(.+?)<p#i";
    preg_match_all($contractTermPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $contractTerm = $out[2][0];
        $contractTerm = preg_replace("|</?.+?>|", "", $contractTerm);
    }

    $contractExpiryDetailsPattern = "#body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){4}(.+?)<p#i";
    preg_match_all($contractExpiryDetailsPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $contractExpiryDetails = $out[2][0];
        $contractExpiryDetails = preg_replace("|</?.+?>|", "", $contractExpiryDetails);
    }

    $billFrequencyPattern = "#body.+?<b>Electricity offer<\/b><\/p>(<p.+?>){6}(.+?)<p#i";
    preg_match_all($billFrequencyPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $billFrequency = $out[2][0];
        $billFrequency = preg_replace("|</?.+?>|", "", $billFrequency);
    }

    $allUsagePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($allUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $allUsagePrice = $out[2][0];
        $allUsagePrice = preg_replace("|</?.+?>|", "", $allUsagePrice);
        $allUsagePrice = preg_replace("|[^\d,.]|", "", $allUsagePrice);

        if (!preg_match("/All usage/i", $out[0][0])) {
            $allUsagePrice = "";
        }
    }

    $dailySupplyChargePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>(<p.+?>){1,3}<p.+?>Daily supply charge<\/p>(.+?)<p|i";
    preg_match_all($dailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $dailySupplyChargePrice = $out[2][0];
        $dailySupplyChargePrice = preg_replace("|</?.+?>|", "", $dailySupplyChargePrice);
        $dailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $dailySupplyChargePrice);
    }


    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Off peak - Controlled load 1.+?<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $out[2][0];
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
    }


    $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Off peak - Controlled load 1.+?<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
    preg_match_all($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = $out[2][0];
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
    }

    $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Off peak - Controlled load 2.+?<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($offPeakControlledLoad2AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = $out[2][0];
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice);
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice);
    }


    $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Off peak - Controlled load 2.+?<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
    preg_match_all($offPeakControlledLoad2AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = $out[2][0];
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice);
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice);
    }


    $areThesePricesFixedPattern = "|body.+?>Are these prices fixed\?<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($areThesePricesFixedPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $areThesePricesFixed = $out[2][0];
        $areThesePricesFixed = preg_replace("|</?.+?>|", "", $areThesePricesFixed);
    }

    $eligibilityCriteriaPattern = "|body.+?>Eligibility Criteria<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($eligibilityCriteriaPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $eligibilityCriteria = $out[2][0];
        $eligibilityCriteria = preg_replace("|</?.+?>|", "", $eligibilityCriteria);
    }

    $directDebitDishonourPaymentFeePattern = "|body.+?>Direct debit dishonour payment fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($directDebitDishonourPaymentFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $directDebitDishonourPaymentFee = $out[2][0];
        $directDebitDishonourPaymentFee = preg_replace("|</?.+?>|", "", $directDebitDishonourPaymentFee);
        $directDebitDishonourPaymentFee = preg_replace("|[^$\d,.]|", "", $directDebitDishonourPaymentFee);

        $directDebitDishonourPaymentFeeArray = explode("$", $directDebitDishonourPaymentFee);
        if (isset($directDebitDishonourPaymentFeeArray[1])) {
            $directDebitDishonourPaymentFee = normalizeNumber($directDebitDishonourPaymentFeeArray[1]);
        }

    }

    $disconnectionFeePattern = "|body.+?>Disconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($disconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $disconnectionFee =  $out[2][0];
        $disconnectionFee = preg_replace("|</?.+?>|", "", $disconnectionFee);
        $disconnectionFee = preg_replace("|[^$\d,.]|", "", $disconnectionFee);
        $disconnectionFee = normalizeNumber($disconnectionFee);
        $disconnectionFeeArray = explode("$", $disconnectionFee);

        if (isset($disconnectionFeeArray[1])) {
            $disconnectionFee = "$". normalizeNumber($disconnectionFeeArray[1]);
        }
    }

    $reconnectionFeePattern = "|body.+?>Reconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($reconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $reconnectionFee = normalizeNumber($out[2][0]);
        $reconnectionFee = preg_replace("|</?.+?>|", "", $reconnectionFee);
        $reconnectionFee = preg_replace("|[^$\d,. ]|", "", $reconnectionFee);
        $reconnectionFee = trim($reconnectionFee);
        $reconnectionFeeArray = split(" ", $reconnectionFee);
        if (isset($reconnectionFeeArray[0])) {
            $reconnectionFee = normalizeNumber($reconnectionFeeArray[0]);
        }

    }

    $creditCardPaymentProcessingFeePattern = "|body.+?>Credit card payment processing fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($creditCardPaymentProcessingFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $creditCardPaymentProcessingFee = $out[2][0];
        $creditCardPaymentProcessingFee = preg_replace("|</?.+?>|", "", $creditCardPaymentProcessingFee);
    }


    $voluntaryFiTPattern = "#body.+?FiT \(Voluntary\).+?<\/p>(<p.+?>){1}(.+?)<p#";
    preg_match_all($voluntaryFiTPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $voluntaryFiT = $out[2][0];
        $voluntaryFiT = preg_replace("|</?.+?>|", "", $voluntaryFiT);
    }


//second pdf form parsing
    $firstUsagePricePattern = "|body.+?<b>All consumption Anytime<\/b><\/p>((<p.+?>){2})(.+?)<p|i";
    preg_match_all($firstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $firstUsagePrice = $out[3][0];
        $firstUsagePrice = preg_replace("|</?.+?>|", "", $firstUsagePrice);
        $firstUsagePrice = preg_replace("|[^\d,.]|", "", $firstUsagePrice);

        if (isset($out[1][0]) && !preg_match("|first|i", $out[1][0])) {
            $firstUsagePrice = "";
        }
    }

    $balanceUsagePricePattern = "|body.+?Remaining usage per day<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($balanceUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $balanceUsagePrice = $out[2][0];
        $balanceUsagePrice = preg_replace("|</?.+?>|", "", $balanceUsagePrice);
        $balanceUsagePrice = preg_replace("|[^\d,.]|", "", $balanceUsagePrice);
    }

    $firstStepPattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}(First.+?)<p|i";
    preg_match_all($firstStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $firstStep = $out[2][0];
        $firstStep = preg_replace("|</?.+?>|", "", $firstStep);
        $firstStep = preg_replace("|[^\d,.]|", "", $firstStep);
    }

    $conditionalDiscountPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($conditionalDiscountPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $conditionalDiscount = $out[2][0];
        $conditionalDiscount = preg_replace("|</?.+?>|", "", $conditionalDiscount);
    }

    $discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $discountPercent = $out[2][0];
        $discountPercent = preg_replace("|</?.+?>|", "", $discountPercent);

        $discountPercent = preg_replace("/[^0-9,.%]/", "", $discountPercent);

        $discountPercentArray = split("%", $discountPercent);
        if (isset($discountPercentArray[0]) && preg_match("|^\d+[,.]*\d*$|", $discountPercentArray[0])) {
            $discountPercent = $discountPercentArray[0] . "%";
        } else {
            $discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){1}(.+?)<p|i";
            preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

            if ((count($out) > 2) && (isset($out[2][0]))) {
                $discountPercent = $out[2][0];
                $discountPercent = preg_replace("|</?.+?>|", "", $discountPercent);

                $discountPercent = preg_replace("/[^0-9,.%]/", "", $discountPercent);

                $discountPercentArray = split("%", $discountPercent);
                if (isset($discountPercentArray[0]) && preg_match("|^\d+[,.]*\d*$|", $discountPercentArray[0])) {
                    $discountPercent = $discountPercentArray[0] . "%";
                }
            }
        }
    }

    $discountApplicableToPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($discountApplicableToPattern, $htmlContent, $out, PREG_PATTERN_ORDER);
    if ((count($out) > 2) && (isset($out[2][0])) && preg_match("/Usage charges/i", $out[2][0])) {
        $discountApplicableTo = "Usage charges";
    }

    $chequeDishonourPaymentFeePattern = "|body.+?<p.*?>Cheque Dishonour payment fee<\/p><p.*?>(.*?)<\/p>|i";
    preg_match_all($chequeDishonourPaymentFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $chequeDishonourPaymentFee = $out[1][0];
        $chequeDishonourPaymentFee = preg_replace("/[^0-9,.$]/", "", $chequeDishonourPaymentFee);
        $chequeDishonourPaymentFee = normalizeNumber($chequeDishonourPaymentFee);
        $chequeDishonourPaymentFeeArray = explode("$", $chequeDishonourPaymentFee);
        if (isset($chequeDishonourPaymentFeeArray[1])) {
            $chequeDishonourPaymentFee = "$" . normalizeNumber($chequeDishonourPaymentFeeArray[1]);
        }
    }

    $paymentProcessingFeePattern = "|body.+?<p.*?>Payment processing fee<\/p><p.*?>(.*?)<\/p>|i";
    preg_match_all($paymentProcessingFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $paymentProcessingFee = $out[1][0];
        $paymentProcessingFee = preg_replace("/[^0-9,.%]/", "", $paymentProcessingFee);
        $paymentProcessingFee = normalizeNumber($paymentProcessingFee);
        $paymentProcessingFeeArray = explode("%", $paymentProcessingFee);
        if ( isset($paymentProcessingFeeArray[0])) {
            if (preg_match("|%|", $paymentProcessingFee)){
                $paymentProcessingFee = normalizeNumber($paymentProcessingFeeArray[0]) . "%";
            }else{
                $paymentProcessingFeeArray = explode("$", $paymentProcessingFee);
                    if (isset($paymentProcessingFeeArray[1])){
                    $paymentProcessingFee = "$" . normalizeNumber($paymentProcessingFeeArray[1]);
                }
            }
        }
    }

    $otherFee1Pattern = "|body.+?fees.+?>Other fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($otherFee1Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (count($out) > 2 && isset($out[2][0])) {
        $otherFee1 = $out[2][0];

        $otherFee1 = preg_replace("/\s(\d+[^,.]\d*)*\s/", "", $otherFee1);
        $otherFee1 = preg_replace("|\s[0-9,.]+\s|", "", $otherFee1);
        $otherFee1 = preg_replace("/[^0-9,.$]/", "", $otherFee1);
        $otherFee1Array = explode("$", $otherFee1);
        if (isset($otherFee1Array[1])) {
            $otherFee1 = "$" . normalizeNumber($otherFee1Array[1]);
        }
    }

    $otherFee2Pattern = "|body.+?fees.+?>Other fee<\/p>.+?Other fee<\/p>(.+?)<p|i";
    preg_match_all($otherFee2Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (count($out) > 1 && isset($out[1][0])) {
        $otherFee2 = $out[1][0];
        $otherFee2 = preg_replace("|</?.+?>|", "", $otherFee2);

        $otherFee2 = preg_replace("|\s[0-9,.]+\s|", "", $otherFee2);
        $otherFee2 = preg_replace("/[^0-9,.$]/", "", $otherFee2);
        $otherFee2Array = explode("$", $otherFee2);
        if (isset($otherFee2Array[1])) {
            $otherFee2 = "$" . normalizeNumber($otherFee2Array[1]);
        }
    }

    $htmlContentFormat2 = preg_replace("#(<[a-zA-Z0-9]+)[^\>]+>#", "\\1>", $htmlContent);
    $voluntaryFiTPattern = "|<p>inclusive \(if any\).+?<p>(.+?)<p|i";
    preg_match_all($voluntaryFiTPattern, $htmlContentFormat2, $out, PREG_PATTERN_ORDER);


    if ((count($out) > 1) && (isset($out[1][0]))) {
        $voluntaryFiT = $out[1][0];
        $voluntaryFiT = preg_replace("|</?.+?>|", "", $voluntaryFiT);
    }

#third pdf form parsing
    $contractTermPattern = "#body.+?<b>Gas offer<\/b><\/p>(<p.+?>){2}(.+?)<p#i";
    preg_match_all($contractTermPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $contractTerm = $out[2][0];
        $contractTerm = preg_replace("|</?.+?>|", "", $contractTerm);
    }

    $contractExpiryDetailsPattern = "#body.+?<b>Gas offer<\/b><\/p>(<p.+?>){4}(.+?)<p#i";
    preg_match_all($contractExpiryDetailsPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $contractExpiryDetails = $out[2][0];
        $contractExpiryDetails = preg_replace("|</?.+?>|", "", $contractExpiryDetails);
    }

    $billFrequencyPattern = "#body.+?<b>Gas offer<\/b><\/p>(<p.+?>){6}(.+?)<p#i";
    preg_match_all($billFrequencyPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $billFrequency = $out[2][0];
        $billFrequency = preg_replace("|</?.+?>|", "", $billFrequency);
    }


    $dailySupplyChargePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>(<p.+?>){1,3}<p.+?>Daily supply charge<\/p>(.+?)<p|i";
    preg_match_all($dailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $dailySupplyChargePrice = $out[2][0];
        $dailySupplyChargePrice = preg_replace("|</?.+?>|", "", $dailySupplyChargePrice);
        $dailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $dailySupplyChargePrice);
    }


    $firstUsagePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?first.+?<\/p>(.+?)<p|i";
    preg_match_all($firstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $firstUsagePrice = $out[3][0];
        $firstUsagePrice = preg_replace("|</?.+?>|", "", $firstUsagePrice);
        $firstUsagePrice = preg_replace("|[^\d,.]|", "", $firstUsagePrice);
    }

    $secondUsagePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>(.+?)<p|i";
    preg_match_all($secondUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $secondUsagePrice = $out[3][0];
        $secondUsagePrice = preg_replace("|</?.+?>|", "", $secondUsagePrice);
        $secondUsagePrice = preg_replace("|[^\d,.]|", "", $secondUsagePrice);
    }

    $thirdUsagePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>(.+?)<p|i";
    preg_match_all($thirdUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $thirdUsagePrice = $out[3][0];
        $thirdUsagePrice = preg_replace("|</?.+?>|", "", $thirdUsagePrice);
        $thirdUsagePrice = preg_replace("|[^\d,.]|", "", $thirdUsagePrice);
    }


    $fourthUagePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next.+?<\/p>(.+?)<p|i";
    preg_match_all($fourthUagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fourthUagePrice = $out[3][0];
        $fourthUagePrice = preg_replace("|</?.+?>|", "", $fourthUagePrice);
        $fourthUagePrice = preg_replace("|[^\d,.]|", "", $fourthUagePrice);
    }

    $fifthUsagePricePattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next.+?<\/p>.+?next.+?<\/p>(.+?)<p|i";
    preg_match_all($fifthUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fifthUsagePrice = $out[3][0];
        $fifthUsagePrice = preg_replace("|</?.+?>|", "", $fifthUsagePrice);
        $fifthUsagePrice = preg_replace("|[^\d,.]|", "", $fifthUsagePrice);
    }

    $firstStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?first(.+?)<\/p>|i";
    preg_match_all($firstStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);


    if ((count($out) > 3) && (isset($out[3][0]))) {
        $firstStep = $out[3][0];
        $firstStep = preg_replace("|</?.+?>|", "", $firstStep);
        $firstStep = preg_replace("|[^\d,.]|", "", $firstStep);
    }

    $secondStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next(.+?)<\/p>|i";
    preg_match_all($secondStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $secondStep = $out[3][0];
        $secondStep = preg_replace("|</?.+?>|", "", $secondStep);
        $secondStep = preg_replace("|[^\d,.]|", "", $secondStep);
        $secondStep = normalizeNumber($secondStep);
    }

    $thirdStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($thirdStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $thirdStep = $out[3][0];
        $thirdStep = preg_replace("|</?.+?>|", "", $thirdStep);
        $thirdStep = preg_replace("|[^\d,.]|", "", $thirdStep);
        $thirdStep = normalizeNumber($thirdStep);
    }

    $fourthStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($fourthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fourthStep = $out[3][0];
        $fourthStep = preg_replace("|</?.+?>|", "", $fourthStep);
        $fourthStep = preg_replace("|[^\d,.]|", "", $fourthStep);
        $fourthStep = normalizeNumber($fourthStep);
    }

    $fifthStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($fifthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fifthStep = $out[3][0];
        $fifthStep = preg_replace("|</?.+?>|", "", $fifthStep);
        $fifthStep = preg_replace("|[^\d,.]|", "", $fifthStep);
        $fifthStep = normalizeNumber($fifthStep);
    }

    $latePaymentFeePattern = "|body.+?<b>Fees<\/b><\/p>.+?Late payment fee.+?<p.+?>(.+?)<\/p>|i";
    preg_match_all($latePaymentFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $latePaymentFee = normalizeNumber($out[1][0]);
        $latePaymentFee = preg_replace("|</?.+?>|", "", $latePaymentFee);
        $latePaymentFee = preg_replace("|[^\d,.$]|", "", $latePaymentFee);

        $latePaymentFeeArray = explode("$", normalizeNumber($latePaymentFee));
        if (isset($latePaymentFeeArray[1])) {
            $latePaymentFee = "$" . normalizeNumber($latePaymentFeeArray[1]);
        }
    }


    $extractResultArray = array($pdfFileName, $postCode, $retailer, $offerName, $offerNo, $customerType, $fuelType, $distributor, $tariffType, $offerType, $releaseDate,
        $contractTerm, $contractExpiryDetails, $billFrequency, $allUsagePrice, $dailySupplyChargePrice, $firstUsagePrice,
        $secondUsagePrice, $thirdUsagePrice, $fourthUagePrice, $fifthUsagePrice, $balanceUsagePrice, $firstStep, $secondStep,
        $thirdStep, $fourthStep, $fifthStep, $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice, $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice,
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice, $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice,
        $conditionalDiscount, $discountPercent, $discountApplicableTo, $areThesePricesFixed, $eligibilityCriteria, $chequeDishonourPaymentFee,
        $directDebitDishonourPaymentFee, $paymentProcessingFee, $disconnectionFee, $reconnectionFee, $otherFee1, $latePaymentFee,
        $creditCardPaymentProcessingFee, $otherFee2, $voluntaryFiT
    );

    fputcsv($handle, $extractResultArray);
}
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo "Finish parse $totalHtmlFile files\n";
$time = number_format($timeEnd - $timeStart, 2);
echo "Total time parse document: {$time}s\n";
echo "##################################################################################################\n";
fclose($handle);

printListDuplicateFile($globalDuplicateFileNames);

function normalizeNumber($stringPresentNumber)
{
    while (in_array(substr($stringPresentNumber, 0, 1), array(",", "."))) {
        $stringPresentNumber = preg_replace('/^./', '', $stringPresentNumber);
    }

    while (in_array(substr($stringPresentNumber, -1), array(",", "."))) {
        $stringPresentNumber = preg_replace('/.$/', '', $stringPresentNumber);
    }

    return trim($stringPresentNumber);
}

function printListDuplicateFile($globalDuplicateFileNames)
{
    $hasDuplicateFile = false;
    $listDuplicateFile = array();
    foreach ($globalDuplicateFileNames as $hash => $files) {
        if (count($files) < 2) {
            continue;
        }

        $hasDuplicateFile = true;
        $i = 0;
        while ($i < count($files)) {
            $listDuplicateFile[$hash][] = $files[$i];
            $i++;
        }
    }

    if ($hasDuplicateFile) {
        $fp = fopen(__DIR__ . '/duplicate_files.txt', 'w');
        foreach ($listDuplicateFile as $hash => $files) {
            var_dump($files);
            fwrite($fp, implode("->", $files));
        }
        echo "File holds list duplicate files duplicate_files.txt\n";
        fclose($fp);
    }
}