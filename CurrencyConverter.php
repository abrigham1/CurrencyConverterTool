<?php

// require our two helper classes
require_once(dirname(__FILE__).'/CurrencyRateFeed.php');
require_once(dirname(__FILE__).'/Database.php');

// define our short options
$shortOptions = "c:s";

$options = getopt($shortOptions);

echo "Starting Currency Converter " . date('Y-m-d H:i:s') . "\n";

// check for our -c option which means we want to convert currencies
if (array_key_exists('c', $options)) {

    $cashToConvert = explode(',', $options['c']);
    $usdValues = convertCurrency($cashToConvert);

    // if its an array return we need to loop
    if (is_array($usdValues)) {
        // loop through our values and print them out
        foreach($usdValues as $usdValue) {
            echo $usdValue . "\n";
        }
    } else {
        // else we can just print out the string
        echo $usdValues . "\n";
    }
} elseif (array_key_exists('s', $options)) {
    // save option invoked lets get the xml feed and save the new value
    pullAndSaveCurrencyRates();
} else {
    echo "To convert currencies use the -c option ex.\n\nphp CurrencyConverter.php -c \"JPY 5000\" "
        . "or php CurrencyConverter.php -c \"JPY 5000,CZK 62.5\" \n\n"
        . "To save new currencies to the database from xml use the -s option ex. \n\nphp CurrencyConverter.php -s \n\n";
}

echo "Finished running Currency Converter " . date('Y-m-d H:i:s') . "\n";

/**
 * @param array|string $cashToConvert
 * @return array|string
 */
function convertCurrency($cashToConvert)
{

    $usdValues = [];

    // problem dictates that we should be able to take in input of either a string or an array
    // in either case lets convert it to an array for ease of use
    if (!is_array($cashToConvert)) {
        $cashToConvert[] = $cashToConvert;
    }
    // instantiate our currencies array
    $currencies = [];

    $cashCount = count($cashToConvert);

    // loop through our currencies to separate the currency and rate information
    for ($i = 0; $i < $cashCount; $i++) {

        // trimming the currencies just in case some whitespace snuck in
        $cashToConvert[$i] = trim($cashToConvert[$i]);

        // split our currency names and amounts with currency name occupying [0] and amount [1]
        $cashToConvert[$i] = explode(' ', $cashToConvert[$i]);

        // the single quotes are just for our in clause this is somewhat hokey
        $currencies[] = "'".$cashToConvert[$i][0]."'";
    }

    // lets get our database connection
    $database = new Database();

    // get the rates for the currencies we have
    $currencyRates = $database->retrieveByCurrencies($currencies);

    // loop through our cash to convert so we can convert each of them
    foreach ($cashToConvert as $cash) {

        // get a handle on our cash amount
        $cashAmount = $cash[1];

        // get a handle on our currency name
        $currencyName = $cash[0];

        // looking up our rate from the currency rates array by currency name
        if (array_key_exists($currencyName, $currencyRates)) {
            $rate = $currencyRates[$currencyName];

            // add the usd value to the usd values array
            $usdValues[] = getValueInUsd($cashAmount, $rate);
        } else {
            // we couldn't find a matching rate for one passed in alert the user
            echo "Error unable to find rate for CurrencyName: {$currencyName}\n";
        }
    }

    // problem dictates that we are outputting a string if we have just 1 item to convert
    // so lets check if we have just 1 item and if so return only a string
    if (count($usdValues) == 1) {
        $usdValues = $usdValues[0];
    }

    // return our usd values
    return $usdValues;
}

/**
 * get value in usd
 *
 * @param $cashAmount
 * @param $conversionRate
 * @return string
 */
function getValueInUsd($cashAmount, $conversionRate)
{
    // to get our value in usd multiply cash amount * conversion rate limit to 2 decimal places
    return 'USD '.round($cashAmount * $conversionRate, 2);
}


/**
 * main function to pull and save currency rates
 *
 * @return int|null
 */
function pullAndSaveCurrencyRates()
{
    // instantiating CurrencyRateFeed
    $currencyRateFeed = new CurrencyRateFeed();

    // get the currency rates
    $currencyRates = $currencyRateFeed->getCurrencyRates();

    // if we have currency rates lets try and save them
    if ($currencyRates) {
        $database = new Database();

        // save the currency rates
        $insertCount = $database->saveCurrencyRates($currencyRates);

        return $insertCount;
    }

    return null;
}



