<?php

namespace ETE\Component\EventTableEdit\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;
require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';
require_once JPATH_COMPONENT.'/helper/datatypes.php';

class ChangetableModel extends ItemModel
{
	
	protected $_context = 'com_eventtableedit.changetable';
	
	
	public function __construct()
    {
        parent::__construct();

        // Load the parameters
        $app = Factory::getApplication('site');
        $params = $app->getParams();

        $this->setState('params', $params);
        $this->params = $params;
        $main = $app->input;
        $this->id = $main->getInt('id', '');

        $this->db = $this->getDbo();
    }
	
	
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		
		$this->setState('changetable.id', $pk);

		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = Factory::getUser();

		$asset = empty($pk) ? 'com_eventtableedit' : 'com_eventtableedit.changetable.' . $pk;

		
	}
	
	
	
	public function getItem($pk = null)
	{
		
		if (!isset($this->_item))
		{
			
			$cache = Factory::getCache('com_eventtableedit', 'callback');

			$pk = (int) ($pk ?: $this->getState('changetable.id'));

		
			$db = $this->getDbo();

			$loader = function ($pk) use ($db)
			{
				$query = $db->getQuery(true);

				$query->select('*')
					->from($db->quoteName('#__eventtableedit_heads', 'a'))
					->where($db->quoteName('a.table_id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);
				$query->order('a.ordering', 'asc');
				$db->setQuery($query);

				$data = $db->loadObjectList();								
				
				return $data;
				
			};

			try
			{
				$this->_item = $cache->get($loader, array($pk), md5(__METHOD__ . $pk));
			}
			catch (CacheExceptionInterface $e)
			{
				$this->_item = $loader($pk);
			}
		}
		

		return $this->_item;
	}
	
	/**
     * Get the table name and check if it is published.
     */
    public function getInfo()
    {
        $query = $this->db->getQuery(true);

        $query->select('d.name as tablename');
        $query->from('#__eventtableedit_details AS d');
        $query->where('d.id = '.$this->id);
        $query->where('d.published = 1');
        $this->db->setQuery($query);

        return $this->db->loadAssoc();
    }
	
	public function save($cid, $name, $datatype, $defaultSorting)
    {
		
		
        // Update rows table
		
        $this->createRowsTable();
		

        $deleteColIds = $this->deleteNotUsedCols($cid);
		
        // Delete not used entries in the heads table
        if (count($deleteColIds) > 0) {
            $query = 'DELETE FROM #__eventtableedit_heads'.
                     ' WHERE id IN ('.implode(',', $deleteColIds).')';
            $this->db->setQuery($query);
            $this->db->execute();
        }
        $db = Factory::getDBO();
        $updatecol = "UPDATE `#__eventtableedit_details` SET col='".count($name)."' WHERE id='".$this->id."'";
        $db->setQuery($updatecol);
        $db->execute();
        // Add the new table heads
		
		
        for ($a = 0; $a < count($name); ++$a) {
            /* $table = Table::getInstance('Heads', 'EventtableeditTable'); */
			$table = Table::getInstance('HeadsTable','ETE\Component\EventTableEdit\Administrator\Table\\');

            $data = [];
            $data['id'] = $cid[$a];
            $data['table_id'] = $this->id;
            $data['name'] = addslashes($name[$a]);
            $data['datatype'] = $datatype[$a];
            $data['defaultSorting'] = $defaultSorting[$a];
            $data['ordering'] = $a;			
			
            if (!$table->bind($data)) {
                echo $table->getError();
            }
            if (!$table->store()) {
                echo $table->getError();
            }

            $newId = $table->id;

            // Create or adjust the db table to save the entries
            $this->updateRowsTable($cid[$a], $newId, $datatype[$a]);
        }
    }
	
	/**
     * See if a new table has to be created.
     */
    private function createRowsTable()
    {
        // Need to use getPrefix because of a Joomla Bug
        // within quotes #__ is not replaced
        $query = 'SHOW TABLE STATUS LIKE \''.$this->db->getPrefix().'eventtableedit_rows_'.$this->id.'\'';
		
        $this->db->setQuery($query);

        if (count($this->db->loadObjectList()) > 0) {
            return false;
        }

        // A new table has to be created
        $query = 'CREATE TABLE #__eventtableedit_rows_'.$this->id.
                 ' (id INT NOT NULL AUTO_INCREMENT,'.
                 ' ordering INT(11) NOT NULL default 0,'.
                 ' created_by INT(11) NOT NULL default 0,'.
                 ' PRIMARY KEY (id))'.
                 ' ENGINE=MyISAM CHARACTER SET \'utf8\' COLLATE \'utf8_general_ci\'';
        $this->db->setQuery($query);
        $this->db->execute();
		
    }

    private function deleteNotUsedCols($cid)
    {
		
        // Get columns that has to be deleted
        $query = 'SELECT id FROM #__eventtableedit_heads'.
                 ' WHERE table_id = '.$this->id;
		
		
        if (count($cid) > 0) {
            $query .= ' AND id NOT IN ('.implode(',', $cid).')';
        }
        $this->db->setQuery($query);
        $rows = $this->db->loadColumn();

        for ($a = 0; $a < count($rows); ++$a) {
            $query = 'ALTER TABLE #__eventtableedit_rows_'.$this->id.
                     ' DROP COLUMN head_'.$rows[$a];
            $this->db->setQuery($query);
            $this->db->execute();
        }

        // Delete all rows if all colums are deleted
        if (!count($cid)) {
            $query = 'TRUNCATE TABLE #__eventtableedit_rows_'.$this->id;
            $this->db->setQuery($query);
            $this->db->execute();
        }

        return $rows;
    }

    /**
     * replace string between start and end string.
     */
    public function delete_all_between($beginning, $end, $string)
    {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if (false === $beginningPos || false === $endPos) {
            return $string;
        }

        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

        return str_replace($textToDelete, '', $string);
    }

    /**
     * Alters the _rows_$id table that it fits to the table heads.
     */
    private function updateRowsTable($cid, $newId, $datatype)
    {
        $query = 'ALTER TABLE #__eventtableedit_rows_'.$this->id.' ';

        // If it's a existing column
        if (0 !== (int)$cid) {
            $qx = 'SELECT DATA_TYPE as datatype FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "'.$this->db->getPrefix().'eventtableedit_rows_'.$this->id.'" AND COLUMN_NAME = "head_'.$newId.'"';
            $this->db->setQuery($qx);
            $this->db->execute();
            $new_datatype = $this->db->loadObject()->datatype;

            $map_datatype = $this->delete_all_between('(', ')', strtoupper(\Datatypes::mapDatatypes($datatype)));

            if (strtoupper($new_datatype) !== $map_datatype) {
				
				if ('int' == $datatype) {
					$q1 = 'UPDATE  #__eventtableedit_rows_'.$this->id.'  SET head_'.$newId.' = "0"';
					$this->db->setQuery($q1);
					$this->db->execute();
				}
				if ('time' == $datatype) {
					$q1 = 'UPDATE  #__eventtableedit_rows_'.$this->id.'  SET head_'.$newId.' = "00:00"';
					$this->db->setQuery($q1);
					$this->db->execute();
				}
				if ('text' == $datatype) {
					$q2 = 'UPDATE  #__eventtableedit_rows_'.$this->id.'  SET head_'.$newId.' = null';
					$this->db->setQuery($q2);
					$this->db->execute();
				}
				
				if ('date' == $datatype) {
					$q2 = 'UPDATE  #__eventtableedit_rows_'.$this->id.'  SET head_'.$newId.' = "0000-00-00"';
					$this->db->setQuery($q2);
					$this->db->execute();
				}
				
				
                $query .= 'CHANGE head_'.$newId.' head_'.$newId.' '.\Datatypes::mapDatatypes($datatype);
                
                $this->db->setQuery($query);
                $this->db->execute();

                if ('four_state' === $datatype) {
                    $q2 = 'UPDATE #__eventtableedit_rows_'.$this->id.' SET head_'.$newId.' = "0"';
                    $this->db->setQuery($q2);
                    $this->db->execute();
                } elseif ('boolean' === $datatype) {
                    $q3 = 'UPDATE #__eventtableedit_rows_'.$this->id.' SET head_'.$newId.' = "&nbsp;"';
                    $this->db->setQuery($q3);
                    $this->db->execute();
                }
            }
        } else {
            $detailquery = "SELECT normalorappointment FROM #__eventtableedit_details WHERE id ='".$this->id."'";
            $this->db->setQuery($detailquery);
            $appointment = $this->db->loadResult();
            $query .= 'ADD head_'.$newId.' '.\Datatypes::mapDatatypes($datatype);
            $this->db->setQuery($query);
            $this->db->execute();
            if (1 === (int)$appointment) {
                $update = 'UPDATE `#__eventtableedit_rows_'.$this->id.'` SET `head_'.$newId."`='free'";
                $this->db->setQuery($update);
                $this->db->execute();
            }
        }
    }

    public function getnormal_table($id)
    {
        $db = Factory::GetDBO();
        $select = "SELECT normalorappointment FROM #__eventtableedit_details WHERE id='".$id."'";
        $db->setQuery($select);
        $table = $db->loadResult();
        return $table;
    }
	
}
