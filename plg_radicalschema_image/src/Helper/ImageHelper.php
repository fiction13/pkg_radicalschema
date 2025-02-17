<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Plugin\RadicalSchema\Image\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalSchema\Administrator\Helper\ParamsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\PluginsHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\Tree\OGHelper;
use Joomla\Component\RadicalSchema\Administrator\Helper\ValueHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\LanguageHelper;
use stdClass;

/**
 * @package     pkg_radicalschema
 *
 * @since       __DEPLOY_VERSION__
 */
class ImageHelper
{
    /**
     * @var array
     *
     * @since __DEPLOY_VERSION__
     */
    protected $params = [];

    /**
     * @var \Joomla\CMS\Application\CMSApplication
     *
     * @since __DEPLOY_VERSION__
     */
    protected $app;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->params = ParamsHelper::getComponentParams();
        $this->app    = Factory::getApplication();
    }

    /**
     * Method get provider data
     *
     * @return object|void
     *
     * @since __DEPLOY_VERSION__
     */
    public function getProviderData()
    {
        // Data object
        $object        = new stdClass();
        $object->image = $this->getImage();

        return $object;
    }

    /**
     * Get image from settings or generate
     *
     * @return void|string
     *
     * @since __DEPLOY_VERSION__
     */
    public function getImage()
    {
        $fileName = $this->getCacheFile();
        $file     = $this->getCachePath() . '/' . $fileName . '.jpg';

        if (file_exists(JPATH_ROOT . '/' . $file))
        {
            return ValueHelper::prepareLink($file);
        }

        // Get title from OG build
        $build = OgHelper::getInstance()->getBuild('root');

        if (!isset($build['radicalschema.meta.og']))
        {
            return $this->showDefaultImage(false);
        }

        $title = $build['radicalschema.meta.og']->{'og:title'} ?? '';
        $image = $build['radicalschema.meta.og']->{'og:image'} ?? '';

        if (empty($title))
        {
            return $this->showDefaultImage(false);
        }

        $hash = md5($title . ':' . $fileName . ':' . $this->params->get('image_imagetype_generate_secret_key'));

        // Save data
        $this->setData($fileName, [
            'title' => $title,
            'image' => $image,
        ]);

        return ValueHelper::prepareLink('/index.php?' . http_build_query([
                'option' => 'com_ajax',
                'group'  => 'radicalschema',
                'plugin' => 'image',
                'task'   => 'generate',
                'file'   => $fileName,
                'hash'   => $hash,
                'format' => 'raw',
            ])
        );
    }

    /**
     * Generate image
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function generate()
    {
        // Check file
        $file  = $this->app->input->get('file', '', 'raw');
        $data  = $this->getData($file);
        $title = $data['title'] ?? false;
        $image = $data['image'] ?? false;
        $hash  = $this->app->input->get('hash', '', 'raw');

        // Check hash, title and file
        if ($hash !== md5($title . ':' . $file . ':' . $this->params->get('image_imagetype_generate_secret_key')) || empty($title) || empty($file))
        {
            d(334);
            $this->showDefaultImage();
        }

        $local = $this->getCachePath($file);
        $path  = JPATH_ROOT . '/' . $local;

        if (file_exists($path . '/' . $file . '.jpg'))
        {
            $this->app->redirect($local . '/' . $file . '.jpg');
        }


        // Check access on folder
        if (!is_writable($path))
        {
            $this->showDefaultImage();
        }

        // Trigger plugins for generate image
        // Trigger for `onRadicalSchemaRegisterTypes` event.
        $results = PluginsHelper::triggerPlugins(['system', 'radicalschema'], 'onRadicalSchemaGenerateImage', ['title' => $title, 'image' => $image, 'file' => &$file, 'path' => $local]);

        foreach ($results as $result)
        {
            if (!empty($result))
            {
                //redirect to image
                $this->app->redirect($local . '/' . $file . '.jpg', 302);
            }
        }

        // Generate image
        $titlePosition            = $this->params->get('image_imagetype_generate_position', 'middle');
        $backgroundType           = $this->params->get('image_imagetype_generate_background', 'fill');
        $backgroundImage          = ValueHelper::prepareLink($this->params->get('image_imagetype_generate_background_image'), true);
        $backgroundWidth          = (int) $this->params->get('image_imagetype_generate_background_width', 1200);
        $backgroundHeight         = (int) $this->params->get('image_imagetype_generate_background_height', 630);
        $backgroundColor          = $this->params->get('image_imagetype_generate_background_color', '#000000');
        $backgroundOverlay        = $this->params->get('image_imagetype_generate_background_overlay', 'rgba(0, 0, 0, 0.6)');
        $backgroundTextBackground = $this->params->get('image_imagetype_generate_background_text_background', '');
        $backgroundTextColor      = $this->params->get('image_imagetype_generate_background_text_color', '#ffffff');
        $backgroundTextFontSize   = (int) $this->params->get('image_imagetype_generate_background_text_fontsize', 20);
        $backgroundTextMargin     = (int) $this->params->get('image_imagetype_generate_background_text_margin', 10);
        $backgroundTextPadding    = (int) $this->params->get('image_imagetype_generate_background_text_padding', 10);
        $font                     = $this->params->get('image_imagetype_generate_background_text_font', '');
        $fontCustom               = $this->params->get('image_imagetype_generate_background_text_font_custom', '');

        // Check background type
        if ($backgroundType == 'fill')
        {
            $img = imagecreatetruecolor($backgroundWidth, $backgroundHeight);
            $bg  = $this->hexColorAllocate($img, $backgroundColor);
            imagefilledrectangle($img, 0, 0, $backgroundWidth, $backgroundHeight, $bg);
        }
        elseif ($backgroundType == 'static' || $backgroundType == 'current')
        {
            // Check empty bg or image
            if (
                ($backgroundType == 'static' && empty($backgroundImage)) ||
                ($backgroundType == 'current' && empty($image))
            )
            {
                $this->showDefaultImage();
            }

            if ($backgroundType == 'current')
            {
                $backgroundImage = str_replace(Uri::root(), '', $image);
            }

            $rgba            = $this->convertRgba($backgroundOverlay);
            $backgroundImage = JPATH_ROOT . '/' . ltrim($backgroundImage, '/');

            // Get image
            $sourceImage = imagecreatefromstring(file_get_contents($backgroundImage));

            // Show default image if not available
            if ($sourceImage === false)
            {
                $this->showDefaultImage();
            }

            // Create new image
            $img = imagecreatetruecolor($backgroundWidth, $backgroundHeight);

            // Set opacity
            imagesavealpha($img, true);
            $transparentBackground = imagecolorallocatealpha($img, 0, 0, 0, 127); // Полностью прозрачный фон
            imagefill($img, 0, 0, $transparentBackground);

            // Get original size and resize it
            $sourceWidth       = imagesx($sourceImage);
            $sourceHeight      = imagesy($sourceImage);
            $sourceAspectRatio = $sourceWidth / $sourceHeight;
            $newAspectRatio    = $backgroundWidth / $backgroundHeight;

            if ($sourceAspectRatio > $newAspectRatio)
            {
                // Trim to width
                $tempHeight = $sourceHeight;
                $tempWidth  = $sourceHeight * $newAspectRatio;
                $srcX       = ($sourceWidth - $tempWidth) / 2;
                $srcY       = 0;
            }
            else
            {
                // Trim to height
                $tempWidth  = $sourceWidth;
                $tempHeight = $sourceWidth / $newAspectRatio;
                $srcX       = 0;
                $srcY       = ($sourceHeight - $tempHeight) / 2;
            }

            // Copy and scale original image to new generated
            imagecopyresampled(
                $img, $sourceImage,
                0, 0, $srcX, $srcY,
                $backgroundWidth, $backgroundHeight, $tempWidth, $tempHeight
            );

            // Create overlay
            $overlay = imagecreatetruecolor($backgroundWidth, $backgroundHeight);
            imagesavealpha($overlay, true);
            $transparentOverlay = imagecolorallocatealpha($overlay, $rgba['red'], $rgba['green'], $rgba['blue'], 127 * $rgba['alpha']); // Прозрачность 0.5
            imagefill($overlay, 0, 0, $transparentOverlay);

            // Set overlay on image
            imagecopymerge($img, $overlay, 0, 0, 0, 0, $backgroundWidth, $backgroundHeight, $rgba['alpha'] * 100);
        }

        $colorForText = $this->hexColorAllocate($img, $backgroundTextColor);
        $font         = JPATH_ROOT . '/' . implode('/', ['media', 'plg_radicalschema_image', 'fonts', $font]);

        // Get custom font
        if (!empty($fontCustom))
        {
            $font = JPATH_ROOT . '/' . ltrim($fontCustom, '/');

            // Add custom fonts for other languages support, etc arabic or chines
            if ($langSef = $this->getCurrentLangSef())
            {
                $fontForLang = str_replace('.ttf', '_' . $langSef . '.ttf', $font);

                if (File::exists($fontForLang))
                {
                    $font = $fontForLang;
                }
            }
        }

        $width  = imagesx($img);
        $height = imagesy($img);

        $maxWidth          = imagesx($img) - (($backgroundTextMargin + $backgroundTextPadding) * 2);
        $fontSizeWidthChar = $backgroundTextFontSize / 1.5;
        $countForWrap      = (int) ((imagesx($img) - (($backgroundTextMargin + $backgroundTextPadding) * 2)) / $fontSizeWidthChar);

        // Set title
        $txt         = urldecode($title);
        $text        = explode("\n", wordwrap($txt, $countForWrap, "\n", true));
        $text_width  = 0;
        $text_height = 0;

        foreach ($text as $line)
        {
            $dimensions         = imagettfbbox($backgroundTextFontSize, 0, $font, $line);
            $text_width_current = max([$dimensions[2], $dimensions[4]]) - min([$dimensions[0], $dimensions[6]]);
            $text_height        += $dimensions[3] - $dimensions[5];

            if ($text_width < $text_width_current)
            {
                $text_width = $text_width_current;
            }
        }

        $delta_y = 0;
        if (count($text) > 1)
        {
            $delta_y = $backgroundTextFontSize * -1;

            foreach ($text as $line)
            {
                $delta_y += ($dimensions[3] + $backgroundTextFontSize * 1.5);
            }

            $delta_y -= $backgroundTextFontSize * 1.5 - $backgroundTextFontSize;
        }


        $centerX = $backgroundTextPadding;
        $centerY = $height / 2;

        // Title position top
        if ($titlePosition == 'top')
        {
            $centerY = $text_height + $backgroundTextPadding;
        }

        // Title position bottom
        if ($titlePosition == 'bottom')
        {
            $centerY = $height - $backgroundTextPadding - $text_height;
        }

        $centerRectX2 = $text_width > $maxWidth ? $maxWidth : $text_width;
        $centerRectY1 = $centerY - $delta_y / 2 - $backgroundTextPadding;
        $centerRectY2 = $centerY + $backgroundTextPadding * 2 + $delta_y / 2;
        $centerRectX2 += $backgroundTextPadding * 2 + $backgroundTextMargin;

        // Check transparent background text color
        if ($backgroundTextBackground)
        {
            $colorForBackground = $this->hexColorAllocate($img, $backgroundTextBackground);
            imagefilledrectangle($img, $backgroundTextMargin, $centerRectY1, $centerRectX2, $centerRectY2, $colorForBackground);
        }

        $y = $centerRectY1 + $backgroundTextPadding * 2;

        $delta_y = 0;
        foreach ($text as $line)
        {
            imagettftext($img, $backgroundTextFontSize, 0, $backgroundTextMargin + $backgroundTextPadding, $y + $delta_y, $colorForText, $font, $line);
            $delta_y += ($dimensions[3] + $backgroundTextFontSize * 1.5);
        }

        imagejpeg($img, $path . '/' . $file . '.jpg');

        //redirect to image
        $this->app->redirect($local . '/' . $file . '.jpg', 302);
    }

    /**
     * @param $im
     * @param $hex
     *
     * @return false|int
     *
     * @since __DEPLOY_VERSION__
     */
    private function hexColorAllocate($im, $hex)
    {
        $hex = ltrim($hex, '#');
        $a   = hexdec(substr($hex, 0, 2));
        $b   = hexdec(substr($hex, 2, 2));
        $c   = hexdec(substr($hex, 4, 2));

        return imagecolorallocate($im, $a, $b, $c);
    }

    /**
     * Method to convert rgba
     *
     * @param   string  $rgba
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    private function convertRgba(string $rgba)
    {
        $rgba    = trim($rgba, ' ()');
        $rgba    = str_replace(['rgba', 'rgb', '(', ')', ' '], '', $rgba);
        $rgbaArr = explode(',', $rgba);

        return [
            'red'   => $rgbaArr[0],
            'green' => $rgbaArr[1],
            'blue'  => $rgbaArr[2],
            'alpha' => $rgbaArr[3] ?? 0,
        ];
    }

    /**
     * If an error occurred during generation, then show the default picture
     *
     * @since __DEPLOY_VERSION__
     */
    private function showDefaultImage($redirect = true)
    {
        $img = $this->params->get('image_imagetype_generate_image_for_error', 'media/plg_radicalschema_image/images/default.png');

        if ($redirect)
        {
            $this->app->redirect($img, 302);
        }

        return $img;

    }

    /**
     * Get cache path for image
     *
     * @param   bool  $checkPath
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    private function getCachePath($file = null)
    {
        $folder = $this->params->get('image_imagetype_generate_cache', 'images');
        $path   = implode('/', [$folder, 'radicalschema']);

        // Add subfolder
        if ($this->params->get('image_imagetype_generate_cache_subfolder', 0))
        {
            $file      = $file ?: $this->getCacheFile();
            $md5path   = md5($file);
            $subfolder = substr($md5path, 0, 2);
            $path      = $path . '/' . $subfolder;
        }

        if (!file_exists(JPATH_ROOT . '/' . $path))
        {
            Folder::create(JPATH_ROOT . '/' . $path);
        }

        return $path;
    }

    /**
     * @param   string  $exs
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    private function getCacheFile()
    {
        $file = trim(preg_replace("#\?.*?$#isu", '', $this->app->input->server->get('REQUEST_URI', '', 'raw')), '/#');
        $file = str_replace('/', '-', $file);

        if (!$file)
        {
            $file = 'home';
        }

        $app = Factory::getApplication();

        $registeredurlparams = new \stdClass();

        // Get url parameters set by plugins
        if (!empty($app->registeredurlparams))
        {
            $registeredurlparams = $app->registeredurlparams;
        }

        // Platform defaults
        $defaulturlparams = [
            'format' => 'WORD',
            'option' => 'WORD',
            'view'   => 'WORD',
            'layout' => 'WORD',
            'tpl'    => 'CMD',
            'id'     => 'INT',
        ];

        // Add lang
        if (Multilanguage::isEnabled())
        {
            $defaulturlparams['lang'] = 'WORD';
        }

        // Use platform defaults if parameter doesn't already exist.
        foreach ($defaulturlparams as $param => $type)
        {
            if (!property_exists($registeredurlparams, $param))
            {
                $registeredurlparams->$param = $type;
            }
        }

        $safeuriaddon = new \stdClass();

        foreach ($registeredurlparams as $key => $value)
        {
            $safeuriaddon->$key = $app->getInput()->get($key, null, $value);
        }

        return $file . '_' . md5(serialize($safeuriaddon));
    }

    /**
     * Get current language sef code
     *
     * @return string|void
     *
     * @since __DEPLOY_VERSION__
     */
    public function getCurrentLangSef()
    {
        $languages = LanguageHelper::getLanguages();
        $languages = ArrayHelper::getColumn($languages, null, 'lang_code');

        $langCode = Factory::getApplication()->getLanguage()->getTag();

        if (isset($languages[$langCode]))
        {
            return $languages[$langCode]->sef;
        }
    }

    /**
     * Method for save data for cache
     *
     * @param   string  $file
     * @param   array   $data
     *
     * @return void
     *
     * @since version
     */
    public function setData($file = '', $data = [])
    {
        $path     = $this->getCachePath($file) . '/json';
        $pathFull = JPATH_ROOT . '/' . $path;

        if (!file_exists($pathFull))
        {
            Folder::create($pathFull);
        }

        file_put_contents($pathFull . '/' . $file . '.json', (new Registry($data))->toString());
    }

    /**
     * Method for get data from cache
     *
     * @param   string  $file
     *
     * @return array|false
     *
     * @since version
     */
    public function getData($file = '')
    {
        $path     = $this->getCachePath($file) . '/json';
        $pathFull = JPATH_ROOT . '/' . $path;

        if (!file_exists($pathFull))
        {
            return false;
        }

        $data = file_get_contents($pathFull . '/' . $file . '.json');

        return (new Registry($data))->toArray();
    }

}