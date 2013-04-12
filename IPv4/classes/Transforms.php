<?php

/**
 * Library for IPv4 data transformation.
 * All arguments are GMP resources.
 *
 * @link    https://github.com/Haran/PHP.IPv4
 * @author  Olegs Capligins
 * @licence GPLv3
 */
class IPTransforms
{

    /**
     * Converts IP in GMP resource to human-readable format AAA.BBB.CCC.DDD
     *
     * @param $res resource
     * @return string
     */
    public function HumanReadable( $res )
    {
        return long2ip( gmp_strval($res, 10) );
    }

    /**
     * Converts IP in GMP resource to hexademical format 0xAABBCCDD
     *
     * @param $res resource
     * @return string
     */
    public function Hex( $res )
    {
        return '0x'.strtoupper( str_pad(gmp_strval($res, 16), 8, '0', STR_PAD_LEFT) );
    }

    /**
     * Converts IP in GMP resource to hexademical dotted format 0xAA.0xBB.0xCC.0xDD
     *
     * @param $res resource
     * @return string
     */
    public function HexDotted( $res )
    {

        $ip = explode( '.', $this->HumanReadable($res) );

        for( $i=0; $i<4; $i++ ) {
            $ip[$i] = '0x' . strtoupper( sprintf("%02x", $ip[$i]) );
        }

        return join('.', $ip);

    }

    /**
     * Converts IP in GMP resource to octal format aaaa.bbbb.cccc.dddd
     *
     * @param $res resource
     * @return string
     */
    public function Oct( $res )
    {

        $ip = explode( '.', $this->HumanReadable($res) );

        for( $i=0; $i<4; $i++ ) {
            $ip[$i] = sprintf("%04o", $ip[$i]);
        }

        return join('.', $ip);

    }

    /**
     * Converts IP in GMP resource to ip2long format.
     * Result is unsigned. We say 'no' to negative integers after ip2long().
     *
     * @param $res resource
     * @return string
     */
    public function Long( $res )
    {
        return gmp_strval($res, 10);
    }

    /**
     * Converts IP in GMP resource to binary format
     *
     * @param $res resource
     * @return string
     */
    public function Bin( $res )
    {
        return str_pad( gmp_strval($res, 2), 32, '0', STR_PAD_LEFT);
    }

    /**
     * Converts subnet address in GMP resource to CIDR format.
     * Performs additional check if argument is a valid subnet mask.
     * Be sure to add slash to the returned value if necessary.
     *
     * @param $res resource
     * @return int
     */
    public function Cidr( $res )
    {

        $mask = gmp_strval($res, 2);
        if( !preg_match('/(?=^[0-1]{32}$)^1+(0?)+$/i', $mask) ) {
            return null;
        }

        // All methods return strings
        return strval( gmp_popcount($res) );

    }

}
