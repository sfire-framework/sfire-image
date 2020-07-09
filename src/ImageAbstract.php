<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Image;


/**
 * Class ImageAbstract
 * @package sFire\Image
 */
abstract class ImageAbstract {


    /**
     * Constructor
     * @param string $file The path to the image
     */
    public function __construct(string $file = null) {

        if(null !== $file) {
            $this -> setImage($file);
        }
    }


    /**
     * Sets a new image from a path
     * @param string $file The path to the image
     * @return self
     */
    abstract public function setImage(string $file): self;


    /**
     * Executes all commands and saves the image to an optional new file location
     * @param string $file [optional] file path for saving the new image
     * @param int $quality [optional] The quality of the image. The higher the number, the better the quality
     * @return bool
     */
    abstract public function save(string $file = null, int $quality = 90): bool;


    /**
     * Validates and returns quality based on image extension
     * @param int $quality The quality of the image. The higher the number, the better the quality
     * @param string $extension The image extension without leading dot
     * @return int
     */
    protected function getQuality(int $quality, string $extension): int {

        if('png' === strtolower($extension)) {
            $quality = min(9, floor((100 - $quality) / 10));
        }

        return $quality;
    }
}