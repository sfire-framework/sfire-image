<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Image\Color\Color;

final class ColorTest extends TestCase {


    /**
     * Testing converting hexadecimal colors to HSL colors
     * @return void
     */
    public function testHsl(): void {

        $this -> assertEquals((object) ['h' => 255, 's' => 255, 'l' => 255], Color :: hexToHsl('#ffffff'));
        $this -> assertEquals((object) ['h' => 255, 's' => 255, 'l' => 255], Color :: hexToHsl('ffffff'));

        $this -> expectException(ErrorException :: class);
        Color :: hexToHsl('testing');
    }


    /**
     * Testing converting hexadecimal colors to RGB colors
     * @return void
     */
    public function testRGB(): void {

        $this -> assertEquals((object) ['r' => 255, 'g' => 255, 'b' => 255], Color :: hexToRgb('#ffffff'));
        $this -> assertEquals((object) ['r' => 255, 'g' => 255, 'b' => 255], Color :: hexToRgb('ffffff'));

        $this -> expectException(ErrorException :: class);
        Color :: hexToRgb('testing');
    }
}