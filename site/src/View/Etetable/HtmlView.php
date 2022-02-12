<?php

namespace ETE\Component\EventTableEdit\Site\View\Etetable;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
/**
 * @package     Joomla.Site
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * View for the user identity validation form
 */
class HtmlView extends BaseHtmlView {
    

    /**
     * Display the view
     *
     * @param   string  $template  The name of the layout file to parse.
     * @return  void
     */
    public function display($template = null) {
		
		// Initialise variables.
        $app = Factory::getApplication();
        $user = Factory::getUser();
		$this->state = $this->get('State','etetable');
        $this->item = $this->get('Item');
		
		
        $this->unique = 'ETE_'.$this->item->alias.'_'.rand(0, 999);
		
		
		$this->heads = $this->get('Heads');
        $this->dropdowns = $this->get('Dropdowns');
        $this->rows = $this->get('Rows');
        $main = $app->input;
        $this->print = $main->get('print');
        $filterstring = $main->get('filterstring');
		
		$this->params = $app->getParams();
		
		$this->params->merge($this->item->params);

        $this->params->set('filterstring', $filterstring);
		
		$rows = $this->rows;
		
		$this->rows = $rows['rows'];
        $this->additional = $rows['additional'];
        $this->additional['defaultSorting'] = $this->isDefaultSorted();
        $this->additional['dropdowns'] = $this->buildDropdownJsArray();
        $this->additional['containsDate'] = $this->containsDate();
		if (isset($active->query['layout'])) {
            // We need to set the layout in case this is an alternative menu item (with an alternative layout)
            $this->setLayout($active->query['layout']);
        }
		
		Text::script('COM_EVENTTABLEEDIT_LAYOUT_LAYOUTMODE');
        Text::script('COM_EVENTTABLEEDIT_LAYOUT_STACK');
        Text::script('COM_EVENTTABLEEDIT_LAYOUT_SWIPE');
        Text::script('COM_EVENTTABLEEDIT_LAYOUT_TOGGLE');
		
		$this->_prepareDocument();
		 
		if (!$this->checkAccess()) {
            return false;
        }
        // Call the parent display to display the layout file
        parent::display($template);
    }
	
	
	protected function checkAccess()
    {
        $user = Factory::getUser();

        $userAccess = $user->getAuthorisedViewLevels();

        if (in_array($this->item->access, $userAccess)) {
            return true;
        } else {
            Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
    }
	
	function _prepareDocument(){
		$app = Factory::getApplication();
        $menus = $app->getMenu();
		$menu = $menus->getActive();

        if ($menu->title) {
            $this->page_heading = $menu->title;
        } else {
            $this->page_heading =  JText::_('COM_EVENTTABLEEDIT_DEFAULT_PAGE_TITLE');
        }
		
		$this->id = (int) @$menu->query['id'];
		$title = $this->params->get('page_title', '');
		if (empty($title)) {
            $title = htmlspecialchars_decode($app->getCfg('sitename'));
        } elseif ($app->getCfg('sitename_pagetitles', 0)) {
            $title = Text::sprintf('JPAGETITLE', htmlspecialchars_decode($app->getCfg('sitename')), $title);
        }
		
		$this->document->addStyleSheet($this->baseurl.'/components/com_eventtableedit/template/css/tablesaw.css');
        $this->document->addStyleSheet($this->baseurl.'/components/com_eventtableedit/template/css/eventtablecss.css');
		
		
		$this->document->addStyleDeclaration($this->getVariableStyles($this->item->cellspacing, $this->item->cellpadding, $this->item->tablecolor1, $this->item->tablecolor2));
		
		
		$this->document->addCustomTag($this->getBrowserStyles());

        $this->document->setTitle($title);
		
		if (empty($title)) {
            $title = $this->item->title;
            $this->document->setTitle($title);
        }

        if ($this->item->metadesc) {
            $this->document->setDescription($this->item->metadesc);
        }

        if ($this->item->metakey) {
            $this->document->setMetadata('keywords', $this->item->metakey);
        }

        if ('1' === $app->getCfg('MetaTitle')) {
            $this->document->setMetaData('title', $this->item->name);
        }
		/* echo "<pre>";
		print_r($this->item->metadata);
		die; */
		if($this->item->metadata){
			$mdata = $this->item->metadata->toArray();

			foreach ($mdata as $k => $v) {
				if ($v) {
					$this->document->setMetadata($k, $v);
				}
			}
		}
		
		
		if ($this->print) {
            $this->preparePrintView();
        } else {
            $script = 'function initClickEvent(){';
            $script .= 'initClickEvent_'.$this->unique.'();';
            $script .= '}';
            
			$this->document->addScriptDeclaration($script);
			
			$wa = $this->document->getWebAssetManager();
			$wa->useScript('core');
			$wa->useScript('jquery');
			$wa->useScript('field.calendar');
			$wa->useScript('field.calendar.helper');
			
            require_once JPATH_SITE.'/components/com_eventtableedit/helper/phpToJs.php';
			
            $doc = Factory::getDocument();
            $this->document->addScriptDeclaration('
													var newest = "'.Text::_('COM_EVENTTABLEEDIT_NEWEST').'";
													var oldest = "'.Text::_('COM_EVENTTABLEEDIT_OLDEST').'";
													var unique = "'.$this->unique.'";
													
													');
            $this->document->addScript($this->baseurl.'/components/com_eventtableedit/template/js/tablesaw.js?v3');
            $this->document->addScript($this->baseurl.'/components/com_eventtableedit/template/js/tablesaw-init.js');
			
			require_once JPATH_SITE.'/components/com_eventtableedit/helper/tableAjax.php';			
			$this->document->addScript($this->baseurl.'/media/system/js/fields/calendar-locales/date/gregorian/date-helper.js');
			$this->document->addScript('//code.jquery.com/ui/1.13.1/jquery-ui.js');
            require_once JPATH_SITE.'/components/com_eventtableedit/helper/popup.php';			
			
        }
		
	}
	
	private function isDefaultSorted()
    {
        if (!count($this->heads)) {
            return 0;
        }
		
        foreach ($this->heads as $head) {
            if ('' != $head->defaultSorting && ':' != $head->defaultSorting) {
				return 1;
            }
        }
        return 0;
    }
	
	private function buildDropdownJsArray()
    {
        $ret = [];

        for ($a = 0; $a < count($this->dropdowns); ++$a) {
            // If Dropdown was deleted
            if (null === $this->dropdowns[$a]['name']) {
                $ret[$a]['meta']['name'] = '';
                $ret[$a]['meta']['id'] = -1;
                continue;
            }

            $ret[$a]['meta']['name'] = $this->dropdowns[$a]['name']['name'];
            $ret[$a]['meta']['id'] = $this->dropdowns[$a]['name']['id'];

            if (!count($this->dropdowns[$a]['items'])) {
                continue;
            }

            foreach ($this->dropdowns[$a]['items'] as $item) {
                $ret[$a]['items'][] = $item->name;
            }
        }

        return $ret;
    }
	
	private function containsDate()
    {
        if (!count($this->heads)) {
            return false;
        }

        foreach ($this->heads as $row) {
            if ('date' === $row->datatype) {
                return true;
            }
        }
        return false;
    }
	
	private function getVariableStyles($cellspacing, $cellpadding, $linecolor0, $linecolor1)
    {
		
        $style = [];
		$linecolor0 = str_replace("#","",$linecolor0);
		$linecolor1 = str_replace("#","",$linecolor1);
        $style[] = '#etetable-table td {padding: '.$cellpadding.'px;}';
        $style[] = '@media screen and (min-width: 959px) and  (max-width: 995px) { #etetable-table td {padding: 0px;} }';
       $style[] = '.tablesaw tbody tr:nth-child(odd) {background-color: #'.(($linecolor0)?$linecolor0:'#ffffff').';}';
        $style[] = '.tablesaw tbody tr:nth-child(even) {background-color: #'.(($linecolor1)?$linecolor1:'#ffffff').';}';

        if (0 !== (int) $cellspacing) {
            $style[] = '#etetable-table {border-collapse: separate !important;}';
        }

        $style[] = '.eventtableedit .limit {display: none;}';

        if (0 === (int)$this->item->rowsort) {
            $style[] = '.eventtableedit .sort_col {display: none !important;}';
        }

        if (!$this->params->get('access-reorder')) {
            $style[] = '.eventtableedit .sort_col {display: none !important;}';
        }

        if (!$this->params->get('access-delete')) {
            $style[] = '.eventtableedit .del_col {display: none !important;}';
        }
		
        return implode("\n", $style);
    }
	private function getBrowserStyles()
    {
        $ie = '<!--[if IE]>'."\n";
        $ie .= '<link rel="stylesheet" href="'.$this->baseurl.'/components/com_eventtableedit/template/css/ie.css" />'."\n";
        $ie .= '<![endif]-->'."\n";

        $ie .= '<!--[if lte IE 7]>'."\n";
        $ie .= '<link rel="stylesheet" href="'.$this->baseurl.'/components/com_eventtableedit/template/css/ie7.css" />'."\n";
        $ie .= '<![endif]-->'."\n";

        return $ie;
    }

}