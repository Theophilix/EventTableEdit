<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';
class EtetableController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }
	
	/**
     * Get cellcontent.
     */
    public function ajaxGetCell()
    {
        $main = Factory::getApplication()->input;
        $rowId = $main->getInt('rowId', '-1');
        $tableId = $main->getInt('id', '-1');
        if (!$this->aclCheck('edit') && !$this->checkAclOwnRow($rowId)) {
            return false;
        }

        $postget = $main->getArray();

        $cell = $postget['cell'];

        //Get Model and perform action

        $model = $this->getModel('etetable');
        $ret = $model->getCell($rowId, $cell, $tableId);

        echo $ret;
        exit;
    }

    /**
     * Saves a cellcontent.
     */
    public function ajaxSaveCell()
    {
        $main = Factory::getApplication()->input;
        $rowId = $main->getInt('rowId', '-1');
        $tableId = $main->getInt('id', '-1');
        if (!$this->aclCheck('edit') && !$this->checkAclOwnRow($rowId)) {
            return false;
        }
        $postget = $main->getArray();

        $cell = $postget['cell'];
        $content = nl2br($postget['content']);

        $db = Factory::getDBO();
        // START if appointment text changed from appointment view then below code is efected //
        $gettable_settings = "SELECT * FROM #__eventtableedit_details WHERE id='".$postget['id']."'";
        $db->setQuery($gettable_settings);
        $current_table_settings = $db->loadobject();
        //Get Model and perform action
        $model = $this->getModel('etetable');
        $data = $model->saveCell($rowId, $cell, $content, $tableId);
        $ret = $data[0];

        if (1 === (int)$current_table_settings->normalorappointment) {
            $user = Factory::GetUser();
            if (in_array('8', $user->groups)) {
                $permisioncheck = $current_table_settings->showusernametoadmin;
                $admin = 1;
            } else {
                $permisioncheck = $current_table_settings->showusernametouser;
                $admin = 0;
            }

            if (0 !== (int)$cell) {
                if ('free' === $ret) {
                    $ret = '<span class="blueclass">'.Text::_(strtoupper($ret)).'</span>'; // free appointment
                } else {
                    if (1 === (int)$admin) {
                        if (0 === (int)$permisioncheck) {
                            $ret = 'reserved';
                            $ret = strtoupper($ret);
                        }
                    } else {
                        if (0 === (int)$permisioncheck) {
                            $ret = 'reserved';
                            $ret = strtoupper($ret);
                        }
                    }
                    $ret = '<span class="redclass">'.Text::_($ret).'</span>'; // reserved appointment
                }
            }
        } else {
            $ret = $ret;
        }
        // END if appointment text changed from appointment view then below code is efected //
        if (isset($datatype) && ('boolean' === $datatype || 'four_state' === $datatype)) {
            $pos = strpos($ret, 'cross.png');
            $pos1 = strpos($ret, 'tick.png');
            $pos2 = strpos($ret, 'question-mark.png');
            $atemptime = '';
            if (false !== $pos) {
                $atemptime = '<input type="hidden" value="0">';
            } elseif (false !== $pos1) {
                $atemptime = '<input type="hidden" value="1">';
            } elseif (false !== $pos2) {
                $atemptime = '<input type="hidden" value="2">';
            } else {
                $atemptime = '<input type="hidden" value="-1">';
            }
            echo $atemptime;
        }
        echo $ret.'|'.$data[1];
        exit;
    }

    /**
     * Create a new row through an ajax request.
     */
    public function ajaxNewRow()
    {
        if (!$this->aclCheck('add')) {
            return false;
        }
        $main = Factory::getApplication()->input;
        $tableId = $main->getInt('id', '-1');
        //Get Model and perform action
        $model = $this->getModel('etetable');
        $ret = $model->newRow($tableId);

        echo $ret;
        exit;
    }

    /**
     * Delete a row through an ajax request.
     */
    public function ajaxDeleteRow()
    {
        $main = Factory::getApplication()->input;
        $tableId = $main->getInt('id', '-1');
        $rowId = $main->getInt('rowId', '-1');

        if (!$this->aclCheck('delete') && !$this->checkAclOwnRow($rowId)) {
            return false;
        }

        //Get Model and perform action
        $model = $this->getModel('etetable');
        $model->deleteRow($rowId, $tableId);

        exit;
    }

    public function saveOrder()
    {
        if (!$this->aclCheck('reorder')) {
            return false;
        }
        $main = Factory::getApplication()->input;
        $postget = $main->getArray();

        $rowIds = $postget['rowId'];
        $order = $postget['order'];
        $Itemid = $postget['Itemid'];
        $id = $postget['id'];

        $model = $this->getModel('etetable');
        $model->saveOrder($rowIds, $order);

        $this->setRedirect(JRoute::_('index.php?option=com_eventtableedit&view=etetable&id='.$id.'&Itemid='.$Itemid, false),
                           Text::_('COM_EVENTTABLEEDIT_SUCCESSFUL_REORDER'));
    }

    public function ajaxSaveOrder()
    {
        if (!$this->aclCheck('reorder')) {
            echo false;
            exit;
        }
        $main = Factory::getApplication()->input;
        $postget = $main->getArray();

        $rowIds = explode(',', $postget['rowId']);
        $order = explode(',', $postget['order']);
        //$Itemid  = $postget['Itemid'];
        $id = $postget['id'];

        $model = $this->getModel('etetable');

        $model->saveOrder($rowIds, $order, $id);

        echo true;
        exit;
    }

    private function aclCheck($object)
    {
        $user = Factory::getUser();

        $main = Factory::getApplication()->input;
        $id = $main->getInt('id', '-1');
        $asset = 'com_eventtableedit.etetable.'.$id;

        if (!$user->authorise('core.'.$object, $asset)) {
            return false;
        }
        return true;
    }

    /**
     * Check if a user created a row himself and
     * has the right to edit it.
     */
    private function checkAclOwnRow($rowId)
    {
        $user = Factory::getUser();
        $uid = $user->get('id');

        $model = &$this->getModel('etetable');
        return $model->checkAclOwnRow($rowId, $uid);
    }

    public function setSessionOption()
    {
        $jinput = Factory::getApplication()->input;
        $session = Factory::getSession();
        $session->set('corresponding_table', $jinput->get('corresponding_table'));
    }
	
	public function ajaxReplaceRows(){
		
		$jinput = Factory::getApplication()->input;
		$db = Factory::getDBO();
		$model = $this->getModel('etetable');
		
		$filterstring = $jinput->get('filterstring','','string');
		$replacestring = $jinput->get('replacestring','','string');
		
		$tableId = $jinput->get('tableId');
		
		
		
		if($filterstring!="" && $replacestring!=""){
			if($item = $model->checkTable($tableId)){
				
				$db->setQuery("SELECT * FROM #__eventtableedit_rows_$tableId");
				$table = $db->loadObjectList();
				
				$db->setQuery("SELECT * FROM #__eventtableedit_heads where table_id = '$tableId'");
				$heads = $db->loadObjectList();
				
				
				foreach($table as $rows){
					$rowId = $rows->id;
					foreach($heads as $head){
						
						$cell = $rows->{'head_'.$head->id};
						//$cell = $model->parseCellForReplace($cell, $head->datatype, $item);
						$filter = $filterstring;
						$replace = $replacestring;
						
						if ('float' === $head->datatype) {
							$cell = \eteHelper::parseFloat($cell, $item->float_separator);
						}
						if ('date' === $head->datatype) {
							$cell = \eteHelper::date_mysql_to_german_to($cell, $item->dateformat);
						}
						
						$content = str_replace($filter, $replace, $cell);
						if ('date' === $head->datatype) {
							$content = \eteHelper::date_german_to_mysql($content);
						}
						if ('float' === $head->datatype) {
							$content = str_replace(',', '.', $content);
						}
						
						$currentTime = new \DateTime();
						$timestamp = $currentTime->format('Y-m-d H:i:s');
						$query = 'UPDATE #__eventtableedit_rows_'.$tableId.
							' SET `head_'.$head->id.'` = \''.$content.'\', timestamp = \''.$timestamp.'\' WHERE id = '.$rowId;
						//echo $query . '<br>';
						$db->setQuery($query);
						$db->execute();
					}
				}
				echo json_encode(array('status'=>true));die;
			}
		}
		
	}
    
}