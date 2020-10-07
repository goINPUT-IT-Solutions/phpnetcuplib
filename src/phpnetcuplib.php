<?php


namespace goINPUT\phpnetcuplib;

use Exception;
use InvalidArgumentException;
use function curl_init;

define("NETCUP_MISSING_API_KEY", 0x0457);
define("NETCUP_MISSING_API_PASSWORD", 0x0458);
define("NETCUP_MISSING_CUSTOMER_NUMBER", 0x0459);

class phpnetcuplib
{
    private string  $apiKey = "",
        $apiPassword = "",
        $customerNumber = "",
        $sessionID = "";

    private $curl;

    public function __construct(string $apiKey = null, string $apiPassword = null, string $customerNumber = null)
    {
        if (!empty ($apiKey))
            $this->setApiKey($apiKey);

        if (!empty ($apiPassword))
            $this->setApiPassword($apiPassword);

        if (!empty ($customerNumber))
            $this->setCustomerNumber($customerNumber);

        $this->curl = curl_init("https://ccp.netcup.net/run/webservice/servers/endpoint.php?JSON");
    }

    /**
     * Set API Password
     * @param $apiPassword "netcup API Password"
     */
    public function setApiPassword($apiPassword)
    {
        $this->apiPassword = $apiPassword;
    }

    /**
     * Set Customer Number
     * @param $customerNumber "netcup Customer Number"
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * Not a "real" netcup api function, but infoDomain did not that I want.
     * (Do I use it wrong?)
     * @param $domain
     * @return bool
     */
    public function checkDomainAvailability(string $domain): bool
    {
        if (gethostbyname($domain) != $domain) {
            return false;
        }
        return true;
    }

    public function login(string $apiKey = null, string $apiPassword = null, string $customerNumber = null)
    {
        if (!empty ($apiKey))
            $this->setApiKey($apiKey);

        if (!empty ($apiPassword))
            $this->setApiPassword($apiPassword);

        if (!empty ($customerNumber))
            $this->setCustomerNumber($customerNumber);

        $validateApiCredentials = $this->validateApiCredentials();

        if ($validateApiCredentials === NETCUP_MISSING_API_KEY)
            throw new InvalidArgumentException("Missing API Key for netcup API");

        if ($validateApiCredentials === NETCUP_MISSING_API_PASSWORD)
            throw new InvalidArgumentException("Missing API Password for netcup API");

        if ($validateApiCredentials === NETCUP_MISSING_CUSTOMER_NUMBER)
            throw new InvalidArgumentException("Missing Customer Number for netcup API");

        $payload = json_encode([
            "action" => "login",
            "param" => [
                "apikey" => $this->getApiKey(),
                "apipassword" => $this->getApiPassword(),
                "customernumber" => $this->getCustomerNumber()
            ]
        ]);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);
        $resultArray = json_decode($result, true);

        if ($resultArray["status"] !== "success")
            throw new Exception("Failed to login");

        $this->setSessionID($resultArray["responsedata"]["apisessionid"]);
    }

    /**
     * Validate API Credentials
     * @return bool|int
     */
    private function validateApiCredentials()
    {
        if (empty($this->getApiKey()))
            return NETCUP_MISSING_API_KEY;

        if (empty($this->getApiPassword()))
            return NETCUP_MISSING_API_PASSWORD;

        if (empty($this->getCustomerNumber()))
            return NETCUP_MISSING_CUSTOMER_NUMBER;
        return true;
    }

    /**
     * Get current API Key
     * @return string "netcup API Key"
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set API Key
     * @param $apiKey "netcup API Key"
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get current API Password
     * @return string "netcup API Password"
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * Get current Customer Number
     * @return string "netcup Customer Number"
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    public function setSessionID($sessionid)
    {
        $this->sessionID = $sessionid;
    }

    public function infoDomain(string $domain)
    {
        $payload = json_encode([
            "action" => "infoDomain",
            "param" => [
                "apikey" => $this->getApiKey(),
                "apipassword" => $this->getApiPassword(),
                "customernumber" => $this->getCustomerNumber(),
                "apisessionid" => $this->getSessionID(),
                "domainname" => $domain
            ]
        ]);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);
        var_dump($result);
    }

    public function getSessionID()
    {
        return $this->sessionID;
    }

    function __destruct()
    {
        $this->logout();
    }

    public function logout()
    {
        $payload = json_encode([
            "action" => "logout",
            "param" => [
                "apikey" => $this->getApiKey(),
                "customernumber" => $this->getCustomerNumber(),
                "apisessionid" => $this->getSessionID()
            ]
        ]);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);
        $resultArray = json_decode($result, true);

        if ($resultArray["status"] !== "success")
            throw new Exception("Failed to logout");

        curl_close($this->curl);
    }
}