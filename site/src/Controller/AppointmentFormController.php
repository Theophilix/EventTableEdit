<?php

namespace ETE\Component\EventTableEdit\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;


require_once JPATH_SITE.'/components/com_eventtableedit/helper/etetable.php';
class AppointmentFormController extends FormController {
	
	
	public function display($cachable = false, $urlparams = array()) {
		
		$document = Factory::getDocument();
        $viewName = $this->input->getCmd('view', 'login');
        $viewFormat = $document->getType();
        
        $view = $this->getView($viewName, $viewFormat);
        
        $view->document = $document;
        $view->display();
    }
	 
	
	
	public function save($key = null, $urlVar = null)
    {
        $app = Factory::getApplication();
        $main = Factory::getApplication()->input;
        $post = $main->getArray();
        $oneics = false;
        if (isset($post['oneics']) && 'yes' === $post['oneics']) {
            $oneics = true;
        }
        $model = $this->getModel('appointmentform');
		
        $totalappointments_row_col = explode(',', $post['rowcolmix']);
        $tableeditpost = $post['id'];
        $tableeditpost_option = $post['id'];
        $session = Factory::getSession();
        $corresponding_table = $session->get('corresponding_table');
        if ($corresponding_table) {
            $tableeditpost_option = $corresponding_table;
        }

        $Itemid = $post['Itemid'];

        $cols = $model->getHeads();
        $rows = $model->getRows();
        $tableeditpostalldata = $model->getItem();
        $hoursitem = $tableeditpostalldata->hours;
        $db = Factory::GetDBO();
        $postdateappointment = explode(',', $post['dateappointment']);

        $session = Factory::getSession();
        $corresponding_table = $session->get('corresponding_table');
        if ($corresponding_table) {
            $corresptable = json_decode($tableeditpostalldata->corresptable, true);
            $corresponding_table_name = '';
            foreach ($corresptable as $key => $corresptabl) {
                if ($corresptabl === $corresponding_table) {
                    $corresponding_table_name = $key;
                }
            }
        }

        //	implode(glue,$user->id);

        // update appointment date to reserved //
        foreach ($totalappointments_row_col as $rowcol) {
            $temps = explode('_', $rowcol);
            $rops = $temps[0];
            $cops = $temps[1];
            $roweditpost = $rops;
            $coleditpost = $cops;

            //	echo $mintdiffrence;

            $to_time = strtotime($rows['rows'][0][0]);
            $from_time = strtotime($rows['rows'][1][0]);
            $mintdiffrence = round(abs($from_time - $to_time) / 60, 2);
            $findupdatecell = $cols[$coleditpost]->head;
            $rowupdates = $roweditpost + 1;

            $selectuserd = sprintf("SELECT %s FROM #__eventtableedit_rows_%s WHERE id='%s'", $findupdatecell, $tableeditpost_option, $rowupdates);

            $db->setQuery($selectuserd);
            $getUserbooking = $db->loadResult();

            if ('free' === $getUserbooking) {
                $reserved_app = $post['first_name'].' '.$post['last_name'];
            } else {
                $temp_check = strtolower(trim($getUserbooking));
                $posUser = strpos($temp_check, '<br />');
                if (false !== $posUser) {
                    $reserved_app = $getUserbooking.'<br />'.$post['first_name'].' '.$post['last_name'];
                } else {
                    $reserved_app = $post['first_name'].' '.$post['last_name'];
                }
            }

            if ($corresponding_table) {
                $reserved_app = $reserved_app.' ('.$corresponding_table_name.')';
            }
            $Update = sprintf("UPDATE  #__eventtableedit_rows_%s SET %s='%s' WHERE id='%s'", $tableeditpost_option, $findupdatecell, $reserved_app, $rowupdates);
            $db->setQuery($Update);
            $db->execute();
        }
        // END update appointment date to reserved //

        // create ics files //
        $ttemp = 0;
        $addAttachment = [];

        $timeArr = $postdateappointment;
        sort($timeArr);

        $date_array = [];
        $start = '';
        $ref_start = &$start;
        $end = '';
        $ref_end = &$end;
        foreach ($timeArr as $time) {
            $date = date('Y-m-d', strtotime($time));
            if ('' === $start || strtotime($time) < strtotime($start)) {
                $ref_start = $time;
            }
            if (strtotime($time) > strtotime($end) && strtotime($time) <= strtotime('+ '.$mintdiffrence.' minutes', strtotime($end))) {
                $ref_end = $time;
            } else {
                $ref_start = $time;
                $ref_end = $time;
                $date_array[$time] = $time;
            }
            $date_array[$start] = $end;
        }

        $array = [];
        foreach ($date_array as $key => $value) {
            $key = date('Y-m-d H:i:s', strtotime($key));
            $array[$key] = $value;
        }
        ksort($array);
        $date_array = [];
        foreach ($array as $key => $value) {
            $key = date('d.m.Y H:i:s', strtotime($key));
            $date_array[$key] = $value;
        }

        $arrayof_sdates = [];
        $arrayof_times = [];

        $tableeditpostalldata->icsfilename = str_replace('{first_name}', $post['first_name'], $tableeditpostalldata->icsfilename);
        $tableeditpostalldata->icsfilename = str_replace('{last_name}', $post['last_name'], $tableeditpostalldata->icsfilename);

        if ($oneics) {
            $ical_oneics = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//bobbin v0.1//NONSGML iCal Writer//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH';

            $filename_oneics = $tableeditpostalldata->icsfilename.'.ics';
        }

        foreach ($date_array as $keyu => $valueu) {
            $exp_startdate = explode(' ', $keyu);
            $exp_sdate = explode('-', $exp_startdate[0]);
            $timesremovedsec = explode(':', $exp_startdate[1]);
            $exp_stime = explode(':', $exp_startdate[1]);
            $starttimeonly = $exp_stime[0].$exp_stime[1].$exp_stime[2];
            $starttimeonly_email = $exp_stime[0].':'.$exp_stime[1];
            $startdate = date('Ymd', strtotime($keyu)).'T'.$starttimeonly;
            $exp_enddate = explode(' ', $valueu);
            $exp_edate = explode('-', $exp_enddate[0]);
            $exp_etime = explode(':', $exp_enddate[1]);
            $mintplus = intval($exp_etime[1]) + intval($mintdiffrence);
            if ($mintplus >= 60) {
                $mintsend = $mintplus - 60;
                if ($mintsend > 9) {
                    $mintsendadd = $mintsend;
                } else {
                    $mintsendadd = '0'.$mintsend;
                }
                if ($exp_etime[0] >= 9) {
                    $hoursends = $exp_etime[0] + 1;
                } else {
                    $hoursends1 = $exp_etime[0] + 1;
                    $hoursends = '0'.$hoursends1;
                }
                if (24 === $hoursends) {
                    $endtimeonly = '00'.$mintsendadd.$exp_etime[2];

                    $enddate = date('Ymd', strtotime($valueu) + 3600 * 24).'T'.$endtimeonly;
                } else {
                    $endtimeonly = $hoursends.$mintsendadd.$exp_etime[2];
                    $enddate = date('Ymd', strtotime($valueu)).'T'.$endtimeonly;
                }
            } else {
                $endtimeonly = $exp_etime[0].$mintplus.$exp_etime[2];
                $enddate = date('Ymd', strtotime($valueu)).'T'.$endtimeonly;
            }
            $arrayof_sdates[] = date('d.m.Y', strtotime($keyu));
            $arrayof_times[] = $starttimeonly_email;

            // START CAL //

            $config = Factory::getConfig();
            $summary = $tableeditpostalldata->summary;
			
			
			
            $datestart = $startdate;
            $dateend = $enddate;

            $address = $tableeditpostalldata->location;
			
            $uri = \JURI::root();
            $description = $post['comment'];

            $summary = str_replace('{first_name}', $post['first_name'], $summary);
            $summary = str_replace('{last_name}', $post['last_name'], $summary);

            if ($oneics) {
                $ical_oneics .= '
BEGIN:VEVENT
DTEND:'.$dateend.'
UID:'.uniqid().'
DTSTAMP:'.$datestart.'
LOCATION:'.$this->escapeString($address).'
DESCRIPTION:'.$this->escapeString($description).'
URL;VALUE=URI:'.$this->escapeString($uri).'
SUMMARY:'.$this->escapeString($summary).'
DTSTART:'.$datestart.'
END:VEVENT';
            } else {
                $ical = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTEND:'.$dateend.'
UID:'.uniqid().'
DTSTAMP:'.$datestart.'
LOCATION:'.$this->escapeString($address).'
DESCRIPTION:'.$this->escapeString($description).'
URL;VALUE=URI:'.$this->escapeString($uri).'
SUMMARY:'.$this->escapeString($summary).'
DTSTART:'.$datestart.'
END:VEVENT
END:VCALENDAR';
                $ttemp1 = $ttemp + 1;
                $filename = $tableeditpostalldata->icsfilename.$ttemp1.'.ics';
                file_put_contents(JPATH_BASE.'/components/com_eventtableedit/template/ics/'.$filename, $ical);
                $addAttachment[] = JPATH_BASE.'/components/com_eventtableedit/template/ics/'.$filename;
            }

            ++$ttemp;
        }
        if ($oneics) {
            $ical_oneics .= '
END:VCALENDAR';
            file_put_contents(JPATH_BASE.'/components/com_eventtableedit/template/ics/'.$filename_oneics, $ical_oneics);
            $addAttachment[] = JPATH_BASE.'/components/com_eventtableedit/template/ics/'.$filename_oneics;
        }

        $msg = Text::_('COM_EVENTEDITTABLE_APPOINTMENT_SUCCESSFULLY_BOOKED');

        $datetimelist_body = '<ul style="list-style:none;">';
        foreach ($date_array as $keystart => $valueend) {
            $exp_startdate = explode(' ', $keystart);
            $exp_sdate = explode('-', $exp_startdate[0]);
            $timesremovedsec = explode(':', $exp_startdate[1]);
            $exp_stime = explode(':', $exp_startdate[1]);

            $starttimeonly = $exp_stime[0].':'.$exp_stime[1];

            $exp_enddate = explode(' ', $valueend);
            $exp_edate = explode('-', $exp_enddate[0]);

            $exp_etime = explode(':', $exp_enddate[1]);

            $mintplus = intval($exp_etime[1]) + intval($mintdiffrence);

            if ($mintplus >= 60) {
                $mintsend = $mintplus - 60;

                if ($mintsend > 9) {
                    $mintsendadd = $mintsend;
                } else {
                    $mintsendadd = '0'.$mintsend;
                }

                if ($exp_etime[0] >= 9) {
                    $hoursends = $exp_etime[0] + 1;
                } else {
                    $hoursends1 = $exp_etime[0] + 1;
                    $hoursends = '0'.$hoursends1;
                }
                if (24 === $hoursends) {
                    $endtimeonly = '00:'.$mintsendadd;
                } else {
                    $endtimeonly = $hoursends.':'.$mintsendadd;
                }
            } else {
                $endtimeonly = $exp_etime[0].':'.$mintplus;
            }

            $namesofday1 = date('l', strtotime($keystart));

            $datetimelist_body .= '<li>'.Text::_('COM_EVENTTABLEEDIT_'.strtoupper($namesofday1)).', '.date('d.m.Y', strtotime($keystart)).', '.$starttimeonly.' - '.$endtimeonly.'</li>';
        }
        $datetimelist_body .= '</ul>';

        //$replace_onlydate = implode(' / ',$arrayof_sdates);
        //$replace_onlytime = implode(' / ',$arrayof_times);

        // START user email //
        $mailer = Factory::getMailer();
        $config = Factory::getConfig();
        $sender = [
                    $tableeditpostalldata->email,
                    $tableeditpostalldata->displayname,
                ];

        $subject = $tableeditpostalldata->useremailsubject;
        $subject = str_replace('{date}', str_replace('-', '.', $exp_startdate[0]), $subject);
        $subject = str_replace('{time}', $timesremovedsec[0].':'.$timesremovedsec[1], $subject);
        $body = $tableeditpostalldata->useremailtext;

        $body = str_replace('{datetimelist}', $datetimelist_body, $body);
		if(isset($corresponding_table_name))
			$body = str_replace('{option}', $corresponding_table_name, $body);

        $mailer->setSender($sender);
        $mailer->addRecipient($post['email']);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        // Optional file attached
        $mailer->addAttachment($addAttachment);
        $mailer->Send();
        // End user email //

        // Start admin email //
        $mailer = Factory::getMailer();
        $config = Factory::getConfig();
        $sender = [
                    $config->get('mailfrom'),
                    $tableeditpostalldata->displayname,
                ];
        //$adminsubject = Text::sprintf('COM_EVENTEDITTABLE_APPOINTMENT_ADMIN_BODY',$post['first_name'],	$post['last_name']);
        $adminsubject = $tableeditpostalldata->adminemailsubject;
        $adminsubject = str_replace('{first_name}', $post['first_name'], $adminsubject);
        $adminsubject = str_replace('{last_name}', $post['last_name'], $adminsubject);

        $description = $post['comment'];

        $description_adminbody = $tableeditpostalldata->adminemailtext;
        $description_adminbody = str_replace('{comment}', $post['comment'], $description_adminbody);
        $description_adminbody = str_replace('{datetimelist}', $datetimelist_body, $description_adminbody);
        $description_adminbody = str_replace('{option}', $corresponding_table_name, $description_adminbody);

        //$adminbody   = $this->escapeString($description_adminbody);
        $adminbody = $description_adminbody;
        $mailer->setSender($sender);
        $mailer->addRecipient($tableeditpostalldata->email);
        $mailer->isHTML(true);
        $mailer->setSubject($adminsubject);

        $mailer->Encoding = 'base64';
        $mailer->setBody($adminbody);
        // Optional file attached
        $mailer->addAttachment($addAttachment);
        $mailer->Send();
        // End admin email //
        if (count($addAttachment) > 0) {
            foreach ($addAttachment as $oneattachment) {
                if (file_exists($oneattachment)) {
                    unlink($oneattachment);
                }
            }
        }

        //$app->redirect(Route::_('index.php?option=com_eventtableedit&view=appointments&id='.$tableeditpost.'&Itemid='.$Itemid, false), $msg);
		
		Factory::getApplication()->enqueueMessage($msg, 'success');
        $this->setRedirect(Route::_('index.php?option=com_eventtableedit&view=appointments&id='.$tableeditpost.'&Itemid='.$Itemid, false));
        $this->redirect();
		die;
    }

    public function escapeString($string)
    {
        return preg_replace('/([\,;])/', '\\\$1', $string);
    }

    public function date_sort($a, $b)
    {
        return strtotime($a) - strtotime($b);
    }
}