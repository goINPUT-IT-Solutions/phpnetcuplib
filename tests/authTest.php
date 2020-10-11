<?php

use goINPUT\phpnetcuplib\phpnetcuplib;

define("BASEPATH", __DIR__);

require_once(BASEPATH . "/../vendor/autoload.php");
require_once(BASEPATH . "/config.php");

try {
    global $config;
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
    print("Handle $handleid created." . PHP_EOL);

    if ($phpnetcuplib->updateHandle($handleid, "person", "Test John", "Test Org", "Test Street 2", 99735, "Testort", "DE", "+49.123456789", "test2@goinput.de"))
        print("Handle $handleid updated." . PHP_EOL);
    else {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }

    /*if ($phpnetcuplib->deleteHandle($handleid))
        print("Handle $handleid deleted." . PHP_EOL);
    else {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }*/

} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    die(-1);
} finally {
    $phpnetcuplib->logout();
    
}