<?php

/**
* randchar() -  generate arbitrary length string of random characters
*               or random, decimal/hexadecimal rgb/gray color values
* Copyright (C) 2003  Erich Spencer <ewspencer@industrex.com>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
* for more details.
*
*
* Description:
*
* mixed randchar ( [int length, string range, string case] )
*
* randchar() returns a string or number with $length number of
* characters or digits, from a $range set of possible character
* values, in $case character case.
*
* If arguments are invalid or missing, $length defaults to 8,
* $range defaults to alphanumeric, and $case defaults to mixed.
*
* Four special instances of $range are provided to produce decimal
* or hexadecimal rgb, or grayscale color values for $length bit
* color depth. Currently, randchar() supports 24 bit color depth
* maximum, so $length defaults to 8 if greater than 24.
*
* Possible values for $range are
*
*      abc = alphabetic
*      anc = alphanumeric
*      hxd = hexadecimal
*      nmc = numeric
*
*      dmc = color: decimal
*      dmg = gray : decimal
*      hxc = color: hexadecimal
*      hxg = gray : hexadecimal
*
* Possible values for $case are
*
*        l = lower
*        u = upper
*        m = mixed
*
* -------------------------------------------------------------
*
* CREATED 2003/04/30
* REVISED 2003/05/28
* VERSION 0.5.20030528
*
*/

function randchar($length = 8, $range = 'anc', $case = 'm')
{
    $str = null;
    if (gettype($length) != 'integer')
            $length = 8;
    switch ($range) {
    case 'abc': // alphabetic
        $minval = 2; $maxval = 3;
        break;
    case 'dmc': // color: decimal
    case 'hxc': // color: hexadecimal
        $minval = 5; $maxval = 5;
        if ($length > 24) $length = 24;
        $depth  = $length;
        $length = 6;
        break;
    case 'dmg': // gray: decimal
    case 'hxg': // gray: hexadecimal
        $minval = 5; $maxval = 5;
        if ($length > 24) $length = 24;
        $depth  = $length;
        $length = 2;
        break;
    case 'hxd': // hexadecimal
        $minval = 5; $maxval = 5;
        break;
    case 'nmc': // numeric
        $minval = 1; $maxval = 1;
        break;
    case 'anc': // alphanumeric
    default :   // alphanumeric
        $minval = 1; $maxval = 4;
        break;
    }
    for ($i = 0;$i < $length;$i++) {
        switch (@rand($minval, $maxval)) {
        case 1: $str .= chr(rand(48, 57));  // 0-9
            break;
        case 2: $str .= chr(rand(97, 122)); // a-z
            break;
        case 3: $str .= chr(rand(65, 90));  // A-Z
            break;
        case 4: $str .= chr(rand(48, 57));  // 0-9
            break;
        case 5: $str .= dechex(rand(0,15)); // 0-16
                break;
        }
    }
    switch ($range) { // procedure for color values
    case 'dmc':
    case 'dmg':
    case 'hxc':
    case 'hxg':
        $clrs  = chunk_split($str,2,' ');      // space delimit color value pairs
        $clrs  = explode(' ',trim($clrs));     // load color value pairs into array
        $bpclr = floor($depth/3);              // set number of bits per color value
        $step  = (256/pow(2,$bpclr));          // calculate step value for quantizing
        foreach ($clrs as $key => $clr) {
            $clrs[$key] = hexdec($clr);        // convert to decimal for manipulation
            $clr = round($clrs[$key] / $step); // calculate quantizing (Q) factor
            $clr = $clr * $step;               // multiply color value by Q factor
            $clr = $clr - floor($clr/256);     // adjust to maintain bounds 0-256
            $clrs[$key] = $clr;                // replace original color value pair
        }
        switch ($range) { // triple gray value
        case 'dmg':
        case 'hxg':
            $clrs = array_pad($clrs,3,$clrs[0]);
            break;
        }
        switch ($range) {
        case 'dmc': // rrr,ggg,bbb
        case 'dmg': // rrr,ggg,bbb
            $str =  (
                         $clrs[0].","
                        .$clrs[1].","
                        .$clrs[2]
                        );
            break;
        case 'hxc': // rrggbb
        case 'hxg': // rrggbb
            $str =  (
                         sprintf("%02s",dechex($clrs[0]))
                        .sprintf("%02s",dechex($clrs[1]))
                        .sprintf("%02s",dechex($clrs[2]))
                        );
            break;
        }
    break;
    }
    switch ($case) {
    case 'l': // lower case
        $str = strtolower($str);
        break;
    case 'u': // upper case
        $str = strtoupper($str);
        break;
    }
    return $str;
}

?>
