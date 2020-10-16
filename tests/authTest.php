<?php

use goINPUT\phpnetcuplib\nameServerType;
use goINPUT\phpnetcuplib\phpnetcuplib;

define("BASEPATH", __DIR__);

require_once(BASEPATH . "/../vendor/autoload.php");
require_once(BASEPATH . "/config.php");

try {
    global $config;
    $phpnetcuplib = new phpnetcuplib ();
    $phpnetcuplib->login($config["api_key"], $config["api_password"], $config["customer_number"]);

    $nameserver1 = new nameServerType();
    $nameserver1->setHostname("fred.goitdns.com");

    $nameserver2 = new nameServerType();
    $nameserver2->setHostname("josh.goitdns.com");


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
    print("Handle $handleid created." . PHP_EOL);

    if ($phpnetcuplib->updateHandle($handleid, "person", "Test John", "Test Org", "Test Street 2", 99735, "Testort", "DE", "+49.123456789", "test2@goinput.de"))
        print("Handle $handleid updated." . PHP_EOL);
    else {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }

    if ($phpnetcuplib->deleteHandle($handleid))
        print("Handle $handleid deleted." . PHP_EOL);
    else {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }

    if($phpnetcuplib->createDomain("goinput.net", array($nameserver1, $nameserver2), array("ownerc" => "5563617",
        "adminc" => "5563617",
        "techc" => "5563617",
        "zonec" => "5563617",
        "billingc" => "5563617",
        "onsitec" => "5563617",
        "generalrequest" => "5563617",
        "abusecontact" => "5563617")))
        print("Domain goinput.net registred." . PHP_EOL);
    else {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }



} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    die(-1);
} finally {
    $phpnetcuplib->logout();

}