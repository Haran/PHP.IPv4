<?php

namespace dautkom\ipv4\classes;

use dautkom\ipv4\IPv4;
use dautkom\ipv4\iAddress;

/**
 * Class Address
 * Work with IP-addresses or with subnet addresses
 *
 * @package dautkom\ipv4\classes
 */
class Address extends IPv4 implements iAddress
{

    /**
     * Raw IP-address
     * @var string
     */
    protected static $ip;

    /**
     * Subnet property
     * @var string
     */
    protected static $subnet;

    /**
     * IP-address as a GMP resource
     * @var resource
     */
    protected static $ipResource;

    /**
     * IP-address as a GMP resource
     * @var resource
     */
    protected static $subnetResource;

    /**
     * Supported IP-address formats.
     *
     * @var array
     */
    protected $formats = [

        // 172.16.128.22
        'HumanReadable' => '/^((0|1[0-9]{0,2}|2[0-9]{0,1}|2[0-4][0-9]|25[0-5]|[3-9][0-9]{0,1})\.){3}(0|1[0-9]{0,2}|2[0-9]{0,1}|2[0-4][0-9]|25[0-5]|[3-9][0-9]{0,1})$/',

        // 0xc0.0xA8.0xFF.0xFF ; 0xa412bf11
        'Hex'           => '/(?=^[0-9a-fx.]{10,19}$)^(0x(?:(?:0x)?[0-9a-f]{2}\.?){4}$)/i',

        // 0300.0250.0377.0155
        'Oct'           => '/(?=^[0-8.]{19}$)^([0-8.?]+)/',

        // 10101010111100111011010001110011
        'Bin'           => '/(?=^.*1.*$)^([0-1]{32})$/',

        // ip2long formats
        'Long'          => [-2147483649, 4294967296],

    ];

    /**
     * Constructor registers IP-address static property
     *
     * @param string $ip
     */
    public function __construct( $ip )
    {

        self::$ip         = $ip;
        self::$ipResource = $this->unifyAddress();

        if( !is_null(self::$ipResource) ) {
            self::$ipResource = gmp_init( self::$ipResource );
        }
        else {
            if(self::$show_errors) trigger_error("Invalid IP-address", E_USER_WARNING);
        }

    }

    /**
     * Checks if IP-address is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return ( is_null(self::$ipResource) ) ? false : true;
    }

    /**
     * Returns IP-address format.
     * Supported formats:
     * - HumanReadable
     * - Hex
     * - Oct
     * - Long
     * - Bin
     *
     * @param null $ip
     * @return string|null
     */
    public function getFormat( $ip = null )
    {

        if( is_null($ip) ) {
            $ip = self::$ip;
        }

        foreach( $this->formats as $format=>$pattern ) {

            // For regular expressions
            if( !is_array($pattern) ) {
                if ( preg_match( $pattern, $ip ) ) {
                    return $format;
                }
            }

            // Check for Long format
            elseif( count($pattern) == 2 && array_key_exists(0, $pattern) && array_key_exists(1, $pattern) ) {
                if( is_numeric($ip) && $ip > $pattern[0] && $ip < $pattern[1] && preg_match('/^\-?[0-9]+$/', $ip) ) {
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
        $to = new Transforms();
        return ( $this->isValid() ) ? $to->HumanReadable(self::$ipResource) : null;
    }

    /**
     * Converts IP-address to a certain format.
     * Supported formats:
     * - HumanReadable
     * - Hex
     * - HexDotted
     * - Oct
     * - Long
     * - Bin
     *
     * @param $format string
     * @return string
     */
    public function convertTo( $format )
    {

        if( $this->isValid() ) {
            $reflection = new \ReflectionMethod('dautkom\ipv4\classes\Transforms', $format);
            return $reflection->invoke( new Transforms(), self::$ipResource );
        }

        return null;

    }

    /**
     * Retrieves the largest CIDR block that an IP address will fit into
     *
     * @return string
     */
    public function getMaxBlock()
    {

        if( !$this->isValid() ) {
            return null;
        }

        $i = (-(ip2long($this->getHumanReadable()) & -(ip2long($this->getHumanReadable()))));
        return strval( gmp_popcount( gmp_init( sprintf("%u", $i) ) ) );

    }

    /**
     * Transforms IP-address into GMP decimal resource.
     * Source IP-address could be declarated in several formats.
     *
     * Original function author: ncritten
     * Idea taken from http://lv.php.net/manual/ru/function.ip2long.php#85425
     * and expanded to handle hexademical strings (original method worked only with hex floats)
     *
     * @credit ncritten
     * @return string
     */
    private function unifyAddress()
    {

        $format = null;
        $result = null;
        $format = $this->getFormat();

        if( !is_null($format) ) {

            if ( is_numeric(self::$ip) ) {

                $result = sprintf("%u", floatval(self::$ip));

                if( $result === "0" ) {

                    if( $format=='Bin' ) {
                        $result = gmp_init( self::$ip, 2 );
                        $result = gmp_strval( $result, 10 );
                    }

                    else {
                        $result = gmp_init( self::$ip );
                        $result = gmp_strval( $result, 10 );
                    }

                }

            }
            else {
                $result = sprintf("%u", floatval(ip2long(self::$ip)));
            }

        }

        return ( empty($result) ) ? null :$result;

    }

}
