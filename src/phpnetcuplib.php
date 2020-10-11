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

    public function setSessionID($sessionid)
    {
        $this->sessionID = $sessionid;
    }

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
        return true;
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

}
