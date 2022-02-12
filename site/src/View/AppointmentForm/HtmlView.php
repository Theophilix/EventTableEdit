<?php

namespace ETE\Component\EventTableEdit\Site\View\AppointmentForm;

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

        $this->item = $this->get('Item');

        // Check for errors.

        if (!$this->checkError()) {
            return false;
        }

        $this->heads = $this->get('Heads');

        $this->rows = $this->get('Rows');

        $main = $app->input;

        // Check for errors.

        if (!$this->checkError()) {
            return false;
        }

        // Get the parameters of the active menu item

        $params = $app->getParams();

        $params->merge($this->item->params);

        // check if access is not public

        $groups = $user->getAuthorisedViewLevels();

        $rows = $this->rows['rows'];

        if (isset($active->query['layout'])) {
            // We need to set the layout in case this is an alternative menu item (with an alternative layout)

            $this->setLayout($active->query['layout']);
        }

        $this->params =  $params;
        $this->rows = $rows;

        $this->_prepareDocument();
		

		parent::display($template);
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

    /**
     * Prepares the document.
     */
    protected function _prepareDocument()
    {
        $app = Factory::getApplication();

        $menus = $app->getMenu();

        $pathway = $app->getPathway();

        $title = null;

		$wa = $this->document->getWebAssetManager();
		$wa->useScript('core');
		$wa->useScript('jquery');
		$wa->useScript('field.calendar');
		$wa->useScript('field.calendar.helper');
		$wa->useScript('form.validate');

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
	
}