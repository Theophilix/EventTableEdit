<?php

namespace ETE\Component\EventTableEdit\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Contacts list controller class.
 *
 * @since  1.6
 */
class DropdownsController extends AdminController
{
	
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unfeatured', 'featured');
	}

	
	public function getModel($name = 'Dropdown', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
