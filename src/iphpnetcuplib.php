<?php


namespace goINPUT\phpnetcuplib;


interface iphpnetcuplib
{
    /**
     * phpnetcuplib constructor.
     * @param string|null $apiKey
     * @param string|null $apiPassword
     * @param string|null $customerNumber
     */
    function __construct(string $apiKey = null,
                         string $apiPassword = null,
                         string $customerNumber = null);
    function __destruct();

    /**
     * Set API Password
     * @param $apiPassword "netcup API Password"
     */
    function setApiPassword(string $apiPassword) :void;

    /**
     * Set Customer number
     * @param int $customerNumber
     */
    function setCustomerNumber(int $customerNumber) :void;

    /**
     * Returns short error message
     * @return string
     */
    function getShortErrorMessage();

    function getLongErrorMessage();

    function login(string $apiKey = null,
                   string $apiPassword = null,
                   string $customerNumber = null);

    function getApiKey();

    function setApiKey($apiKey);

    function getApiPassword();

    function getCustomerNumber();

    function setSessionID($sessionid);

    function infoDomain(string $domain);

    function createHandle(string $type,
                          string $name,
                          string $organisation,
                          string $street,
                          int $postalCode,
                          string $city,
                          string $countryCode,
                          string $telephone,
                          string $email
    );

    function updateHandle(int $handleId,
                          string $type,
                          string $name,
                          string $organisation,
                          string $street,
                          int $postalCode,
                          string $city,
                          string $countryCode,
                          string $telephone,
                          string $email
    );

    function listAllHandles();

    function createDomain(string $domainName,
                          array $nameserver,
                          array $contacts);

    function logout();

    /**
     * Not a "real" netcup api function, but infoDomain did not that I want.
     * (Do I use it wrong?)
     * @param $domain
     * @return bool
     */
    static function checkDomainAvailability(string $domain)  :bool;
}