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
    "Second usage Price (exc. GST)", "Third Usage Price (exc. GST)", "Fourth Uage Price (exc. GST)", "Fifth Usage Price (exc. GST)", "Balance Usage Price", "Peak", "Shoulder",
    "Off Peak", "Peak - Summer", "Peak - Winter", "Peak - First usage Price", "Peak - Second Usage Price", "Peak - Third usage Price", "Peak - Fourth Usage Price",
    "Peak - Fifth Usage Price", "Peak - Balance Price", "Summer Monthly Demand", "Winter Monthly Demand", "Additional Monthly Demand", "First Step", "Second Step", "Third Step", "Fourth Step", "Fifth Step", "Off peak - Controlled load 1 All controlled load 1 ALL USAGE Price (exc. GST)",
    "Off peak - Controlled load 1 All controlled load 1 Daily Supply Charge Price (exc. GST)", "Off peak - Controlled load 2 All controlled load 2 ALL USAGE Price (exc. GST)",
    "Off peak - Controlled load 2 All controlled load 2 Daily Supply Charge Price (exc. GST)", "Frequency", "Conditional Discount", "Discount %", "Discount applicable to", "Conditional Discount 2", "Conditional Discount 2 %", "Conditional Discount 2 Applicable to", "Guaranteed discounts",
    "Discount %", "Discount applicability", "Are these prices fixed?", "Eligibility Criteria", "Exit fee 1 year", "Exit fee 2 year", "Cheque Dishonour payment fee", "Contribution Fee", "Direct debit dishonour payment fee", "Payment processing fee", "Disconnection fee",
    "Reconnection fee", "Contribution Fee", "Other fee", "Late payment fee", "Credit card payment processing fee", "Other fee", "Voluntary FiT", "GreenPower option", "Incentives"
);

if (file_exists(__DIR__ . "/parse_result.csv")) {
    unlink(__DIR__ . "/parse_result.csv");
}

if (file_exists(__DIR__ . "/parse_result_fake.csv")) {
    unlink(__DIR__ . "/parse_result_fake.csv");
}

if (file_exists(__DIR__ . "/duplicate_files.txt")) {
    unlink(__DIR__ . "/duplicate_files.txt");
}

