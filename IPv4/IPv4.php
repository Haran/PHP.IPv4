<?php

require_once('classes' . DIRECTORY_SEPARATOR . 'Transforms.php');
require_once('classes' . DIRECTORY_SEPARATOR . 'Address.php');
require_once('classes' . DIRECTORY_SEPARATOR . 'Mask.php');

/**
 * Library for IPv4 addresses management
 *
 * @author  Olegs Capligins
 * @licence GPLv2
 */
class IPv4
{

    /**
     * Constructor checks if GMP extension is present
     * @throws Exception
     */
    public function __construct()
    {
        if( !extension_loaded('gmp') ) {
            throw new Exception("GMP extension must be installed and loaded");
        }
    }

    /**
     * Child initialization
     * @param $ip string
     * @return Address
     */
    public function address( $ip )
    {
        return new Address( $ip );
    }

    /**
     * Child initialization
     * @param $subnet string
     * @return Mask
     */
    public function mask( $subnet )
    {
        return new Mask( $subnet );
    }

}