<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Image\Adapter;

use sFire\Image\Exception\InvalidArgumentException;
use sFire\Image\Exception\RuntimeException;
use sFire\Image\ImageAbstract;
use sFire\Image\Color\Color;


/**
 * Class Gd
 * @package sFire\Image
 */
class Gd extends ImageAbstract {


    /**
     * Flips the image horizontally
     * @var int
     */
    public const FLIP_HORIZONTAL = 1;


    /**
     * Flips the image vertically
     * @var int
     */
    public const FLIP_VERTICAL = 2;


    /**
     * Flips the image both horizontally and vertically
     * @var int
     */
    public const FLIP_BOTH = 3;


    /**
     * Contains the image as resource
     * @var resource
     */
    private $image;


    /**
     * Contains the image as file path
     * @var string
     */
    private string $file;


    /**
     * Contains the image extension
     * @var string
     */
    private string $extension;


    /**
     * Sets a new image from file
     * @param string $file The path to the image
     * @return self
     * @throws RuntimeException
     */
    public function setImage(string $file): self {

        if(false === is_file($file)) {
            throw new RuntimeException(sprintf('File "%s" passed to %s() does not exists', $file, __METHOD__));
        }

        if(false === is_readable($file)) {
            throw new RuntimeException(sprintf('File "%s" passed to %s() is not readable', $file, __METHOD__));
        }

        $info = @getimagesize($file);

        if(false === is_array($info) || count($info) < 3) {
            throw new RuntimeException(sprintf('File "%s" passed to %s() is not a valid image', $file, __METHOD__));
        }

        $this -> image 	 	= @imagecreatefromstring(file_get_contents($file));
        $this -> file	 	= $file;
        $this -> extension 	= pathinfo($file, PATHINFO_EXTENSION);

        return $this;
    }


    /**
     * Returns an array with all the hexadecimal colors used in an image
     * @param int $limit [optional] Limit the amount of results in the returned array
     * @param bool $round [optional] Round the hexadecimal colors
     * @return array
     */
    public function getHexColors(int $limit = null, bool $round = true): array {

        $hex 	= [];
        $width 	= imagesx($this -> image);
        $height = imagesy($this -> image);

        for($y = 0; $y < $height; $y++) {

            for($x = 0; $x < $width; $x++) {

                $index = imagecolorat($this -> image, $x, $y);
                $color = imagecolorsforindex($this -> image, $index);

                if(true === $round) {

                    foreach(['red', 'green', 'blue'] as $type) {

                        $color[$type] = intval((($color[$type]) + 15) / 32) * 32;

                        if($color[$type] >= 256){
                            $color[$type] = 240;
                        }
                    }
                }

                $hex[] = substr('0' . dechex($color['red']), -2) . substr('0' . dechex($color['green']), -2) . substr('0' . dechex($color['blue']), -2);
            }
        }

        $hex = array_count_values($hex);

        natsort($hex);

        $hex = $limit ? array_slice(array_reverse($hex, true), 0, $limit, true) : array_reverse($hex, true);

        return $hex;
    }


    /**
     * Returns the most used colors in an image with extra information about the used colors. Returns the hexadecimal value, shade and base color.
     * @param int $limit [optional] Limit the amount of results in the returned array
     * @return array
     */
    public function getBaseColors(int $limit = 10): array {

        $hexs 	= $this -> getHexColors($limit);
        $output = [];
        $index  = 0;

        foreach($hexs as $hex => $amount) {

            $color = Color :: name((string) $hex);

            if(null !== $color) {
                $output[] = $color;
            }

            $index++;
        }

        return $output;
    }


    /**
     * Returns a percentage (integer) of how much an image is considered black and white between 0 and 100
     * @return int
     */
    public function blackWhite(): int {

        $r 	 	= [];
        $g 	 	= [];
        $b 	 	= [];
        $c 	 	= 0;
        $width  = imagesx($this -> image);
        $height = imagesy($this -> image);

        for($x = 0; $x < $width; $x++) {

            for($y = 0; $y < $height; $y++) {

                $rgb = imagecolorat($this -> image, $x, $y);

                $r[$x][$y] = ($rgb >> 16) & 0xFF;
                $g[$x][$y] = ($rgb >> 8) & 0xFF;
                $b[$x][$y] = $rgb & 0xFF;

                if($r[$x][$y] == $g[$x][$y] && $r[$x][$y] == $b[$x][$y]) {
                    $c++;
                }
            }
        }

        return (int) round($c / ($width * $height) * 100, 0);
    }


    /**
     * Applies a negative filter on the current image
     * @return self
     */
    public function negate(): self {

        imagefilter($this -> image, IMG_FILTER_NEGATE);
        return $this;
    }


    /**
     * Applies a higher or lower contrast on the current image
     * @param int $level [optional] The higher the number, the darker the image
     * @return self
     */
    public function contrast($level = 50): self {

        imagefilter($this -> image, IMG_FILTER_CONTRAST, $level);
        return $this;
    }


