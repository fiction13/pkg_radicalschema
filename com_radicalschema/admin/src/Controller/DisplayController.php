<?php
/*
 * @package   RadicalSchema
 * @version   __DEPLOY_VERSION__
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2025 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

namespace Joomla\Component\RadicalSchema\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

class DisplayController extends BaseController
{
	/**
     * The URL option for the component.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $option = 'com_radicalschema';

	/**
	 * The default view.
	 *
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $default_view = 'default';

	/**
	 * Typical view method for MVC based architecture.
	 *
	 * @param   bool   $cachable   If true, the view output will be cached
	 * @param   array  $urlparams  An array of safe url parameters and their variable types, for valid values.
	 *
	 * @throws  \Exception
	 *
	 * @return  static  A DisplayController object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = [])
	{
		$result = parent::display($cachable, $urlparams);

		return $result;
	}
}
