<?php


namespace goINPUT\phpnetcuplib;

use Exception;
use InvalidArgumentException;
use function curl_init;

define("NETCUP_MISSING_API_KEY", 0x0457);
define("NETCUP_MISSING_API_PASSWORD", 0x0458);
define("NETCUP_MISSING_CUSTOMER_NUMBER", 0x0459);
define("PHPNETCUPLIB_VERSION", "1.0.0");

class phpnetcuplib
{
    private string  $apiKey = "",
        $apiPassword = "",
        $customerNumber = "",
        $sessionID = "",
        $errorShortMessage = "",
        $errorLongMessage = "",
        $clientIdPrefix = "";

    private bool $loggedOut = false;

    private $curl;

    /**
     * phpnetcuplib constructor.
     * @param string|null $apiKey
     * @param string|null $apiPassword
     * @param string|null $customerNumber
     */
    public function __construct(string $apiKey = null, string $apiPassword = null, string $customerNumber = null)
    {
        if (!empty ($apiKey))
            $this->setApiKey($apiKey);

        if (!empty ($apiPassword))
            $this->setApiPassword($apiPassword);

        if (!empty ($customerNumber))
            $this->setCustomerNumber($customerNumber);

        $this->curl = curl_init("https://ccp.netcup.net/run/webservice/servers/endpoint.php?JSON");
        curl_setopt($this->curl, CURLOPT_USERAGENT, "phpnetcuplib/" . PHPNETCUPLIB_VERSION . " (https://github.com/goINPUT-IT-Solutions/phpnetcuplib)");

        $this->clientIdPrefix = littleHelpers::generateRandomString(8);
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
    static public function checkDomainAvailability(string $domain): bool
    {
        if (gethostbyname($domain) != $domain) {
            return false;
        }
        return true;
    }

    /**
     * Returns short error message
     * @return string
     */
    public function getShortErrorMessage()
    {
        return $this->errorShortMessage;
    }

    /** Returns long error message
     * @return string
     */
    public function getLongErrorMessage()
    {
        return $this->errorLongMessage;
    }

    /**
     * @param string|null $apiKey
     * @param string|null $apiPassword
     * @param string|null $customerNumber
     * @return bool
     */
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

        $params = [
            "apikey" => $this->getApiKey(),
            "apipassword" => $this->getApiPassword(),
            "customernumber" => $this->getCustomerNumber()
        ];

        $resultArray = $this->performAction("login", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }

        $this->setSessionID($resultArray["responsedata"]["apisessionid"]);
        return true;
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

    /**
     * Runs requests for the netcup API
     * @param string $action Netcup API action
     * @param array $params Array with params
     * @return array Returns response array
     */
    private function performAction(string $action, array $params)
    {
        $params["clientrequestid"] = $this->clientIdPrefix . littleHelpers::generateRandomString(6);

        $payload = json_encode([
            "action" => "$action",
            "param" => $params
        ]);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($this->curl);
        return (json_decode($result, true));
    }

    /**
     * Sets returned error from netcup api
     * @param string $shortMessage
     * @param string $longMessage
     */
    private function setErrorMessage(string $shortMessage, string $longMessage)
    {
        $this->errorLongMessage = $longMessage;
        $this->errorShortMessage = $shortMessage;
    }

    /**
     * Sets current session id
     * @param $sessionid netcup API session ID
     */
    public function setSessionID($sessionid)
    {
        $this->sessionID = $sessionid;
    }

    /**
     * @param string $domain
     * @return array
     */
    public function infoDomain(string $domain)
    {
        $params = [
            "apikey" => $this->getApiKey(),
            "apipassword" => $this->getApiPassword(),
            "customernumber" => $this->getCustomerNumber(),
            "apisessionid" => $this->getSessionID(),
            "domainname" => $domain
        ];

        $resultArray = $this->performAction("infoDomain", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }
        return $resultArray;
    }

    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * Creates new handle
     * @param string $type Type of the handle like organisation or person
     * @param string $name Full name of the contact.
     * @param string $street Street
     * @param int $postalCode Postal code
     * @param string $city City
     * @param string $countryCode Country code in ISO 3166 ALPHA-2 format. 2 char codes like CH for Switzerland.
     * @param string $telephone Telephone number
     * @param string $email Email address
     * @param string $organisation Organisation like company name.
     * @return int Handle Id
     */
    public function createHandle(
        string $type,
        string $name,
        string $organisation,
        string $street,
        int $postalCode,
        string $city,
        string $countryCode,
        string $telephone,
        string $email
    )
    {

        if (strlen($name) > 80)
            throw new InvalidArgumentException("Name too long");

        if (strlen($street) > 63)
            throw new InvalidArgumentException("Street name too long");

        if (!preg_match('/[0-9]{0,12}/', $postalCode))
            throw new InvalidArgumentException("Postal code too long");

        if (strlen($city) > 63)
            throw new InvalidArgumentException("City name too long");

        if (strlen($countryCode) > 2)
            throw new InvalidArgumentException("Country code too long");

        if (!preg_match('/\+[0-9]{1,4}\.([0-9]{1,57})/', $telephone))
            throw new InvalidArgumentException("Telephone number has wrong format");

        $params = [
            "customernumber" => $this->getCustomerNumber(),
            "apikey" => $this->getApiKey(),
            "apisessionid" => $this->getSessionID(),
            "type" => $type,
            "name" => $name,
            "organisation" => $organisation,
            "street" => $street,
            "postalcode" => $postalCode,
            "city" => $city,
            "countrycode" => $countryCode,
            "telephone" => $telephone,
            "email" => $email,
        ];

        $resultArray = $this->performAction("createHandle", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }

        return ((int)$resultArray["responsedata"]["id"]);
    }

    function __destruct()
    {
        try {
            $this->logout();
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Logout from netcup API
     * @return bool True if logout successful
     */
    public function logout()
    {
        if($this->loggedOut != true) {
            $params = [
                "apikey" => $this->getApiKey(),
                "customernumber" => $this->getCustomerNumber(),
                "apisessionid" => $this->getSessionID()
            ];

            $resultArray = $this->performAction("logout", $params);

            if ($resultArray["status"] !== "success") {
                $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
                return false;
            }

            curl_close($this->curl);
            $this->loggedOut = true;
            return true;
        }
        return false;
    }

    /**
     * Update handle
     * @param int $handleId Id of the contact that will be updated
     * @param string $type type of the handle like organisation or person
     * @param string $name full name of the contact
     * @param string $organisation organisation like company name
     * @param string $street street
     * @param int $postalCode postcode
     * @param string $city city
     * @param string $countryCode countrycode in ISO 3166 ALPHA-2 format. 2 char codes like CH for Switzerland
     * @param string $telephone telephone number
     * @param string $email email address
     * @return bool True if logout successful
     */
    public function updateHandle(
        int $handleId,
        string $type,
        string $name,
        string $organisation,
        string $street,
        int $postalCode,
        string $city,
        string $countryCode,
        string $telephone,
        string $email
    )
    {

        if (strlen($name) > 80)
            throw new InvalidArgumentException("Name too long");

        if (strlen($street) > 63)
            throw new InvalidArgumentException("Street name too long");

        if (!preg_match('/[0-9]{0,12}/', $postalCode))
            throw new InvalidArgumentException("Postal code too long");

        if (strlen($city) > 63)
            throw new InvalidArgumentException("City name too long");

        if (strlen($countryCode) > 2)
            throw new InvalidArgumentException("Country code too long");

        if (!preg_match('/\+[0-9]{1,4}\.([0-9]{1,57})/', $telephone))
            throw new InvalidArgumentException("Telephone number has wrong format");

        $params = [
            "customernumber" => $this->getCustomerNumber(),
            "apikey" => $this->getApiKey(),
            "apisessionid" => $this->getSessionID(),
            "handle_id" => $handleId,
            "type" => $type,
            "name" => $name,
            "organisation" => $organisation,
            "street" => $street,
            "postalcode" => $postalCode,
            "city" => $city,
            "countrycode" => $countryCode,
            "telephone" => $telephone,
            "email" => $email,
        ];

        $resultArray = $this->performAction("updateHandle", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }
        return true;
    }

    /**
     * Deletes handle
     * @param int $handleId Id of the contact that will be updated
     * @return bool True if logout successful
     */
    public function deleteHandle(int $handleId) {
        $params = [
            "customernumber" => $this->getCustomerNumber(),
            "apikey" => $this->getApiKey(),
            "apisessionid" => $this->getSessionID(),
            "handle_id" => $handleId
        ];

        $resultArray = $this->performAction("deleteHandle", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }
        return true;
    }

    /**
     * List all handles in netcup account
     * @return array|false netcup response or false
     */
    public function listAllHandles() {
        $params = [
            "customernumber" => $this->getCustomerNumber(),
            "apikey" => $this->getApiKey(),
            "apisessionid" => $this->getSessionID()
        ];

        $resultArray = $this->performAction("deleteHandle", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }
        return $resultArray;
    }

    public function createDomain(string $domainName, array $nameserver, array $contacts) {
        $nameserverId = 1;
        $nameserverArray = [];

        foreach ($nameserver as $nameserverItem) {
            $nameserverArray["nameserver" . $nameserverId]["hostname"] = $nameserverItem->getHostname();
            $nameserverArray["nameserver" . $nameserverId]["ipv4"] = $nameserverItem->getIPv4();
            $nameserverArray["nameserver" . $nameserverId]["ipv6"] = $nameserverItem->getIPv6();

            $nameserverId++;
            if($nameserverId > 8)
                throw new Exception("Too many nameserver");
        }

        $params = [
            "domainname" => $domainName,
            "customernumber" => $this->getCustomerNumber(),
            "contacts" => $contacts,
            "nameservers" => $nameserverArray,
            "apikey" => $this->getApiKey(),
            "apisessionid" => $this->getSessionID()
        ];

        $resultArray = $this->performAction("createDomain", $params);

        if ($resultArray["status"] !== "success") {
            $this->setErrorMessage($resultArray["shortmessage"], $resultArray["longmessage"]);
            return false;
        }
        return true;
    }

}
