<?php

namespace ETE\Component\EventTableEdit\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\CMS\Plugin\PluginHelper;


require_once JPATH_COMPONENT.'/src/Model/CsvimportModel.php';
require_once JPATH_COMPONENT.'/src/Helper/datatypes.php';


/**
 * A subclass of Csvimport to handle importing a new table
 * so that the main class doesn't get too crowded.
 */
class CsvimportnewtableModel extends CsvimportModel
{
    private $detailsModel;
    private $tableName;

    public function __construct()
    {
        parent::__construct();
		
		$this->app = Factory::getApplication();
		$this->db = Factory::getDBO();

        $this->separator = $this->app->getUserState('com_eventtableedit.separator', ';');
        $this->doubleqt = $this->app->getUserState('com_eventtableedit.doubleqt', 1);
    }
	

    /**
     * Import all the csv data and create a new table.
     */
    public function importCsvNew($detailsModel, $name, $datatype)
    {
        $input = Factory::getApplication()->input;
        $checkfun = $input->get('checkfun');

        $this->detailsModel = $detailsModel;
        $this->checkfun = $checkfun;
        $this->tableName = $name;
        $this->heads['datatype'] = $datatype;

        if (!$this->createDetailsTable()) {
            return false;
        }

        $this->createRowsTable();
        $this->createHeadsTable();

        $this->readCsvFile();
        $this->app->setUserState('com_eventtableedit.csvError', false);
    }

    /**
     * Save the configuration.
     */
    private function createDetailsTable()
    {
        $data = [];
        $data['id'] = 0;
        $data['name'] = $this->tableName;
        $data['normalorappointment'] = $this->checkfun;

        $data['published'] = 1;
        if (!$this->detailsModel->save($data)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_EVENTTABLEEDIT_SAME_NAME'), 'error');
            return false;
        }

        // Get the table id
        $this->id = $this->app->getUserState('etetable.id', 0);
		
        return true;
    }

    private function createHeadsTable()
    {
        $this->getHeadLine();
        $db = Factory::getDBO();

        $col_count = count($this->csvHeadLine);
        $cntr = $col_count - 1;
        if (isset($this->csvHeadLine[$cntr]) && 'timestamp' === $this->csvHeadLine[$cntr]) {
            $col_count = $col_count - 1;
        }
        $updatecol = "UPDATE `#__eventtableedit_details` SET col='".$col_count."' WHERE id='".$this->id."'";
        $db->setQuery($updatecol);
        $db->execute();
        $len = count($this->csvHeadLine);
        if (isset($this->csvHeadLine[$cntr]) && 'timestamp' === $this->csvHeadLine[$cntr]) {
            $len = $len - 1;
        }

        for ($a = 0; $a < $len; ++$a) {
            //$table = Table::getInstance('Heads', 'EventtableeditTable');
            $table = Table::getInstance('HeadsTable','ETE\Component\EventTableEdit\Administrator\Table\\');
			
			
            $data = [];
            $data['table_id'] = $this->id;
            $data['name'] = $this->csvHeadLine[$a];
            $data['datatype'] = $this->heads['datatype'][$a];
            $data['ordering'] = $a;

            if (!$table->bind($data)) {
                echo $table->getError();
            }
            if (!$table->store()) {
                echo $table->getError();
            }

            $newId = $table->id;

            // Adjust the db rows table to save the entries
            $this->updateRowsTable($newId, $this->heads['datatype'][$a]);
        }
    }

    /**
     * A new table has to be created, insert into rows table.
     */
    private function createRowsTable()
    {
        // A new table has to be created
        $query = 'CREATE TABLE IF NOT EXISTS #__eventtableedit_rows_'.$this->id.
                 ' (id INT NOT NULL AUTO_INCREMENT,'.
                 ' ordering INT(11) NULL default 0,'.
                 ' created_by INT(11) NULL default 0,'.
                 ' timestamp TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,'.
                 ' PRIMARY KEY (id)
				 )'.
                 ' ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;';
        $this->db->setQuery($query);
        $this->db->execute();
    }

    /**
     * Alters the _rows_$id table that it fits to the table heads.
     */
    private function updateRowsTable($newId, $datatype)
    {
        $query = 'ALTER TABLE #__eventtableedit_rows_'.$this->id;
        $query .= ' ADD head_'.$newId.' '.\Datatypes::mapDatatypes($datatype);

        $this->db->setQuery($query);
        $this->db->execute();
    }
}
