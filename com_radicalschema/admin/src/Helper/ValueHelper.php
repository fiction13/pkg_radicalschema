<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Helper;

defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use stdClass;

class ValueHelper
{
	/**
	 * Method for prepare text
	 *
	 * @param   string  $text
	 * @param   int     $limit
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function prepareText(string $text, $limit = 0)
	{
		// Remove <script> tags
		$text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);

		// Strip HTML tags/comments and minify
		$text = str_replace(array("\r\n", "\n", "\r"), ' ', trim(strip_tags($text)));

		// Truncate text
		if ($limit > 0)
		{
			if (strlen($text) > $limit)
			{
				$text = StringHelper::substr($text, 0, $limit) . '...';
			}
			else
			{
				$text = StringHelper::substr($text, 0, $limit);
			}
		}

		return $text;
	}

	/**
	 * Method for prepare date
	 *
	 * @param $date
	 *
	 * @return mixed|Date|string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function prepareDate($date)
	{
		$date = is_string($date) ? trim($date) : $date;

		if (empty($date) || is_null($date) || $date == '0000-00-00 00:00:00')
		{
			return $date;
		}

		// Skip if date is already in ISO8601 format
		if (strpos($date, 'T') !== false)
		{
			return $date;
		}

		try
		{
			$date     = strtotime($date);
			$timeZone = new DateTimeZone(Factory::getConfig()->get('offset', 'UTC'));

			$date = new Date($date, $timeZone);

			return $date->toISO8601(true);

		}
		catch (\Exception $e)
		{
			return $date;
		}
	}

	/**
	 * Method for prepare user
	 *
	 * @param   int|string  $user
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function prepareUser($user)
	{
		if (!is_numeric($user))
		{
			return $user;
		}

		$userObject = Factory::getUser($user);

		if ($userObject)
		{
			return $userObject->name;
		}

		return Text::_('PLG_SYSTEM_RADICALSCHEMA_NO_USER');
	}

	/**
	 * Method for prepare link
	 *
	 * @param   string  $url
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function prepareLink($url, $relative = false)
	{
		if (empty($url))
		{
			return '';
		}

		// No prepare direct links
		if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false)
		{
			return $url;
		}

		// Clean image providers
		if ($pos = strpos($url, '#'))
		{
			$url = substr($url, 0, $pos);
		}

		if ($relative)
		{
			return $url;
		}

		return rtrim(Uri::root(), '/') . '/' . ltrim($url, '/');
	}

	/**
	 * Method for get image size
	 *
	 * @param   string  $src  Image url
	 *
	 * @return stdClass
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getImageSize($src)
	{
		$result         = new \stdClass();
		$result->width  = '';
		$result->height = '';

		if (empty($src) || strpos($src, 'com_ajax') !== false)
		{
			return $result;
		}

		// Clean image providers
		if (strpos($src, '#') !== false)
		{
			$imgParams      = explode('#', $src);
			$uri            = new Uri($imgParams[1]);
			$result->width  = $uri->getVar('width');
			$result->height = $uri->getVar('height');

			return $result;
		}

		try
		{
			$src = str_replace(Uri::root(), JPATH_ROOT . '/', $src);

			if (!$src || !file_exists($src))
			{
				return $result;
			}

			$imageSize      = getimagesize($src);
			$result->width  = $imageSize[0];
			$result->height = $imageSize[1];
		}
		catch (\Exception $e)
		{
			// noop
		}

		return $result;
	}

	/**
	 * Method for clean text
	 *
	 * @param   string  $string
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function cleanText(string $string)
	{
		// Remove spaces
		$string = str_replace(' ', '-', $string);

		// Remove special characters
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

		// Remove doubled dash
		return preg_replace('/-+/', '-', $string);
	}

	/**
	 * Method for get first image from text
	 *
	 * @param $text Text
	 *
	 * @return mixed|string|void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getFirstImage(string $text)
	{
		if (empty($text))
		{
			return;
		}

		preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $text, $img);

		if (!empty($img) && isset($img['src']))
		{
			return $img['src'];
		}

		return '';
	}

	/**
	 * @param $text
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getArrayFromText($text)
	{
		if (empty($text))
		{
			return [];
		}

		if (is_string($text))
		{
			$result = preg_split('/\r\n|\r|\n/', $text);
			$result = array_values(array_filter($result));
			$result = array_map('strip_tags', $result);

			return $result;
		}

		return $text;
	}

	/**
	 * Prepare item object with json values to array
	 *
	 * @param $item Object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function prepareArray($array)
	{
		if (!empty($array))
		{
			foreach ($array as $key => $item)
			{
				if (self::isJson($item))
				{
					$array[$key] = (new Registry($item))->toArray();
				}
			}
		}

		return $array;
	}

	/**
	 * Check string json or not
	 *
	 * @param   string  $string
	 *
	 * @return bool
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function isJson($string)
	{
		if (is_array($string) || is_object($string))
		{
			return false;
		}

		return is_object(json_decode($string ?? ''));
	}
}