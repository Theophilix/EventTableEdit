<?php

namespace ETE\Component\EventTableEdit\Site\View\Appointments;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;


require_once JPATH_COMPONENT.'/helper/datatypes.php';
/**
 * View for the user identity validation form
 */
class HtmlView extends BaseHtmlView {
    

    public function display($template = null) {
		
		$app = Factory::getApplication();
        $user = Factory::getUser();
        $this->state = $this->get('State');
		$this->dropdowns	= $this->get('Dropdowns');
        $this->item = $this->get('Item');
		//$this->pagination	= $this->get('Pagination');
        $this->option_id = $this->get('OptionID');
		
		$this->unique = rand(5,9999);
		if (!$this->checkError()) {
            return false;
        }
		
		

        $this->heads = $this->get('Heads');
        $this->row = $this->get('Rows');	
		
		
        $main = $app->input;
		
		$this->print = $main->get('print', 0);
		
		$this->params = $app->getParams();
        $this->params->merge($this->item->params);
		
		
		$groups = $user->getAuthorisedViewLevels();

        $this->rows = $this->row['rows'];
		$this->additional = $this->row['additional'];
		
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
		parent::display($template);
    }
	
	protected function checkAccess()
    {
        $user = Factory::getUser();

        $userAccess = $user->getAuthorisedViewLevels();

        if (in_array($this->item->access, $userAccess)) {
            return true;
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            return false;
        }
    }
	
	
	private function isDefaultSorted() {
		if (!count($this->heads)) {
			return 0;
		}

		foreach ($this->heads as $head) {
			if ($head->defaultSorting != '' && $head->defaultSorting != ':') {
				return 1;
			}
		}
		return 0;
	}
	
	private function buildDropdownJsArray() {
		$ret = array();
		if(is_array($this->dropdowns)){
			for($a = 0; $a < count($this->dropdowns); $a++) {
				// If Dropdown was deleted
				if ($this->dropdowns[$a]['name'] == null) {
					$ret[$a]['meta']['name'] = '';
					$ret[$a]['meta']['id'] = -1;
					continue;
				}

				$ret[$a]['meta']['name'] = $this->dropdowns[$a]['name']['name'];
				$ret[$a]['meta']['id'] = $this->dropdowns[$a]['name']['id'];
				
				if (!count($this->dropdowns[$a]['items'])) continue;

				foreach ($this->dropdowns[$a]['items'] as $item) {
					$ret[$a]['items'][] = $item->name;
				}
			}
		}
		
		return $ret;		
	}
	
	private function containsDate() {
		if (!count($this->heads)) return false;

		foreach($this->heads as $row) {
			if($row->datatype == 'date') return true;
		}
		return false;
	}
	
	private function checkError()
    {
        if (count($errors = $this->get('Errors'))) {
            foreach ($errors as $error) {
                Factory::getApplication()->enqueueMessage($error, 'warning');
            }
            return false;
        }
        return true;
    }
	
	
	protected function _prepareDocument()
    {
        $app = Factory::getApplication();
        $menus = $app->getMenu();
        $pathway = $app->getPathway();
        $title = null;

        
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->page_heading = $this->params->get('page_title', $menu->title);
        } else {
            $this->params->page_heading = Text::_('COM_EVENTTABLEEDIT_DEFAULT_PAGE_TITLE');
        }

        $id = (int) @$menu->query['id'];

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = htmlspecialchars_decode($app->getCfg('sitename'));
        } elseif ($app->getCfg('sitename_pagetitles', 0)) {
            $title = Text::sprintf('JPAGETITLE', htmlspecialchars_decode($app->getCfg('sitename')), $title);
        }

        // Add css
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

        $mdata = $this->item->metadata->toArray();

        foreach ($mdata as $k => $v) {
            if ($v) {
                $this->document->setMetadata($k, $v);
            }
        }

		if ($this->print) {
            $this->preparePrintView();
        } else {
			
			$doc = Factory::getDocument();
			
			$wa = $this->document->getWebAssetManager();
			$wa->useScript('core');
			$wa->useScript('jquery');
			$wa->useScript('field.calendar');
			$wa->useScript('field.calendar.helper');
			
			$this->document->addScript('//code.jquery.com/ui/1.13.1/jquery-ui.js');
			
			require JPATH_SITE.'/components/com_eventtableedit/helper/phpToJs.php';
			
			$this->document->addScript($this->baseurl.'/components/com_eventtableedit/template/js/tablesaw.js');
			$this->document->addScript($this->baseurl.'/components/com_eventtableedit/template/js/tablesaw-init.js');		
			
			require JPATH_SITE.'/components/com_eventtableedit/helper/tableAjax.php';
			require JPATH_SITE.'/components/com_eventtableedit/helper/popup.php';
			
			$user = Factory::GetUser();
			if(in_array(8,$user->groups)){
				$style = '.etetable-linecolor0{background-color:#fff;}';
				$this->document->addStyleDeclaration( $style );			
			}
			
			if ($this->item->rowsort == 0) {
				$this->document->addStyleDeclaration(".eventtableedit .tablesaw-priority-50 {display: none !important;}");;
				$this->document->addStyleDeclaration(".eventtableedit .tablesaw-priority-60 {display: none !important;}");;
			}
		}
    }
	
	private function getVariableStyles($cellspacing, $cellpadding, $linecolor0, $linecolor1)
    {
        $style = [];
        $style[] = '#etetable-table td {padding: '.$cellpadding.'px;}';

        if (0 !== (int) $cellspacing) {
            $style[] = '#etetable-table {border-collapse: separate !important;}';
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
	private function preparePrintView()
    {
        $this->document->setMetaData('robots', 'noindex, nofollow');
        $this->params->set('access-add', 0);
        $this->params->set('access-create_admin', 0);
        $this->item->show_filter = 0;
    }
}