    /**
     * Applies a higher or lower brightness on the current image
     * @param int $level [optional] The higher the number, the higher the brightness of the image
     * @return self
     */
    public function brightness($level = 50): self {

        imagefilter($this -> image, IMG_FILTER_BRIGHTNESS, $level);
        return $this;
    }


    /**
     * Applies a grayscale filter on the current image
     * @return self
     */
    public function grayscale(): self {

        imagefilter($this -> image, IMG_FILTER_GRAYSCALE);
        return $this;
    }


    /**
     * Applies a edge detect filter on the current image
     * @return self
     */
    public function edgeDetect(): self {

        imagefilter($this -> image, IMG_FILTER_EDGEDETECT);
        return $this;
    }


    /**
     * Applies a emboss filter on the current image
     * @return self
     */
    public function emboss(): self {

        imagefilter($this -> image, IMG_FILTER_EMBOSS);
        return $this;
    }


    /**
     * Applies a gaussian blur filter on the current image
     * @return self
     */
    public function gaussianBlur(): self {

        imagefilter($this -> image, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    }


    /**
     * Applies a selective blur filter on the current image
     * @return self
     */
    public function selectiveBlur(): self {

        imagefilter($this -> image, IMG_FILTER_SELECTIVE_BLUR);
        return $this;
    }


    /**
     * Applies a mean removal filter on the current filter
     * @return self
     */
    public function meanRemoval(): self {

        imagefilter($this -> image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }


    /**
     * Applies a colorize filter on the current image
     * @param int $r Number representing the color red between 0 and 255
     * @param int $g Number representing the color green between 0 and 255
     * @param int $b Number representing the color blue between 0 and 255
     * @param int $alpha Alpha channel between 0 and 255
     * @return self
     * @throws InvalidArgumentException
     */
    public function colorize(int $r, int $g, int $b, int $alpha): self {

        if($r < 0 || $r > 255) {
            throw new InvalidArgumentException(sprintf('Argument 1 given to "%s" must be an integer between 0 and 255, "%s" given', __METHOD__, $r));
        }

        if($g < 0 || $g > 255) {
            throw new InvalidArgumentException(sprintf('Argument 2 given to "%s" must be an integer between 0 and 255, "%s" given', __METHOD__, $g));
        }

        if($b < 0 || $b > 255) {
            throw new InvalidArgumentException(sprintf('Argument 3 given to "%s" must be an integer between 0 and 255, "%s" given', __METHOD__, $b));
        }

        if($alpha < 0 || $alpha > 255) {
            throw new InvalidArgumentException(sprintf('Argument 4 given to "%s" must be an integer between 0 and 255, "%s" given', __METHOD__, $alpha));
        }

        imagefilter($this -> image, IMG_FILTER_COLORIZE, $r, $g, $b, $alpha);
        return $this;
    }


    /**
     * Applies a smooth filter on the current image
     * @param int $level The higher the number, the smoother the image
     * @return self
     */
    public function smooth(int $level = 50): self {

        imagefilter($this -> image, IMG_FILTER_SMOOTH, $level);
        return $this;
    }


    /**
     * Applies a pixelate filter on the current image
     * @param int $blockSize The block size
     * @param int $effect The pixelation effect mode
     * @return self
     */
    public function pixelate(int $blockSize = 5, int $effect = 50): self {

        imagefilter($this -> image, IMG_FILTER_PIXELATE, $blockSize, $effect);
        return $this;
    }


    /**
     * Applies scatter effect on the current image
     * @param int $subtractionEffect [optional] Effect subtraction level. This must not be higher or equal to the addition level set with $additionEffect
     * @param int $additionEffect [optional] Effect addition level
     * @param array|null $colors [optional] array indexed color values to apply effect at
     * @return self
     */
    public function scatter(int $subtractionEffect = 5, int $additionEffect = 3, ?array $colors = []): self {

        imagefilter($this -> image, IMG_FILTER_SCATTER, $subtractionEffect, $additionEffect, $colors);
        return $this;
    }


    /**
     * Crops the current image
     * @param int $x Horizontal start point
     * @param int $y Vertical start point
     * @param int $width The width of the new image
     * @param int $height The height of the new image
     * @param bool $interlace [optional] Enable or disable interlace
     * @return self
     */
    public function crop(int $x, int $y, int $width, int $height, bool $interlace = false): self {

        $this -> createImage($x, $y, $width, $height, $width, $height, $interlace);
        return $this;
    }


    /**
     * Resizing the current image
     * @param int $width The width of the new image
     * @param int $height The height of the new image
     * @param bool $ratio Enable or disable maintaining aspect ratio
     * @param bool $interlace [optional] Enable or disable interlace
     * @return self
     */
    public function resize(int $width, int $height, bool $ratio = false, bool $interlace = false): self {

        $image = ['width' => imagesx($this -> image), 'height' => imagesy($this -> image)];

        //Set width and height
        if($height == 0 && $width > 0) {
            $height = round($image['height'] / ($image['width'] / $width), 0);
        }
        elseif($width == 0 && $height > 0) {
            $width = round($image['width'] / ($image['height'] / $height), 0);
        }

        $x = 0;
        $y = 0;

        //Ratio
        if(true === $ratio) {

            $ratio 		= [$image['width'] / $image['height'], $width / $height];
            $tmp_width 	= $image['width'];
            $tmp_height = $image['height'];

            if($ratio[0] > $ratio[1]) {

                $image['width'] = $image['height'] * $ratio[1];
                $x = ($tmp_width - $image['width']) / 2;
            }
            elseif($ratio[0] < $ratio[1]) {

                $image['height'] = $image['width'] / $ratio[1];
                $y = ($tmp_height - $image['height']) / 2;
            }
        }

        //Resizing
        $this -> createImage($x, $y, $image['width'], $image['height'], $width, $height, $interlace);
        return $this;
    }


    /**
     * Rotates the current image with a given angle
     * @param int $degrees Rotation angle, in degrees. The rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
     * @param string $bgColor Hexadecimal color specifies the color of the uncovered zone after the rotation
     * @param int $alpha [optional] Specifies the alpha channel
     * @return self
     */
    public function rotate(int $degrees, string $bgColor, int $alpha = 0): self {

        $color = Color :: hexToRgb($bgColor);

        imagesavealpha($this -> image , true);
        $color = imagecolorallocatealpha($this -> image , $color -> r, $color -> g, $color -> b, $alpha);
        $this -> image = imagerotate($this -> image, $degrees, $color);

        return $this;
    }


    /**
     * Flips the current image horizontal, vertical or both
     * @param int $mode [optional] The type of flipping (horizontal, vertical or both)
     * @return self
     * @throws InvalidArgumentException
     */
    public function flip(int $mode = self::FLIP_HORIZONTAL): self {

        if(false === in_array($mode, [self::FLIP_HORIZONTAL, self::FLIP_VERTICAL, self::FLIP_BOTH])) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be between 1 and 3, "%s" given', __METHOD__, $mode));
        }

        imageflip($this -> image, $mode);
        return $this;
    }


    /**
     * Outputs image or saves image to optional new file location
     * @param string $file [optional] file path for saving a new image
     * @param int $quality [optional] The quality of the image. The higher the number, the better the quality
     * @return bool
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function save(string $file = null, int $quality = 90): bool {

        if(null !== $file && false === is_writable(dirname($file))) {
            throw new RuntimeException(sprintf('File in "%s" directory is not writable', dirname($file)));
        }

        if($quality < 0 || $quality > 100) {
            throw new InvalidArgumentException(sprintf('Argument 2 passed to %s() must be between 0 and 100, "%s" given', __METHOD__, $quality));
        }

        $extension = (null !== $file) ? pathinfo($file, PATHINFO_EXTENSION) : $this -> extension;
        $quality   = $this -> getQuality($quality, $extension);

        switch(strtolower($extension)) {

            case 'bmp'	: $result = imagebmp($this -> image, $file); break;
            case 'png'	: $result = imagepng($this -> image, $file, $quality); break;
            case 'gif'	: $result = imagegif($this -> image, $file); break;
            case 'webp' : $result = imagewebp($this -> image, $file, $quality); break;
            default 	: $result = imagejpeg($this -> image, $file, $quality); break;
        }

        $this -> image = @imagecreatefromstring(file_get_contents($this -> file));

        return $result;
    }


    /**
     * Creates new image resource. Returns true on success, false if failed.
     * @param int $x Horizontal start point
     * @param int $y Vertical start point
     * @param int $width The current width of the image
     * @param int $height The current height of the image
     * @param int $new_width The new width of the image
     * @param int $new_height The new height of the image
     * @param bool $interlace [optional] Enable or disable interlace
     * @return bool
     * @throws RuntimeException
     */
    private function createImage(int $x, int $y, int $width, int $height, int $new_width, int $new_height, bool $interlace = false): bool {

        $resource = imagecreatetruecolor($new_width, $new_height);

        if(false === imagesavealpha($resource, true)) {
            throw new RuntimeException('Could not set the flag to save full alpha channel');
        }

        if(false === imagealphablending($resource, false)) {
            throw new RuntimeException('Could not set the blending mode');
        }

        if(false === imagefill($resource, 0, 0, imagecolorallocate($resource, 255, 255, 255))) {
            throw new RuntimeException('Could not flood fill the image');
        }

        if(false === imagecopyresampled($resource, $this -> image, 0, 0, $x, $y, $new_width, $new_height, $width, $height)) {
            throw new RuntimeException('\'Could not copy and resize part of an image with resampling');
        }

        if((true === $interlace && imageinterlace($this -> image, true)) || false === $interlace) {

            $this -> image = $resource;
            return true;
        }

        return false;
    }
}