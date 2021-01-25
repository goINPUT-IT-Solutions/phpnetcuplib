<?php


namespace goINPUT\phpnetcuplib\types;


class nameServerType
{
    private string $hostname = "",
        $ipv4 = "",
        $ipv6 = "";

    public function setHostname($hostname) {
        $this->hostname = $hostname;
    }

    public function getHostname() {
        return $this->hostname;
    }

    public function setIPv4($ipv4) {
        $this->ipv4 = $ipv4;
    }

    public function getIPv4() {
        return $this->ipv4;
    }

    public function setIPv6($ipv6) {
        $this->ipv6 = $ipv6;
    }

    public function getIPv6() {
        return $this->ipv6;
    }

    public function __construct($hostname = "", $ipv4 = "", $ipv6 = "") {
        if($hostname != "")
            $this->setHostname($hostname);

        if($ipv4 != "")
            $this->setIPv4($ipv4);

        if($ipv6 != "" )
            $this->setIPv6($ipv6);
    }
}