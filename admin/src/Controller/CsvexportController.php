<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eventtableedit
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ETE\Component\EventTableEdit\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Utilities\ArrayHelper;

/**
 * Controller for a single contact
 *
 * @since  1.6
 */
class CsvexportController extends FormController
{
	use VersionableControllerTrait;
	
	public function export()
    {
        // ACL Check
        $user = Factory::getUser();
        if (!$user->authorise('core.csv', 'com_eventtableedit')) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
            $this->setRedirect(JRoute('index.php?option=com_eventtableedit&view=etetables'));
            return false;
        }

        // Initialize Variables
        $this->model = $this->getModel();

        $input = Factory::getApplication()->input;
        $postget = $input->getArray();

        $this->id = $postget['tableList'];
        $this->separator = $postget['separator'];
        $this->doubleqt = $postget['doubleqt'];
        $this->csvexporttimestamp = $postget['csvexporttimestamp'];

        $input->set('com_eventtableedit.layout', 'summary');
        $input->set('view', 'csvexport');

        $this->model->setVariables($this->id, $this->separator, $this->doubleqt, $this->csvexporttimestamp);
        $this->model->export();

        parent::display();
    }
		
	
	public function download()
    {
        $app = Factory::getApplication();
        $id = $app->input->get('tableList');
        $file = JPATH_ROOT.'/components/com_eventtableedit/csv_'.$id.'.csv';

        header('Content-Description: File Transfer');
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($file));
        readfile($file);
        exit;
    }
}
