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
use sFire\Image\Color\ColorCollection;

final class ColorCollectionTest extends TestCase {


    /**
     * Testing retrieving color
     * @return void
     */
    public function testGet(): void {

        $this -> assertNull(ColorCollection :: getInstance() -> get('key'));
        ColorCollection :: getInstance() -> add('key', 'value');
        $this -> assertEquals(ColorCollection :: getInstance() -> get('key'), 'value');
    }


    /**
     * Testing retrieving all colors
     * @return void
     */
    public function testAll(): void {

        ColorCollection :: getInstance() -> add('key', 'value');
        $this -> assertTrue(count(ColorCollection :: getInstance() -> all()) > 0);
    }


    /**
     * Testing setting new color
     * @return void
     */
    public function testSet(): void {

        ColorCollection :: getInstance() -> set('key', 'value');
        $this -> assertEquals('value', ColorCollection :: getInstance() -> get('key') );
    }


    /**
     * Testing pulling color
     * @return void
     */
    public function testPull(): void {

        ColorCollection :: getInstance() -> add('key', 'value');
        $this -> assertEquals(ColorCollection :: getInstance() -> pull('key'), 'value');
        $this -> assertNull(ColorCollection :: getInstance() -> get('key'));
    }


    /**
     * Testing removing color
     * @return void
     */
    public function testRemove(): void {

        $this -> assertEquals(['s' => 0, 't' => 'Black'], ColorCollection :: getInstance() -> get(['colors', '000000']) );
        ColorCollection :: getInstance() -> remove(['colors', '000000']);
        $this -> assertNull(ColorCollection :: getInstance() -> get(['colors', '000000']));
    }

    /**
     * Testing existing of colors
     * @return void
     */
    public function testExists(): void {

        ColorCollection :: getInstance() -> add('key', 'value');
        $this -> assertTrue(ColorCollection :: getInstance() -> has('key'));
    }

    /**
     * Testing flushing all colors
     * @return void
     */
    public function testFlush(): void {

        ColorCollection :: getInstance() -> flush();
        $this -> assertCount(0, ColorCollection :: getInstance() -> getAll());
    }
}