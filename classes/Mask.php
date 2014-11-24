<?php

namespace dautkom\ipv4\classes;

use ReflectionMethod;
use dautkom\ipv4\iSubnet;

class Mask extends Address implements iSubnet
{

    /**
     * Constructor.
     * Registers subnet mask static property.
     *
     * @param string $subnet
     */
    public function __construct( $subnet )
    {

        // Adding new pattern
        $this->formats = array_merge($this->formats, ['Cidr' => '%^(/[1-9]$|/[1-2][0-9]|/3[0-2])$%i']);

        // Properties
        self::$subnet          = $subnet;
        self::$subnetResource  = $this->unifyAddress();

        if( !is_null(self::$subnetResource) ) {
            self::$subnetResource = gmp_init( self::$subnetResource );
        }

    }

    /**
     * Checks if subnet mask is valid
     *
     * @param bool $strict
     * @return bool
     */
    public function isValid( $strict = false )
    {

        // Check if address was set and if it is valid
        if( isset(self::$ip) && is_null(self::$ipResource) ) {
            return false;
        }

        // Strict check for subnet.
        // In this case address must be a valid subnet address, otherwise 'false' will be returned
        elseif( !is_null(self::$ipResource) && $strict==true && !is_null(self::$subnetResource)  ) {
            $subnet = long2ip(gmp_strval(self::$ipResource));
            $mask   = gmp_popcount(self::$subnetResource);
            $range  = long2ip((ip2long($subnet)) & ((-1 << (32 - (int)$mask))));
            if ($range!=$subnet) return false;
        }

        return ( is_null(self::$subnetResource) ) ? false : true;

    }

    /**
     * Returns subnet mask format.
     * Supported formats:
     * - HumanReadable
     * - Hex
     * - Oct
     * - Long
     * - Bin
     * - Cidr
     *
     * @return string
     */
    public function getFormat()
    {

        foreach( $this->formats as $format=>$pattern ) {

            // For regular expressions
            if( !is_array($pattern) ) {
                if ( preg_match( $pattern, self::$subnet ) ) {
                    return $format;
                }
            }

            // Check for Long and CIDR formats
            elseif( count($pattern) == 2 && array_key_exists(0, $pattern) && array_key_exists(1, $pattern) ) {
                if( is_numeric(self::$subnet) && self::$subnet > $pattern[0] && self::$subnet < $pattern[1] && preg_match('/^\-?[0-9]+$/', self::$subnet) ) {
                    return $format;
                }
            }

        }

        return null;

    }

    /**
     * Retrieves IP-address in human-readable format (aaa.bbb.ccc.ddd)
     *
     * @return string
     */
    public function getHumanReadable()
    {
        $to = new Transforms;
        return ( $this->isValid() ) ? $to->HumanReadable(self::$subnetResource) : null;
    }

    /**
     * Converts subnet mask to a certain format.
     * Supported formats:
     * - HumanReadable
     * - Hex
     * - HexDotted
     * - Oct
     * - Long
     * - Bin
     * - Cidr
     *
     * @param $format string
     * @return string
     */
    public function convertTo( $format )
    {

        if( $this->isValid() && method_exists( "IPTransforms", $format ) ) {
            $reflection = new ReflectionMethod('IPTransforms', $format);
            return $reflection->invoke( new Transforms, self::$subnetResource );
        }

        return null;

    }

    /**
     * Calculates subnet address based on IP and subnet mask.
     *
     * @return string
     */
    public function getAddress()
    {

        if( parent::isValid() && self::isValid() ) {
            return long2ip( gmp_strval( gmp_and(self::$ipResource, self::$subnetResource) ) );
        }

        return null;

    }

    /**
     * Get avaliable range of IP-addresses in subnet
     *
     * @return array
     */
    public function getRange()
    {

        if ( !parent::isValid() || !self::isValid() ) {
            return null;
        }

        $subnet   = parent::getHumanReadable();
        $mask     = self::convertTo('Cidr');
        $range[0] = long2ip((ip2long($subnet)) & ((-1 << (32 - (int)$mask))));
        $range[1] = long2ip((ip2long($subnet)) + pow(2, (32 - (int)$mask)) - 1);

        return ($range[0]!=$subnet) ? null : $range;

    }

