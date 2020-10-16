# phpnetcuplib
phpnetcuplib is PHP library that can interface with the public reseller API of the [(excellent) ISP netcup](https://www.netcup.de). The library is currently in the WiP state. Use it with caution and feel free to commit suggestions.

## Affiliation
I am not affiliated in any way with netcup nor have I received any money for coding this software from anyone.

## Example
This example creates a new handle in your reseller account
```php
  $phpnetcuplib = new phpnetcuplib ();
  $phpnetcuplib->login($config["api_key"], $config["api_password"], $config["customer_number"]);

  $handleid = $phpnetcuplib->createHandle(
        "organisation",
        "Test John",
        "Test Org",
        "Test Street 1",
        99735,
        "Testhausen",
        "DE",
        "+49.123456789",
        "test@goinput.de");

    if (!$handleid) {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }
```

## Current supported api functions
Remember: WiP! Usage may change later.
* login
* logout
* createHandle
* infoDomain
* updateHandle
* deleteHandle
* listallHandle
* createDomain

## ToDo
Current functuons are unsupported but will added soon
* transferDomain
* listallDomains
* getAuthcodeDomain
* cancleDomain
* changeOwnerDomain
* updateDomain
* infoHandle
* priceTopleveldomain
* poll 
* ackpoll

## License
MIT

## goINPUT
This is a project of our very own [fantastic hoster goINPUT](https://goinput.de). We are currently just a small thing, but our day will come :)