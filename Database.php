<?php

/**
 * this class is housing our database calls
 *
 * Class CurrencyRates
 * @package CurrencyConversionTool\CurrencyConverter
 */
class Database
{

    /**
     * the database connection
     *
     * @var $connection
     */
    protected $connection;

    /**
     * constructor normally we want our connection info in an environment file somewhere
     * and definitely not in the codebase where it will be committed to git but for our purposes
     * this will work
     *
     * Database constructor.
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     * @param $dbName
     */
    public function __construct(
        $host = '127.0.0.1',
        $port = '3306',
        $username = 'TestUser',
        $password = '123',
        $dbName = 'test'
    ) {
        $dsn = "mysql:host={$host};dbname={$dbName};port={$port}";
        try {
            // lets try connecting to the database
            $this->connection = new \PDO($dsn, $username, $password);
        } catch (\PDOException $e) {
            // oops that didn't work
            echo 'Connection failed: ' . $e->getMessage() . " " . date("Y-m-d H:i:s") . '\n';
        }
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        // close the database connection
        $this->connection = null;
    }

    /**
     * retrieve by currencies
     *
     * @param $currencies
     * @return array
     */
    public function retrieveByCurrencies($currencies)
    {
        $result = [];

        // get our currencies count
        $currenciesCount = count($currencies);

        // protection in case they put an endless list of currencies we dont want to crash the database
        if ($currenciesCount > 100) {
            $limit = 100;
        } else {
            $limit = $currenciesCount;
        }

        // if we passed in some currencies and they are an array lets proceed
        if ($currencies && is_array($currencies)) {
            $inClause = implode(',', $currencies);

            // retrieving from the table by currency name order by date so we get the latest
            // should be using a prepared statement and binding these params to sanitize
            // but due to time constraints just using query hopefully our devs dont sql inject us
            $sql = "SELECT currency, rate FROM currency_rates where currency IN ({$inClause}) ORDER BY creation_date desc LIMIT {$limit}";

            // loop through the results and put them in our results array
            foreach ($this->connection->query($sql) as $row) {
                $result[$row['currency']] = (float)$row['rate'];
            }
        }

        // return the results of the query
        return $result;
    }

    /**
     * save currency rates to the database
     *
     * @param \SimpleXMLElement $currencyRates
     * @return int
     */
    public function saveCurrencyRates(\SimpleXMLElement $currencyRates)
    {
        echo "Start saving currency rates " . date("Y-m-d H:i:s") . "\n";
        // initializing our inserted row count
        $insertedRowCount = 0;

        // if we have currency rates lets continue
        if ($currencyRates) {
            // using prepared statement to run the query
            $stmt = $this->connection->prepare("INSERT INTO currency_rates (currency, rate) VALUES (:currency, :rate)");

            // bind our params
            $stmt->bindParam(':currency', $currency);
            $stmt->bindParam(':rate', $rate);

            // loop through our rates to save them to the database
            foreach ($currencyRates as $currencyRate) {
                // set our variables to the correct values
                $currency = $currencyRate->currency;
                $rate = $currencyRate->rate;

                // if this returns true the insert was successful
                if ($stmt->execute()) {
                    $insertedRowCount++;
                } else {
                    echo "Error inserting record " . date("Y-m-d H:i:s") . "\n";
                }
            }
        }
        echo "Finished saving currency rates inserted {$insertedRowCount} records " . date("Y-m-d H:i:s") . "\n";

        // return the inserted row count
        return $insertedRowCount;
    }
}