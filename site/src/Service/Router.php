<?php


namespace ETE\Component\EventTableEdit\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;

class Router extends RouterBase
{
	
	public function build(&$query)
	{
		
		$segments = [];
		
		// get a menu item based on Itemid or currently active
		$app = Factory::getApplication();
		$menu = $app->getMenu();

		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();
		} else {
			$menuItem = $menu->getItem($query['Itemid']);
		}
		

		if (isset($query['view'])) {
			if ('etetable' != $query['view'] || !isset($menuItem)) {				
				$segments[] = $query['view'];
			}
		}

		if (isset($query['id'])) {
			if (!isset($menuItem)) {
				$segments[] = $query['id'];
			} elseif ('appointmentform' === $query['view'] || 'changetable' === $query['view'] || 'appointments' === $query['view'] || 'csvexport' === $query['view'] || 'csvimport' === $query['view']) {
				$segments[] = $query['id'];
			}
			if($query['view'] == 'appointments' || $query['view'] == 'etetable'){
				unset($segments[0]);
				unset($segments[1]);
			}
			
			unset($query['view']);
			unset($query['id']);
		}
		
		if (isset($query['row'])) {
			$segments[] = $query['row'];
			unset($query['row']);
		}
		if (isset($query['col'])) {
			$segments[] = $query['col'];
			unset($query['col']);
		}
		
		return $segments;
	}

	
	public function parse(&$segments)
	{
		
		$vars = [];

		//Get the active menu item.
		$app = Factory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getActive();

		//Handle View and Identifier
		switch ($segments[0]) {
			case 'changetable':
				
				$vars['view'] = 'changetable';

				$val = explode(':', $segments[1]);
				$vars['id'] = $val[0];
				unset($segments[0]);
				unset($segments[1]);
			break;
			case 'etetable':
			case 'eventtableedit':

				$vars['view'] = 'etetable';
				if (isset($segments[1])) {
					$vars['id'] = $segments[1];
				}
			break;
			case 'appointments':

				$vars['view'] = 'appointments';
				if (isset($segments[1])) {
					$vars['id'] = $segments[1];
				}
				unset($segments[0]);
				unset($segments[1]);
			break;
			case 'appointmentform':

				$vars['view'] = 'appointmentform';
				$vars['id'] = $segments[1];
				$vars['row'] = $segments[2];
				$vars['col'] = $segments[3];
			break;
			case 'csvexport':
				$vars['view'] = 'csvexport';
				$vars['id'] = $segments[1];
			break;
			case 'csvimport':
			
				$vars['view'] = 'csvimport';
				$vars['id'] = $segments[1];
			break;
		}	
		
		
		unset($segments[0]);
		unset($segments[1]);
		
		
		
		return $vars;
	}
}
