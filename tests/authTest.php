<?php

use goINPUT\phpnetcuplib\handleType;
use goINPUT\phpnetcuplib\phpnetcuplib;

define("BASEPATH", __DIR__);

require_once(BASEPATH . "/../vendor/autoload.php");
require_once(BASEPATH . "/config.php");

try {
    global $config;
    $phpnetcuplib = new phpnetcuplib ();
    $phpnetcuplib->login($config["api_key"], $config["api_password"], $config["customer_number"]);

    $handleid = $phpnetcuplib->createHandle(
        "person",
        "Test John",
        "Test Street 1",
        99735,
        "Testhausen",
        "DE",
        "+49.15773635424",
        "test@goinput.de");

    if(!$handleid) {
        print $phpnetcuplib->getLongErrorMessage() . PHP_EOL;
        die(-1);
    }


} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    die(-1);
}