$handleRealData = fopen(__DIR__ . "/parse_result.csv", "a+");
$handleFakeData = fopen(__DIR__ . "/parse_result_fake.csv", "a+");
fputcsv($handleRealData, $csvHeader);
fputcsv($handleFakeData, $csvHeader);

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
    $frequency = $guaranteedDiscounts = $discountPercent2 = $discountApplicability2 = $exitFee1Year = $exitFee2Year = $contributionFee1 = $contributionFee2 = "";
    $retailer = $offerName = $offerNo = $customerType = $fuelType = $distributor = $tariffType = $offerType = $releaseDate = "";
    $contractTerm = $contractExpiryDetails = $billFrequency = $allUsagePrice = $dailySupplyChargePrice = $firstUsagePrice = "";
    $secondUsagePrice = $thirdUsagePrice = $fourthUagePrice = $fifthUsagePrice = $balanceUsagePrice = $firstStep = $secondStep = "";
    $thirdStep = $fourthStep = $fifthStep = $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = "";
    $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = $conditionalDiscount = $discountPercent = $discountApplicableTo = "";
    $areThesePricesFixed = $eligibilityCriteria = $chequeDishonourPaymentFee = $directDebitDishonourPaymentFee = $paymentProcessingFee = $disconnectionFee = $reconnectionFee = $otherFee1 = $latePaymentFee = "";
    $creditCardPaymentProcessingFee = $otherFee2 = $voluntaryFiT = $greenPowerOption = $incentives = "";
    $peak = $shoulder = $offPeak = $peakSummer = $peakWinter = $peakFirstUsagePrice = $peakSecondUsagePrice = $peakThirdUsagePrice = $peakFourthUsagePrice = $peakFifthUsagePrice = $peakBalancePrice = "";
    $summerMonthlyDemand = $winterMonthlyDemand = $additionalMonthlyDemand = "";
    $conditionalDiscount2 = $conditionalDiscount2Percentage = $conditionalDiscount2Applicableto = "";


    $htmlContent = str_replace("\n", "", $htmlContent);
    $htmlContent = str_replace("\r", "", $htmlContent);
    $htmlContent = str_replace("&#160;", " ", $htmlContent);
    $htmlContent = str_replace("&#34;", '"', $htmlContent);
    $htmlContent = str_replace("&amp;", "&", $htmlContent);


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

        $offerNameNoArray = explode("-", $offerName);

        if (count($offerNameNoArray) > 1) {
            $offerName = trim(implode("-", array_splice($offerNameNoArray, 0, count($offerNameNoArray) - 1)));
            $offerNo = trim($offerNameNoArray[count($offerNameNoArray) - 1]);
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

    $allUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){5}(.+?)<p|i";
    preg_match_all($allUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($allUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $allUsagePrice = $out[2][0];
        $allUsagePrice = preg_replace("|</?.+?>|", "", $allUsagePrice);
        $allUsagePrice = preg_replace("|[^\d,.]|", "", $allUsagePrice);

        if (!preg_match("/All usage/i", $out[0][0])) {
            $allUsagePrice = "";
        }
    }

    $allUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?all usage.+?>)(.+?)<p|i";
    preg_match_all($allUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($allUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $allUsagePrice = $out[2][0];
        $allUsagePrice = preg_replace("|</?.+?>|", "", $allUsagePrice);
        $allUsagePrice = preg_replace("|[^\d,.]|", "", $allUsagePrice);

        if (preg_match("/Daily supply charge/i", $out[0][0])) {
            $allUsagePrice = "";
        }
    }

    $allUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){6}(.+?)<p|i";
    preg_match_all($allUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($allUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
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

    $dailySupplyChargePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){1,3}<p.+?>Daily supply charge<\/p>(.+?)<p|i";
    preg_match_all($dailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($dailySupplyChargePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $dailySupplyChargePrice = $out[2][0];
        $dailySupplyChargePrice = preg_replace("|</?.+?>|", "", $dailySupplyChargePrice);
        $dailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $dailySupplyChargePrice);
    }

    $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Controlled load<\/b><\/p>.+?Controlled load.+?All usage<\/p>(.+?)<\/p>|i";
    preg_match_all($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $out[1][0];
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
    }


    $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Controlled load<\/b><\/p>.+?Controlled load.+?Daily supply charge<\/p>(.+?)<\/p>|i";
    preg_match_all($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = $out[1][0];
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
    }

    $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Controlled load<\/b><\/p>.+?Controlled load 2.+?All usage<\/p>(.+?)<\/p>|i";
    preg_match_all($offPeakControlledLoad2AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = $out[1][0];
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice);
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice);
    }


    $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Controlled load<\/b><\/p>.+?Controlled load 2.+?Daily supply charge<\/p>(.+?)<\/p>|i";
    preg_match_all($offPeakControlledLoad2AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = $out[1][0];
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice);
        $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice);
    }


    if (empty($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice)) {
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern = "|body.+?<b>Controlled load<\/b><\/p><p.+?All usage<\/p>(.+?)<p|i";
        preg_match_all($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && (isset($out[1][0]))) {
            $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = $out[1][0];
            $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);
            $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice);

            if (preg_match("/Daily supply charge/i", $out[1][0])) {
                $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = "";
            }
        }
    }


    if (empty($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice)) {
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern = "|body.+?<b>Controlled load<\/b><\/p><p.+?Daily supply charge<\/p>(.+?)<p|i";
        preg_match_all($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && (isset($out[1][0]))) {
            $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = $out[1][0];
            $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|</?.+?>|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
            $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = preg_replace("|[^\d,.]|", "", $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice);
        }
    }

    if (($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice == $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice) &&
        ($offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice == $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice)
    ) {
        $offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice = "";
        $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice = "";
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
        $disconnectionFee = $out[2][0];
        $disconnectionFee = preg_replace("|</?.+?>|", "", $disconnectionFee);
        $disconnectionFee = preg_replace("|[^$\d,.]|", "", $disconnectionFee);
        $disconnectionFee = normalizeNumber($disconnectionFee);
        $disconnectionFeeArray = explode("$", $disconnectionFee);

        if (isset($disconnectionFeeArray[1])) {
            $disconnectionFee = "$" . normalizeNumber($disconnectionFeeArray[1]);
        }
    }

    $reconnectionFeePattern = "|body.+?>Reconnection fee<\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($reconnectionFeePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $reconnectionFee = normalizeNumber($out[2][0]);
        $reconnectionFee = preg_replace("|</?.+?>|", "", $reconnectionFee);
        $reconnectionFee = preg_replace("|[^$\d,. ]|", "", $reconnectionFee);
        $reconnectionFee = trim($reconnectionFee);
        $reconnectionFeeArray = explode(" ", $reconnectionFee);
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


    $firstUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(.+?first.+?)<\/p>(.+?)<p|i";
    preg_match_all($firstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($firstUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $firstUsagePrice = $out[2][0];
        $firstUsagePrice = preg_replace("|</?.+?>|", "", $firstUsagePrice);
        $firstUsagePrice = preg_replace("|[^\d,.]|", "", $firstUsagePrice);

        $firstUsagePrice = normalizeNumber($firstUsagePrice);
        if (isset($out[1][0]) && !preg_match("|first|i", $out[1][0])) {
            $firstUsagePrice = "";
        }
    }

    $firstUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(.+?Remaining usage per day)<\/p>(.+?)<p|i";
    preg_match_all($firstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($firstUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $firstUsagePrice = $out[2][0];
        $firstUsagePrice = preg_replace("|</?.+?>|", "", $firstUsagePrice);
        $firstUsagePrice = preg_replace("|[^\d,.]|", "", $firstUsagePrice);

        $firstUsagePrice = normalizeNumber($firstUsagePrice);
    }

    $balanceUsagePricePattern = "#body.+?<b>Electricity pricing information<\/b>.+?Remaining usage per.+?<\/p>.+?Remaining usage per.+?Remaining usage per.+?<\/p>(.+?)<\/p#i";
    preg_match_all($balanceUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($balanceUsagePrice) && (count($out) > 1) && (isset($out[1][0]))) {
        $balanceUsagePrice = $out[1][0];
        $balanceUsagePrice = preg_replace("|</?.+?>|", "", $balanceUsagePrice);
        $balanceUsagePrice = preg_replace("|[^\d,.]|", "", $balanceUsagePrice);

        $balanceUsagePrice = normalizeNumber($balanceUsagePrice);
        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $balanceUsagePrice = "";
        }
    }

    $balanceUsagePricePattern = "#body.+?<b>Electricity pricing information<\/b>.+?Remaining usage per.+?<\/p>.+?Remaining usage per.+?<\/p>(.+?)<\/p#i";
    preg_match_all($balanceUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($balanceUsagePrice) && (count($out) > 1) && (isset($out[1][0]))) {
        $balanceUsagePrice = $out[1][0];
        $balanceUsagePrice = preg_replace("|</?.+?>|", "", $balanceUsagePrice);
        $balanceUsagePrice = preg_replace("|[^\d,.]|", "", $balanceUsagePrice);

        $balanceUsagePrice = normalizeNumber($balanceUsagePrice);
        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $balanceUsagePrice = "";
        }
    }

    $balanceUsagePricePattern = "#body.+?<b>Electricity pricing information<\/b>.+?Remaining usage per.+?<\/p>(.+?)<\/p#i";
    preg_match_all($balanceUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($balanceUsagePrice) && (count($out) > 1) && (isset($out[1][0]))) {
        $balanceUsagePrice = $out[1][0];
        $balanceUsagePrice = preg_replace("|</?.+?>|", "", $balanceUsagePrice);
        $balanceUsagePrice = preg_replace("|[^\d,.]|", "", $balanceUsagePrice);

        $balanceUsagePrice = normalizeNumber($balanceUsagePrice);
        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $balanceUsagePrice = "";
        }
    }

    $secondUsagePricePattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}First.+?<\/p>(.+?)<p|i";
    preg_match_all($secondUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $secondUsagePrice = $out[2][0];
        $secondUsagePrice = preg_replace("|</?.+?>|", "", $secondUsagePrice);
        $secondUsagePrice = preg_replace("|[^\d,.]|", "", $secondUsagePrice);

        $secondUsagePrice = normalizeNumber($secondUsagePrice);

    }

    $secondUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(.+?next.+?)<\/p>(.+?)<p|i";
    preg_match_all($secondUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($secondUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $secondUsagePrice = $out[2][0];
        $secondUsagePrice = preg_replace("|</?.+?>|", "", $secondUsagePrice);
        $secondUsagePrice = preg_replace("|[^\d,.]|", "", $secondUsagePrice);

        $secondUsagePrice = normalizeNumber($secondUsagePrice);
        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $secondUsagePrice = "";
        }
    }

    $secondUsagePricePattern = "#body.+?<b>Electricity pricing information<\/b>.+?Remaining usage per.+?<\/p>.+?Remaining usage per.+?<\/p>(.+?)<\/p#i";
    preg_match_all($secondUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($secondUsagePrice) && (count($out) > 1) && (isset($out[1][0]))) {
        $secondUsagePrice = $out[1][0];
        $secondUsagePrice = preg_replace("|</?.+?>|", "", $secondUsagePrice);
        $secondUsagePrice = preg_replace("|[^\d,.]|", "", $secondUsagePrice);

        $secondUsagePrice = normalizeNumber($secondUsagePrice);
        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $secondUsagePrice = "";
        }
    }

    $thirdUsagePricePattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}Next.+?<\/p>(.+?)<p|i";
    preg_match_all($thirdUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $thirdUsagePrice = $out[2][0];
        $thirdUsagePrice = preg_replace("|</?.+?>|", "", $thirdUsagePrice);
        $thirdUsagePrice = preg_replace("|[^\d,.]|", "", $thirdUsagePrice);

        $thirdUsagePrice = normalizeNumber($thirdUsagePrice);
    }


    $thirdUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){1}Next.+?Next.+?<\/p>(.+?)<p|i";
    preg_match_all($thirdUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($thirdUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $thirdUsagePrice = $out[2][0];
        $thirdUsagePrice = preg_replace("|</?.+?>|", "", $thirdUsagePrice);
        $thirdUsagePrice = preg_replace("|[^\d,.]|", "", $thirdUsagePrice);

        $thirdUsagePrice = normalizeNumber($thirdUsagePrice);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $thirdUsagePrice = "";
        }
    }

    $fourthUagePricePattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}Next.+?Next.+?Next.+?<\/p>(.+?)<p|i";
    preg_match_all($fourthUagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $fourthUagePrice = $out[2][0];
        $fourthUagePrice = preg_replace("|</?.+?>|", "", $fourthUagePrice);
        $fourthUagePrice = preg_replace("|[^\d,.]|", "", $fourthUagePrice);

        $fourthUagePrice = normalizeNumber($fourthUagePrice);
    }

    $fourthUagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){1}Next.+?Next.+?Next.+?<\/p>(.+?)<p|i";
    preg_match_all($fourthUagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($fourthUagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $fourthUagePrice = $out[2][0];
        $fourthUagePrice = preg_replace("|</?.+?>|", "", $fourthUagePrice);
        $fourthUagePrice = preg_replace("|[^\d,.]|", "", $fourthUagePrice);
        $fourthUagePrice = normalizeNumber($fourthUagePrice);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $fourthUagePrice = "";
        }
    }

    $fifthUsagePricePattern = "|body.+?<b>All Consumption Anytime<\/b><\/p>(<p.+?>){1}Next.+?Next.+?Next.+?Next.+?<\/p>(.+?)<p|i";
    preg_match_all($fifthUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $fifthUsagePrice = $out[2][0];
        $fifthUsagePrice = preg_replace("|</?.+?>|", "", $fifthUsagePrice);
        $fifthUsagePrice = preg_replace("|[^\d,.]|", "", $fifthUsagePrice);

        $fifthUsagePrice = normalizeNumber($fifthUsagePrice);
        if ($balanceUsagePrice == $fifthUsagePrice) {
            $fifthUsagePrice = "";
        }
    }

    $fifthUsagePricePattern = "|body.+?<b>Electricity pricing information<\/b><\/p>(<p.+?>){1}Next.+?Next.+?Next.+?Next.+?<\/p>(.+?)<p|i";
    preg_match_all($fifthUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($fifthUsagePrice) && (count($out) > 2) && (isset($out[2][0]))) {
        $fifthUsagePrice = $out[2][0];
        $fifthUsagePrice = preg_replace("|</?.+?>|", "", $fifthUsagePrice);
        $fifthUsagePrice = preg_replace("|[^\d,.]|", "", $fifthUsagePrice);

        $fifthUsagePrice = normalizeNumber($fifthUsagePrice);
        if ($balanceUsagePrice == $fifthUsagePrice) {
            $fifthUsagePrice = "";
        }

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $fifthUsagePrice = "";
        }
    }

    $conditionalDiscountPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($conditionalDiscountPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $conditionalDiscount = $out[2][0];
        $conditionalDiscount = preg_replace("|</?.+?>|", "", $conditionalDiscount);
    }

    $conditionalDiscount2Pattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){3}(.+?)<p|i";
    preg_match_all($conditionalDiscount2Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $conditionalDiscount2 = $out[2][0];
        $conditionalDiscount2 = preg_replace("|</?.+?>|", "", $conditionalDiscount2);
    }

    $discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){1}(.+?)<p|i";
    preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $discountPercent = $out[2][0];
        $discountPercent = preg_replace("|</?.+?>|", "", $discountPercent);

        $discountPercent = preg_replace("/[^0-9,.%]/", "", $discountPercent);
        $discountPercent = normalizeNumber($discountPercent);
        $discountPercentArray = explode("%", $discountPercent);
        if (isset($discountPercentArray[0]) && preg_match("|^\d+[,.]*\d*$|", $discountPercentArray[0])) {
            $discountPercent = $discountPercentArray[0] . "%";
            $discountApplicableTo = getDiscountApplicableTo($out[2][0]);
        } else {
            $discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
            preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

            if ((count($out) > 2) && (isset($out[2][0]))) {
                $discountPercent = $out[2][0];
                $discountPercent = preg_replace("|</?.+?>|", "", $discountPercent);

                $discountPercent = preg_replace("/[^0-9,.%]/", "", $discountPercent);
                $discountPercent = normalizeNumber($discountPercent);
                $discountPercentArray = explode("%", $discountPercent);
                if (isset($discountPercentArray[0]) && preg_match("|^\d+[,.]*\d*$|", $discountPercentArray[0])) {
                    $discountPercent = $discountPercentArray[0] . "%";
                    $discountApplicableTo = getDiscountApplicableTo($out[2][0]);
                }
            }
        }
    }

    if (empty($discountApplicableTo)){
        $discountPercentPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
        preg_match_all($discountPercentPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 2) && (isset($out[2][0]))) {
            $discountApplicableTo = getDiscountApplicableTo($out[2][0]);
        }
    }

    $conditionalDiscount2PercentagePattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){3}(.+?)<p|i";
    preg_match_all($conditionalDiscount2PercentagePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $conditionalDiscount2Percentage = $out[2][0];
        $conditionalDiscount2Percentage = preg_replace("|</?.+?>|", "", $conditionalDiscount2Percentage);

        $conditionalDiscount2Percentage = preg_replace("/[^0-9,.%]/", "", $conditionalDiscount2Percentage);
        $conditionalDiscount2Percentage = normalizeNumber($conditionalDiscount2Percentage);
        $conditionalDiscount2PercentageArray = explode("%", $conditionalDiscount2Percentage);
        if (isset($conditionalDiscount2PercentageArray[0]) && preg_match("|^\d+[,.]*\d*$|", $conditionalDiscount2PercentageArray[0])) {
            $conditionalDiscount2Percentage = $conditionalDiscount2PercentageArray[0] . "%";
            $conditionalDiscount2Applicableto = getDiscountApplicableTo($out[2][0]);
        } else {
            $conditionalDiscount2PercentagePattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){4}(.+?)<p|i";
            preg_match_all($conditionalDiscount2PercentagePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

            if ((count($out) > 2) && (isset($out[2][0]))) {
                $conditionalDiscount2Percentage = $out[2][0];
                $conditionalDiscount2Percentage = preg_replace("|</?.+?>|", "", $conditionalDiscount2Percentage);

                $conditionalDiscount2Percentage = preg_replace("/[^0-9,.%]/", "", $conditionalDiscount2Percentage);
                $conditionalDiscount2Percentage = normalizeNumber($conditionalDiscount2Percentage);
                $conditionalDiscount2PercentageArray = explode("%", $conditionalDiscount2Percentage);
                if (isset($conditionalDiscount2PercentageArray[0]) && preg_match("|^\d+[,.]*\d*$|", $conditionalDiscount2PercentageArray[0])) {
                    $conditionalDiscount2Percentage = $conditionalDiscount2PercentageArray[0] . "%";
                    $conditionalDiscount2Applicableto = getDiscountApplicableTo($out[2][0]);
                }
            }
        }
    }

    if (empty($conditionalDiscount2Applicableto)){
        $conditionalDiscount2PercentagePattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){4}(.+?)<p|i";
        preg_match_all($conditionalDiscount2PercentagePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 2) && (isset($out[2][0]))) {
            $conditionalDiscount2Applicableto = getDiscountApplicableTo($out[2][0]);
        }
    }

    if (empty($conditionalDiscount2Percentage) && empty($conditionalDiscount2Applicableto)) {
        $conditionalDiscount2 = "";
    }

    if (empty($guaranteedDiscounts)) {
        $guaranteedDiscountsPattern = "|body.+?<b>Guaranteed discounts<\/b><\/p>(.+?)<\/p>(.+?)<\/p|i";
        preg_match_all($guaranteedDiscountsPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 2) && isset($out[2][0])) {
            if (isset($out[1][0])) {
                $guaranteedDiscounts = $out[1][0];
                $guaranteedDiscounts = preg_replace("|</?.+?>|", "", $guaranteedDiscounts);
            }

            $discountPercent2 = $out[2][0];
            $discountPercent2 = preg_replace("|</?.+?>|", "", $discountPercent2);

            if (preg_match("|Consumption charges|i", $out[2][0]) && empty($discountApplicability2)) {
                $discountApplicability2 = "Consumption charges";
            }

            preg_match_all("|-?\s?([$\d.,]+%?)\s*|i", $discountPercent2, $out1, PREG_PATTERN_ORDER);
            if ((count($out1) > 1) && isset($out1[1][0])) {
                $discountPercent2 = $out1[1][0];
            }

            $discountPercent2 = normalizeNumber($discountPercent2);

            if (empty($discountPercent2)) {
                $discountPercent2 = $out[1][0];
                $discountPercent2 = preg_replace("|</?.+?>|", "", $discountPercent2);

                if (preg_match("|Consumption charges|i", $out[1][0]) && empty($discountApplicability2)) {
                    $discountApplicability2 = "Consumption charges";
                }

                preg_match_all("|-?\s?([$\d.,]+%?)\s*|i", $discountPercent2, $out2, PREG_PATTERN_ORDER);
                if ((count($out2) > 1) && isset($out2[1][0])) {
                    $discountPercent2 = $out2[1][0];
                }

                $discountPercent2 = normalizeNumber($discountPercent2);
            }

        }
    }

    $discountApplicableToPattern = "|body.+?<b>Conditional discounts<\/b><\/p>(<p.+?>){2}(.+?)<p|i";
    preg_match_all($discountApplicableToPattern, $htmlContent, $out, PREG_PATTERN_ORDER);
    if ((count($out) > 2) && (isset($out[2][0])) && preg_match("/Usage charges/i", $out[2][0]) && empty($discountApplicableTo)) {
        $discountApplicableTo = "Usage and Supply Charges";

        if (empty($discountApplicability2)) {
            $discountApplicability2 = "Usage and Supply Charges";
        }
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
        if (isset($paymentProcessingFeeArray[0])) {
            if (preg_match("|%|", $paymentProcessingFee)) {
                $paymentProcessingFee = normalizeNumber($paymentProcessingFeeArray[0]) . "%";
            } else {
                $paymentProcessingFeeArray = explode("$", $paymentProcessingFee);
                if (isset($paymentProcessingFeeArray[1])) {
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

    if (empty($firstUsagePrice) && (count($out) > 3) && (isset($out[3][0]))) {
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

        if (empty($frequency)) {
            $frequency = getFrequency($out[3][0]);
        }

    }

    $firstStepPattern = "|body.+?<b>Electricity pricing information<\/b>.+?first(.+?)<\/p>|i";
    preg_match_all($firstStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);


    if (empty($firstStep) && (count($out) > 1) && (isset($out[1][0]))) {
        $firstStep = $out[1][0];
        $firstStep = preg_replace("|</?.+?>|", "", $firstStep);
        $firstStep = preg_replace("|[^\d,.]|", "", $firstStep);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $firstStep = "";
        }

        if (empty($frequency)) {
            $frequency = getFrequency($out[1][0]);
        }
    }


    $secondStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next(.+?)<\/p>|i";
    preg_match_all($secondStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $secondStep = $out[3][0];
        $secondStep = preg_replace("|</?.+?>|", "", $secondStep);
        $secondStep = preg_replace("|[^\d,.]|", "", $secondStep);
        $secondStep = normalizeNumber($secondStep);
        if (empty($frequency)) {
            $frequency = getFrequency($out[3][0]);
        }
    }

    $secondStepPattern = "|body.+?<b>Electricity pricing information<\/b>.+?next(.+?)<\/p>|i";
    preg_match_all($secondStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($secondStep) && (count($out) > 1) && (isset($out[1][0]))) {
        $secondStep = $out[1][0];
        $secondStep = preg_replace("|</?.+?>|", "", $secondStep);
        $secondStep = preg_replace("|[^\d,.]|", "", $secondStep);
        $secondStep = normalizeNumber($secondStep);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $secondStep = "";
        }
        if (empty($frequency)) {
            $frequency = getFrequency($out[1][0]);
        }
    }

    $thirdStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($thirdStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $thirdStep = $out[3][0];
        $thirdStep = preg_replace("|</?.+?>|", "", $thirdStep);
        $thirdStep = preg_replace("|[^\d,.]|", "", $thirdStep);
        $thirdStep = normalizeNumber($thirdStep);
        if (empty($frequency)) {
            $frequency = getFrequency($out[3][0]);
        }
    }


    $thirdStepPattern = "|body.+?<b>Electricity pricing information<\/b>.+?next.+?next(.+?)<\/p>|i";
    preg_match_all($thirdStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($thirdStep) && (count($out) > 1) && (isset($out[1][0]))) {
        $thirdStep = $out[1][0];
        $thirdStep = preg_replace("|</?.+?>|", "", $thirdStep);
        $thirdStep = preg_replace("|[^\d,.]|", "", $thirdStep);
        $thirdStep = normalizeNumber($thirdStep);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $thirdStep = "";
        }

        if (empty($frequency)) {
            $frequency = getFrequency($out[1][0]);
        }
    }

    $fourthStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($fourthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fourthStep = $out[3][0];
        $fourthStep = preg_replace("|</?.+?>|", "", $fourthStep);
        $fourthStep = preg_replace("|[^\d,.]|", "", $fourthStep);
        $fourthStep = normalizeNumber($fourthStep);

        if (empty($frequency)) {
            $frequency = getFrequency($out[3][0]);
        }
    }

    $fourthStepPattern = "|body.+?<b>Electricity pricing information<\/b>.+?next.+?next.+?next(.+?)<\/p>|i";
    preg_match_all($fourthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($fourthStep) && (count($out) > 1) && (isset($out[1][0]))) {
        $fourthStep = $out[1][0];
        $fourthStep = preg_replace("|</?.+?>|", "", $fourthStep);
        $fourthStep = preg_replace("|[^\d,.]|", "", $fourthStep);
        $fourthStep = normalizeNumber($fourthStep);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $fourthStep = "";
        }

        if (empty($frequency)) {
            $frequency = getFrequency($out[1][0]);
        }
    }

    $fifthStepPattern = "|body.+?<b>Gas pricing information<\/b><\/p>((<p.+?>){2,7}).+?next.+?<\/p>.+?next.+?<\/p>.+?next.+?<\/p>.+?next(.+?)<\/p><p|i";
    preg_match_all($fifthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $fifthStep = $out[3][0];
        $fifthStep = preg_replace("|</?.+?>|", "", $fifthStep);
        $fifthStep = preg_replace("|[^\d,.]|", "", $fifthStep);
        $fifthStep = normalizeNumber($fifthStep);

        if (empty($frequency)) {
            $frequency = getFrequency($out[3][0]);
        }
    }

    $fifthStepPattern = "|body.+?<b>Electricity pricing information<\/b>.+?next.+?next.+?next.+?next(.+?)<\/p>|i";
    preg_match_all($fifthStepPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($fifthStep) && (count($out) > 1) && (isset($out[1][0]))) {
        $fifthStep = $out[1][0];
        $fifthStep = preg_replace("|</?.+?>|", "", $fifthStep);
        $fifthStep = preg_replace("|[^\d,.]|", "", $fifthStep);
        $fifthStep = normalizeNumber($fifthStep);

        if (isset($out[0][0]) && preg_match("|Daily supply charge|i", $out[0][0])) {
            $fifthStep = "";
        }

        if (empty($frequency)) {
            $frequency = getFrequency($out[1][0]);
        }
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

    $exitFee1YearPattern = "|body.+?<b>Fees<\/b><\/p>.+?Exit Fee<\/p>(.+?)<\/p>|i";
    preg_match_all($exitFee1YearPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $exitFee1Year = $out[1][0];
        $exitFee1Year = preg_replace("|</?.+?>|", "", $exitFee1Year);
        preg_match_all("|-?\s([$\d.,]+%?)\s*|i", $exitFee1Year, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $exitFee1Year = $out[1][0];
        }

        $exitFee1Year = normalizeNumber($exitFee1Year);

        $exitFeeYearArray = explode("$", $exitFee1Year);
        if (count($exitFeeYearArray) > 2) {
            $exitFee1Year = "$" . $exitFeeYearArray[1];
        }
    }

    $exitFee2YearPattern = "|body.+?<b>Fees<\/b><\/p>.+?Exit Fee<\/p>.+?Exit Fee<\/p>(.+?)<\/p>|i";
    preg_match_all($exitFee2YearPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $exitFee2Year = $out[1][0];
        $exitFee2Year = preg_replace("|</?.+?>|", "", $exitFee2Year);
        preg_match_all("|-?\s([$\d.,]+%?)\s*|i", $exitFee2Year, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $exitFee2Year = $out[1][0];
        }
        $exitFee2Year = normalizeNumber($exitFee2Year);

        $exitFeeYearArray = explode("$", $exitFee2Year);
        if (count($exitFeeYearArray) > 2) {
            $exitFee2Year = "$" . $exitFeeYearArray[1];
        }
    }

    $contributionFee1Pattern = "|body.+?<b>Fees<\/b><\/p>.+?Contribution Fee<\/p>(.+?)<\/p>|i";
    preg_match_all($contributionFee1Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $contributionFee1 = $out[1][0];
        $contributionFee1 = preg_replace("|</?.+?>|", "", $contributionFee1);
        preg_match_all("|-?\s?([$\d.,]+%?)\s*|i", $contributionFee1, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $contributionFee1 = $out[1][0];
        }

        $contributionFee1 = normalizeNumber($contributionFee1);
    }

    $contributionFee2Pattern = "|body.+?<b>Fees<\/b><\/p>.+?Membership fee<\/p>(.+?)<\/p>|i";
    preg_match_all($contributionFee2Pattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $contributionFee2 = $out[1][0];
        $contributionFee2 = preg_replace("|</?.+?>|", "", $contributionFee2);

        preg_match_all("|-?\s?([$\d.,]+%?)\s*|i", $contributionFee2, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $contributionFee2 = $out[1][0];
        }

        $contributionFee2 = normalizeNumber($contributionFee2);
    }


    $greenPowerOptionPattern = "|body.+?<b>GreenPower(<br\/>option)?<\/b><\/p>(<p.+?){6}<\/p>(<p.+?<\/p)|i";
    preg_match_all($greenPowerOptionPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 3) && (isset($out[3][0]))) {
        $greenPowerOption = $out[3][0];
        $greenPowerOption = preg_replace("|</?.+?>|", "", $greenPowerOption);

        preg_match_all("|\s?([\d.,]+%?).+?(kWh)?|i", $greenPowerOption, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $greenPowerOption = $out[1][0];
        }

        $greenPowerOption = preg_replace("/[^\d.,]/i", "", $greenPowerOption);
        $greenPowerOption = normalizeNumber($greenPowerOption);
    }


    $incentivesPattern = "|body.+?<b>Incentives<\/b><\/p>(<p.+?)<\/p|i";
    preg_match_all($incentivesPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $incentives = $out[1][0];
        $incentives = preg_replace("|</?.+?>|", "", $incentives);

        preg_match_all("|\s?([\d.,$]+)|i", $incentives, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $incentives = $out[1][0];
        }

        $incentives = normalizeNumber($incentives);

        if (!preg_match("/[\d,.$%]+/i", $incentives)) {
            $incentivesPattern = "|body.+?<b>Incentives<\/b><\/p>(<p.+?){2}<\/p|i";
            preg_match_all($incentivesPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

            if ((count($out) > 1) && (isset($out[1][0]))) {
                $incentives = $out[1][0];
                $incentives = preg_replace("|</?.+?>|", "", $incentives);

                $incentives = preg_replace("/[\d.,]+\smonth/i", "", $incentives);
                preg_match_all("|\s?([\d.,$]+)|i", $incentives, $out, PREG_PATTERN_ORDER);

                if ((count($out) > 1) && isset($out[1][0])) {
                    $incentives = $out[1][0];
                } else {
                    $incentives = "";
                }

                $incentives = normalizeNumber($incentives);
            }
        }
    }

    $peakPattern = "|body.+?<b>Peak.+?all usage<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if (empty($out[1][0])) {
        $peakPattern = "|body.+?<b>All Peak.+?all usage<\/p>(<p.+?<\/p)|i";
        preg_match_all($peakPattern, $htmlContent, $out, PREG_PATTERN_ORDER);
    }

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peak = $out[1][0];
        $peak = preg_replace("|</?.+?>|", "", $peak);

        preg_match_all("|\s?([\d.,$]+)|i", $peak, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peak = $out1[1][0];
        } else {
            $peak = "";
        }

        $peak = normalizeNumber($peak);

        if (isset($out[0][0]) && preg_match("/Remaining usage per/i", $out[0][0])) {
            $peak = "";
        }
    }

    $shoulderPattern = "|body.+?<b>Shoulder.+?all usage<\/p>(<p.+?<\/p)|i";
    preg_match_all($shoulderPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $shoulder = $out[1][0];
        $shoulder = preg_replace("|</?.+?>|", "", $shoulder);

        preg_match_all("|\s?([\d.,$]+)|i", $shoulder, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $shoulder = $out[1][0];
        } else {
            $shoulder = "";
        }

        $shoulder = normalizeNumber($shoulder);
    }

    $offPeakPattern = "|body.+?<b>Off\s*[-]?\s*peak (all)?.+?all usage<\/p>(<p.+?<\/p)|i";
    preg_match_all($offPeakPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 2) && (isset($out[2][0]))) {
        $offPeak = $out[2][0];
        $offPeak = preg_replace("|</?.+?>|", "", $offPeak);

        preg_match_all("|\s?([\d.,$]+)|i", $offPeak, $out, PREG_PATTERN_ORDER);

        if ((count($out) > 1) && isset($out[1][0])) {
            $offPeak = $out[1][0];
        } else {
            $offPeak = "";
        }

        $offPeak = normalizeNumber($offPeak);
    }

    $peakWinterPattern = "|body.+?<b>Winter.+?all usage<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakWinterPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakWinter = $out[1][0];
        $peakWinter = preg_replace("|</?.+?>|", "", $peakWinter);

        preg_match_all("|\s?([\d.,$]+)|i", $peakWinter, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakWinter = $out1[1][0];
        } else {
            $peakWinter = "";
        }

        $peakWinter = normalizeNumber($peakWinter);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakWinter = "";
        }
    }

    $peakSummerPattern = "|body.+?<b>Summer.+?all usage<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakSummerPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakSummer = $out[1][0];
        $peakSummer = preg_replace("|</?.+?>|", "", $peakSummer);

        preg_match_all("|\s?([\d.,$]+)|i", $peakSummer, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakSummer = $out1[1][0];
        } else {
            $peakSummer = "";
        }

        $peakSummer = normalizeNumber($peakSummer);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakSummer = "";
        }
    }

    $peakFirstUsagePricePattern = "|body.+?<b>Peak.+?>first.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakFirstUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakFirstUsagePrice = $out[1][0];
        $peakFirstUsagePrice = preg_replace("|</?.+?>|", "", $peakFirstUsagePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakFirstUsagePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakFirstUsagePrice = $out1[1][0];
        } else {
            $peakFirstUsagePrice = "";
        }

        $peakFirstUsagePrice = normalizeNumber($peakFirstUsagePrice);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakFirstUsagePrice = "";
        }
    }

    $peakSecondUsagePricePattern = "|body.+?<b>Peak.+?>next.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakSecondUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakSecondUsagePrice = $out[1][0];
        $peakSecondUsagePrice = preg_replace("|</?.+?>|", "", $peakSecondUsagePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakSecondUsagePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakSecondUsagePrice = $out1[1][0];
        } else {
            $peakSecondUsagePrice = "";
        }

        $peakSecondUsagePrice = normalizeNumber($peakSecondUsagePrice);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakSecondUsagePrice = "";
        }
    }

    $peakThirdUsagePricePattern = "|body.+?<b>Peak.+?>next.+?>next.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakThirdUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakThirdUsagePrice = $out[1][0];
        $peakThirdUsagePrice = preg_replace("|</?.+?>|", "", $peakThirdUsagePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakThirdUsagePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakThirdUsagePrice = $out1[1][0];
        } else {
            $peakThirdUsagePrice = "";
        }

        $peakThirdUsagePrice = normalizeNumber($peakThirdUsagePrice);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakThirdUsagePrice = "";
        }
    }

    $peakFourthUsagePricePattern = "|body.+?<b>Peak.+?>next.+?>next.+?>next.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakFourthUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakFourthUsagePrice = $out[1][0];
        $peakFourthUsagePrice = preg_replace("|</?.+?>|", "", $peakFourthUsagePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakFourthUsagePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakFourthUsagePrice = $out1[1][0];
        } else {
            $peakFourthUsagePrice = "";
        }

        $peakFourthUsagePrice = normalizeNumber($peakFourthUsagePrice);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakFourthUsagePrice = "";
        }
    }

    $peakFifthUsagePricePattern = "|body.+?<b>Peak.+?>next.+?>next.+?>next.+?>next.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakFifthUsagePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakFifthUsagePrice = $out[1][0];
        $peakFifthUsagePrice = preg_replace("|</?.+?>|", "", $peakFifthUsagePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakFifthUsagePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakFifthUsagePrice = $out1[1][0];
        } else {
            $peakFifthUsagePrice = "";
        }

        $peakFifthUsagePrice = normalizeNumber($peakFifthUsagePrice);

        if (preg_match("/Remaining usage per/i", $out[0][0])) {
            $peakFifthUsagePrice = "";
        }
    }

    $peakBalancePricePattern = "|body.+?<b>Peak.+?>Remaining usage per.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($peakBalancePricePattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $peakBalancePrice = $out[1][0];
        $peakBalancePrice = preg_replace("|</?.+?>|", "", $peakBalancePrice);

        preg_match_all("|\s?([\d.,$]+)|i", $peakBalancePrice, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $peakBalancePrice = $out1[1][0];
        }

        $peakBalancePrice = normalizeNumber($peakBalancePrice);
    }

    $summerMonthlyDemandPattern = "|body.+?<b>Capacity charges.+?>Summer Monthly Demand<\/p>(<p.+?<\/p)|i";
    preg_match_all($summerMonthlyDemandPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $summerMonthlyDemand = $out[1][0];
        $summerMonthlyDemand = preg_replace("|</?.+?>|", "", $summerMonthlyDemand);

        preg_match_all("|\s?([\d.,$]+)|i", $summerMonthlyDemand, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $summerMonthlyDemand = $out1[1][0];
        }

        $summerMonthlyDemand = normalizeNumber($summerMonthlyDemand);
    }

    $winterMonthlyDemandPattern = "|body.+?<b>Capacity charges.+?>Winter Monthly Demand<\/p>(<p.+?<\/p)|i";
    preg_match_all($winterMonthlyDemandPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $winterMonthlyDemand = $out[1][0];
        $winterMonthlyDemand = preg_replace("|</?.+?>|", "", $winterMonthlyDemand);

        preg_match_all("|\s?([\d.,$]+)|i", $winterMonthlyDemand, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $winterMonthlyDemand = $out1[1][0];
        }

        $winterMonthlyDemand = normalizeNumber($winterMonthlyDemand);
    }

    $winterMonthlyDemandPattern = "|body.+?<b>Capacity charges.+?>Additional Monthly Demand.+?<\/p>(<p.+?<\/p)|i";
    preg_match_all($winterMonthlyDemandPattern, $htmlContent, $out, PREG_PATTERN_ORDER);

    if ((count($out) > 1) && (isset($out[1][0]))) {
        $additionalMonthlyDemand = $out[1][0];
        $additionalMonthlyDemand = preg_replace("|</?.+?>|", "", $additionalMonthlyDemand);

        preg_match_all("|\s?([\d.,$]+)|i", $additionalMonthlyDemand, $out1, PREG_PATTERN_ORDER);

        if ((count($out1) > 1) && isset($out1[1][0])) {
            $additionalMonthlyDemand = $out1[1][0];
        }

        $additionalMonthlyDemand = normalizeNumber($additionalMonthlyDemand);
    }

    $csvData = array($pdfFileName, $postCode, $retailer, $offerName, $offerNo, $customerType, $fuelType, $distributor, $tariffType, $offerType, $releaseDate,
        $contractTerm, $contractExpiryDetails, $billFrequency, ($allUsagePrice), ($dailySupplyChargePrice), ($firstUsagePrice),
        ($secondUsagePrice), ($thirdUsagePrice), ($fourthUagePrice), ($fifthUsagePrice), ($balanceUsagePrice), ($peak), ($shoulder), ($offPeak),
        ($peakSummer), ($peakWinter), ($peakFirstUsagePrice), ($peakSecondUsagePrice), ($peakThirdUsagePrice),
        ($peakFourthUsagePrice), ($peakFifthUsagePrice), ($peakBalancePrice), ($summerMonthlyDemand), ($winterMonthlyDemand), ($additionalMonthlyDemand), ($firstStep), ($secondStep),
        ($thirdStep), ($fourthStep), ($fifthStep), ($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice), $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice,
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice, $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice, $frequency,
        $conditionalDiscount, $discountPercent, $discountApplicableTo, $conditionalDiscount2, $conditionalDiscount2Percentage, $conditionalDiscount2Applicableto, $guaranteedDiscounts, $discountPercent2, $discountApplicability2, $areThesePricesFixed, $eligibilityCriteria,
        $exitFee1Year, $exitFee2Year, $chequeDishonourPaymentFee, $contributionFee1, $directDebitDishonourPaymentFee, $paymentProcessingFee, $disconnectionFee, $reconnectionFee, $contributionFee2,
        $otherFee1, $latePaymentFee, $creditCardPaymentProcessingFee, $otherFee2, $voluntaryFiT, $greenPowerOption, $incentives,
    );
    fputcsv($handleRealData, $csvData);

    $csvData = array($pdfFileName, $postCode, $retailer, $offerName, $offerNo, $customerType, $fuelType, $distributor, $tariffType, $offerType, $releaseDate,
        $contractTerm, $contractExpiryDetails, $billFrequency, fakeData($allUsagePrice), fakeData($dailySupplyChargePrice), fakeData($firstUsagePrice),
        fakeData($secondUsagePrice), fakeData($thirdUsagePrice), fakeData($fourthUagePrice), fakeData($fifthUsagePrice), fakeData($balanceUsagePrice), fakeData($peak), fakeData($shoulder), fakeData($offPeak),
        fakeData($peakSummer), fakeData($peakWinter), fakeData($peakFirstUsagePrice), fakeData($peakSecondUsagePrice), fakeData($peakThirdUsagePrice),
        fakeData($peakFourthUsagePrice), fakeData($peakFifthUsagePrice), fakeData($peakBalancePrice), fakeData($summerMonthlyDemand), fakeData($winterMonthlyDemand), fakeData($additionalMonthlyDemand), fakeData($firstStep), fakeData($secondStep),
        fakeData($thirdStep), fakeData($fourthStep), fakeData($fifthStep), fakeData($offPeakControlledLoad1AllControlledLoad1ALLUSAGEPrice), $offPeakControlledLoad1AllControlledLoad1DailySupplyChargePrice,
        $offPeakControlledLoad2AllControlledLoad1ALLUSAGEPrice, $offPeakControlledLoad2AllControlledLoad1DailySupplyChargePrice, $frequency,
        $conditionalDiscount, $discountPercent, $discountApplicableTo, $conditionalDiscount2, $conditionalDiscount2Percentage, $conditionalDiscount2Applicableto, $guaranteedDiscounts, $discountPercent2, $discountApplicability2, $areThesePricesFixed, $eligibilityCriteria,
        $exitFee1Year, $exitFee2Year, $chequeDishonourPaymentFee, $contributionFee1, $directDebitDishonourPaymentFee, $paymentProcessingFee, $disconnectionFee, $reconnectionFee, $contributionFee2,
        $otherFee1, $latePaymentFee, $creditCardPaymentProcessingFee, $otherFee2, $voluntaryFiT, $greenPowerOption, $incentives,
    );

    fputcsv($handleFakeData, $csvData);
}
$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
echo "Finish parse $totalHtmlFile files\n";
$time = number_format($timeEnd - $timeStart, 2);
echo "Total time parse document: {$time}s\n";
echo "##################################################################################################\n";

fclose($handleFakeData);
fclose($handleRealData);

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

function getFrequency($htmlContent)
{
    $frequency = "per kWh";

    if (preg_match("/(per day)/i", $htmlContent)) {
        $frequency = "per day";
    }

    if (preg_match("/(per month)/i", $htmlContent)) {
        $frequency = "per month";
    }

    if (preg_match("/(per quarter)/i", $htmlContent)) {
        $frequency = "per quarter";
    }

    return $frequency;
}

function fakeData($number)
{
    if (!is_numeric($number)) {
        return $number;
    }

    if (rand(1, 10) >= 5) {
        return $number * 1.05;
    } else {
        return $number * 0.95;
    }
}

function getDiscountApplicableTo($discountInformation)
{
    $discountApplicableTo = "";

    if (preg_match("/energy/i", $discountInformation) || preg_match("/supply/i", $discountInformation) || preg_match("/total bill/i", $discountInformation)) {
        $discountApplicableTo = "Supply and Energy Charges";
    }

    return $discountApplicableTo;
}