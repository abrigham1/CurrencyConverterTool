<?php

/**
 * this class deals with getting rates from the xml feed
 *
 * Class CurrencyRateFeed
 * @package CurrencyConversionTool\CurrencyRateFeed
 */
class CurrencyRateFeed
{

    /**
     * the feed url
     *
     * @var string
     */
    protected $feedUrl;

    /**
     * constructor
     *
     * CurrencyRateFeed constructor.
     * @param string $feedUrl
     */
    public function __construct(
        $feedUrl = "https://wikitech.wikimedia.org/wiki/Fundraising/tech/Currency_conversion_sample?ctype=text/xml&action=raw"
    ) {
        $this->feedUrl = $feedUrl;
    }

    /**
     * get the currency rates
     *
     * @param null $feedUrl
     * @return null|\SimpleXMLElement
     */
    public function getCurrencyRates($feedUrl = null)
    {
        echo "Fetching currency rates from xml " . date("Y-m-d H:i:s") . "\n";
        // if we passed in a feed url lets override our current feed url
        if ($feedUrl) {
            $this->feedUrl = $feedUrl;
        }

        // lets try to get the xml from the feed url
        if (($feed = file_get_contents($this->feedUrl)) === false) {
            echo "Error fetching XML " . date("Y-m-d H:i:s") . "\n";
        }

        // turning this on so we can ouput errors if need be
        libxml_use_internal_errors(true);

        // lets load our xml element from our feed
        $xmlElement = simplexml_load_string($feed);

        // if we dont have items that means there are errors lets output them
        if (!$xmlElement) {
            echo "Error loading XML" . date("Y-m-d H:i:s") . "\n";
            foreach (libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }

            // return null there were errors
            return null;
        } else {
            // we have the xml lets return it
            echo "Finished fetching currency rates from xml " . date("Y-m-d H:i:s") . "\n";
            return $xmlElement;
        }
    }
}
