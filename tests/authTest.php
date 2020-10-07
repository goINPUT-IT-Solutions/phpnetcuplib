<?php

use goINPUT\phpnetcuplib\phpnetcuplib;

define("BASEPATH", __DIR__);

require_once(BASEPATH . "/../vendor/autoload.php");
require_once(BASEPATH . "/config.php");

try {
    global $config;
    $phpnetcuplib = new phpnetcuplib ();
    $phpnetcuplib->login($config["api_key"], $config["api_password"], $config["customer_number"]);
    var_dump($phpnetcuplib->checkDomainAvailability("goinput.net"));

} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    die(-1);
}