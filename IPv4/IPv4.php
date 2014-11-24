<?php

namespace dautkom\ipv4;

/**
 * Interface iAddress.
 * Methods for IPv4 addresses management.
 */
interface iAddress
{
    public function isValid();
    public function getFormat();
    public function getMaxBlock();
    public function getHumanReadable();
    public function convertTo( $format );
}

/**
 * Interface iSubnet.
 * Methods for IPv4 subnet masks management.
 */
interface iSubnet
{
    public function isValid();
    public function isSubnet();
    public function getRange();
    public function has( $ip );
    public function getFormat();
    public function getAddress();
    public function countHosts();
    public function isBroadcast();
    public function getBroadcast();
    public function getHumanReadable();
    public function convertTo( $format );
}

/**
 * Library for IPv4 addresses management.
 * Brief documentation is avaliable on Github and in readme.
 *
 * @link    https://github.com/Haran/PHP.IPv4
 * @author  Olegs Capligins
 * @licence MIT
 */
class IPv4
{

    /**
     * Constructor checks if GMP extension is present
     * @throws \Exception
     */
    public function __construct()
    {
        if( !extension_loaded('gmp') ) {
            throw new \Exception("GMP extension must be installed and loaded");
        }
    }


    /**
     * Child initialization
     * @param $ip string
     * @return classes\Address
     */
    public function address( $ip )
    {
        return new classes\Address( $ip );
    }


    /**
     * Child initialization
     * @param $subnet string
     * @return classes\Mask
     */
    public function mask( $subnet )
    {
        return new classes\Mask( $subnet );
    }

}
