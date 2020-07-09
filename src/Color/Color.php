<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Image\Color;

use sFire\Image\Exception\InvalidArgumentException;
use stdClass;


/**
 * Class Color
 * @package sFire\Image
 */
class Color {


    /**
     * Contains a list of colors
     * @var array
     */
    private static array $list = [];


    /**
     * Converts a hexadecimal color to color name in human language
     * @param string $color A hexadecimal color
     * @return null|stdClass
     */
    public static function name(string $color): ?stdClass {

        $color  = strtoupper(ltrim($color, '#'));
        $rgb 	= static :: hexToRgb($color);
        $r 		= $rgb -> r;
        $g 		= $rgb -> g;
        $b 		= $rgb -> b;
        $hsl 	= static :: hexToHsl($color);
        $h 		= $hsl -> h;
        $s 		= $hsl -> s;
        $l 		= $hsl -> l;
        $cl 	= -1;
        $df 	= -1;

        //Index all the colors
        if(null === static::$list) {
            static :: index();
        }

        foreach(static::$list as $hex => $color) {

            $ndf1 = pow($r - $color -> r, 2) + pow($g - $color -> g, 2) + pow($b - $color -> b, 2);
            $ndf2 = pow($h - $color -> h, 2) + pow($s - $color -> s, 2) + pow($l - $color -> l, 2);
            $ndf  = $ndf1 + $ndf2 * 2;

            if($df < 0 || $df > $ndf) {

                $df = $ndf;
                $cl = $hex;
            }
        }

        if(true === isset(static::$list[$cl])) {
            return static::$list[$cl];
        }

        return null;
    }


    /**
     * Converts hexadecimal color to RGB (red, green, blue)
     * @param string $color A hexadecimal color with or without leading pound (#)
     * @return stdClass
     * @throws InvalidArgumentException
     */
    public static function hexToRgb(string $color): stdClass {

        $color = strtoupper(ltrim($color, '#'));

        if(1 !== preg_match('#^[0-9a-fA-F]{6}$#', $color)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a 6 character hexadecimal string, "%s" given', __METHOD__, $color), E_USER_ERROR);
        }

        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");

        return (object) [

            'r' => $r,
            'g' => $g,
            'b' => $b
        ];
    }


    /**
     * Converts RGB to HSL
     * @param int $r Number between 0 and 255 which represents the red color
     * @param int $g Number between 0 and 255 which represents the green color
     * @param int $b Number between 0 and 255 which represents the blue color
     * @return stdClass
     * @throws InvalidArgumentException
     */
    public static function rgbToHsl(int $r, int $g, int $b): stdClass {

        if(false === static :: validateRgb($r, $g, $b)) {
            throw new InvalidArgumentException(sprintf('Red, green and blue values should be between 0 and 255, "%s, %s and %s given"', $r, $g, $b));
        }

        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0;
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if($d == 0) {
            $h = $s = 0;
        }
        else {

            $s = $d / (1 - abs( 2 * $l - 1 ));

            switch($max) {

                case $r:

                    $h = 60 * fmod(( ( $g - $b ) / $d ), 6);

                    if($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:

                    $h = 60 * (( $b - $r ) / $d + 2);
                    break;

                case $b:

                    $h = 60 * (( $r - $g ) / $d + 4);
                    break;
            }
        }

        return (object) [

            'h' => round($h, 2),
            's' => round($s * 100, 2),
            'l' => round($l * 100, 2)
        ];
    }


    /**
     * Converts hexadecimal color to HSL
     * @param string $color A hexadecimal color with or without leading pound (#)
     * @return stdClass
     */
    public static function hexToHsl($color): stdClass {

        $rgb = static :: hexToRgb($color);
        return static :: rgbToHsl($rgb -> r, $rgb -> g, $rgb -> b);
    }


    /**
     * Validates a hexadecimal string and returns true if given content is a valid hexadecimal color, false otherwise
     * @param string $content The content that needs to be validated
     * @return bool
     */
    public static function validateHex(string $content): bool {
        return (bool) preg_match('/^#?([A-Fa-f0-9]{6})$/', $content);
    }


    /**
     * Returns true if given values are valid RGB values, false otherwise
     * @param int $r Number between 0 and 255 which represents the red color
     * @param int $g Number between 0 and 255 which represents the green color
     * @param int $b Number between 0 and 255 which represents the blue color
     * @return bool
     */
    public static function validateRgb(int $r, int $g, int $b): bool {
        return max($r, $g, $b) <= 255 && min($r, $g, $b) >= 0;
    }


    /**
     * Initialise all colors
     * @return void
     */
    private static function index(): void {

        $colors = ColorCollection :: getInstance() -> get('colors');

        foreach($colors as $hex => $color) {

            $rgb = static :: hexToRgb($hex);
            $hsl = static :: hexToHsl($hex);

            $shade = ColorCollection :: getInstance() -> get(['shades', $color['s']]);
            $base  = ColorCollection :: getInstance() -> get(['base', $shade['b']]);

            static::$list[$hex] = (object) [

                'r' => $rgb -> r,
                'g' => $rgb -> g,
                'b' => $rgb -> b,
                'h' => $hsl -> h,
                's' => $hsl -> s,
                'l' => $hsl -> l,
                'hex' => $hex,
                'title' => $color['t'],
                'shade' => (object) [

                    'id' => $color['s'],
                    'hex' => $shade['h']
                ],
                'base' => (object) [

                    'id' => $shade['b'],
                    'hex' => $base['h'],
                    'title' => $base['t']
                ]
            ];
        }
    }
}