    /**
     * Returns amount of avaliable IP-addresses in subnet without broadcast address.
     * 0 is returned if there's no avaliable addresses (e.g. mask 255.255.255.254)
     *
     * @param bool $exclude
     * @return int
     */
    public function countHosts( $exclude=false )
    {

        if ( !$this->isValid(true) ) {
            return null;
        }

        return abs(gmp_strval( gmp_xor(self::$subnetResource, gmp_init('0xffffffff', 16)) )-1);

    }

    /**
     * Returns broadcast address.
     * In ->ip($arg) there could be passed regular ip-address or subnet address
     *
     * @return string
     */
    public function getBroadcast()
    {

        if ( !$this->isValid() || !parent::isValid() ) {
            return null;
        }

        $mask  = ip2long( self::getHumanReadable() );
        $bcast = (ip2long( parent::getHumanReadable() ) & $mask) | (~$mask);

        return long2ip($bcast);

    }

    /**
     * Checks if an IP argument is a broadcast address
     *
     * @return bool
     */
    public function isBroadcast()
    {

        $bcast = $this->getBroadcast();

        if( is_null($bcast) ) {
            return null;
        }

        return ( $bcast == parent::getHumanReadable() ) ? true : false;

    }

    /**
     * Checks if an IP argument is a subnet address
     *
     * @return bool
     */
    public function isSubnet()
    {
        $subnet = $this->getAddress();

        if( is_null($subnet) ) {
            return null;
        }

        return ( $subnet == parent::getHumanReadable() ) ? true : false;
    }

    /**
     * Checks if $ip belongs to a specified subnet.
     *
     * @param $ip
     * @return bool
     */
    public function has( $ip )
    {

        if ( !$this->isValid() || !parent::isValid() || is_null(parent::getFormat($ip)) ) {
            return null;
        }

        $subnet = ip2long($this->getAddress());
        $mask   = self::convertTo('Cidr');
        $ip     = ip2long($ip);

        return ($subnet <= $ip) && ($ip <= ($subnet + pow(2, (32 - (int)$mask)) - 1));

    }

    /**
     * Transforms subnet mask into GMP decimal resource.
     * Source subnet address could be declarated in several formats.
     *
     * @return string
     */
    private function unifyAddress()
    {

        $format = null;
        $result = null;
        $format = $this->getFormat();

        if( !is_null($format) ) {

            if ( is_numeric(self::$subnet) ) {

                $result = sprintf("%u", floatval(self::$subnet));

                if( $result === "0" ) {

                    if( $format=='Bin' ) {
                        $result = gmp_init( self::$subnet, 2 );
                        $result = gmp_strval( $result, 10 );
                    }

                    else {
                        $result = gmp_init( self::$subnet );
                        $result = gmp_strval( $result, 10 );
                    }

                }

            }
            else {

                if( $format=='Cidr' ) {
                    $cidr   = intval( substr(self::$subnet, 1) );
                    $result = sprintf("%u", floatval(ip2long(long2ip(-1 << (32 - $cidr)))));
                }

                else {
                    $result = sprintf("%u", floatval(ip2long(self::$subnet)));
                }

            }

        }

        return ( empty($result) || !$this->validateMask($result) ) ? null : $result;

    }

    /**
     * Internal subnet mask validator.
     * Performs binary check if subnet mask is really valid.
     *
     * @param $mask string
     * @return bool
     */
    private function validateMask( $mask )
    {

        if( !empty($mask) ) {

            $mask = gmp_strval( gmp_init($mask), 2 );

            if( preg_match('/(?=^[0-1]{32}$)^1+(0?)+$/i', $mask) ) {
                return true;
            }

        }

        return false;

    }

} 