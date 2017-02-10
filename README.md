##Usage

To download rates from xml file and save to the database run:

```bash
php CurrencyConverter.php -s
```
To convert currencies to USD run:
```bash
php CurrencyConverter.php -c "JPY 5000"
```
Or for multiple currencies
```bash
php CurrencyConverter.php -c "JPY 5000,CZK 62.5"
```
