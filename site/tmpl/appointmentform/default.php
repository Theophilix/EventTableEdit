<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// no direct access
defined('_JEXEC') or die;

$app = Factory::getApplication();

$main = $app->input;
$Itemid = $main->getInt('Itemid', '');

$id = $main->getInt('id', '');
$postget = $main->getArray();
$totalappointments_row_col = explode(',', $postget['rowcolmix']);
$datesofhead = [];

$appointmentsdate = [];
foreach ($totalappointments_row_col as $rowcol) {
    $temps = explode('_', $rowcol);
    $rops = $temps[0];

    $cops = $temps[1];
    $cols = $this->heads[$cops];
    $rows = $this->rows[$rops];
    $details = $rows[$cops];

    //if($details == 'free'){
    // add weekday in first row (head) //
    if (1 === (int)$this->item->showdayname) {
        $namesofday = strtoupper(date('l', strtotime(str_replace('.', '-', trim($cols->name)))));
        $datesofhead[] = Text::_('COM_EVENTTABLEEDIT_'.strtoupper($namesofday)).' '.$cols->name.' '.Text::_('COM_EVENTTABLEEDIT_UM').' '.$rows['0'];
    } else {
        $datesofhead[] = $cols->name.Text::_('COM_EVENTTABLEEDIT_UM').$rows['0'];
    }
    $appointmentsdate[] = str_replace('.', '-', $cols->name).' '.$rows['0'].':00';

    //}
 // END add weekday in first row (head) //
}
$datesofhead = implode(',', $datesofhead);
?>
<!--
<p><?php echo Text::sprintf('COM_EVENTTABLEEDIT_BOOK_BEGIN', $datesofhead); ?></p>
<p>
<?php echo Text::_('COM_EVENTTABLEEDIT_BUTTON_GO_BACKTEXT'); ?>
</p>-->

<script>
	function goback1(){
		
		window.location = "<?php echo JRoute::_('index.php?option=com_eventtableedit&view=appointments&id='.$id.'&Itemid='.$Itemid, false); ?>";
	}
</script>
<div class="appointmentforms">
	<h2>
		<?php echo Text::_('COM_EVENTTABLEEDIT_RESERVATION'); ?>
	</h2>
<div class="span6" style="float: right;">
	<?php
		$model = $this->getModel('appointmentform');
        $cols = $model->getHeads();
        $rows = $model->getRows();
		$totalappointments_row_col = explode(',', $postget['rowcolmix']);
		foreach ($totalappointments_row_col as $rowcol) {
			$temps = explode('_', $rowcol);
			$rops = $temps[0];
			$cops = $temps[1];
			$roweditpost = $rops;
			$coleditpost = $cops;

			$to_time = strtotime($rows['rows'][0][0]);
			$from_time = strtotime($rows['rows'][1][0]);
			$mintdiffrence = round(abs($from_time - $to_time) / 60, 2);
		}

		$postdateappointment = $appointmentsdate;

		if (count($appointmentsdate) > 0) {
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
			} ?>
		<h3><?php echo Text::_('COM_EVENTTABLEEDIT_TABLE_BOOKING'); ?></h3>
		<ul class="appintments_list">
			<?php

			foreach ($date_array as $keystart => $valueend) {
				?>
			<li>
				<?php  $exp_startdate = explode(' ', $keystart);
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
						$hoursends = (int)$exp_etime[0] + 1;
					} else {
						$hoursends1 = (int)$exp_etime[0] + 1;
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

				echo Text::_('COM_EVENTTABLEEDIT_'.strtoupper($namesofday1)).', '.date('d.m.Y', strtotime($keystart)).', '.$starttimeonly.' - '.$endtimeonly; ?>
			</li>
			<?php
			} ?>
			
		</ul>
		<?php
		} ?>
		<?php
		$session = Factory::getSession();
		$corresponding_table = $session->get('corresponding_table');
		if ($corresponding_table) {
			$corresptable = json_decode($this->item->corresptable, true);
			$corresponding_table_name = '';
			foreach ($corresptable as $key => $corresptabl) {
				if ($corresptabl === $corresponding_table) {
					$corresponding_table_name = $key;
				}
			}
			echo '<p><b>'.Text::_('COM_EVENTTABLEEDIT_SELECTED_OPTION').":</b> $corresponding_table_name</p>";
		}
		?>
</div>
<form action="<?php echo JURI::getInstance(); ?>" name="adminForm" id="adminForm" method="post" class="form-validate span6 appointmentform" style="float:left;">
	<div class="control-group">
  <label class="control-label"><?php echo Text::_('COM_EVENTTABLEEDIT_FIRSTNAME'); ?>*</label>
      <div class="controls"><input type="text" value="" name="first_name" class="required"></div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo Text::_('COM_EVENTTABLEEDIT_LASTNAME'); ?>*</label>
      <div class="controls"><input type="text" value="" name="last_name" class="required"></div>
</div>
<div class="control-group">
  <label class="control-label"><?php echo Text::_('COM_EVENTTABLEEDIT_EMAIL'); ?>*</label>
      <div class="controls"><input type="text" value="" name="email" class="required validate-email"></div>
</div>
<div class="control-group">
	<div class="controls" style="width:16px;float: left;margin-top: -3px;"><input type="checkbox" value="yes" name="oneics" id="oneics" class=""></div>
	<label class="control-label" for="oneics"><?php echo Text::_('COM_EVENTTABLEEDIT_ONE_ICS'); ?></label>
</div>
<div class="control-group">
  <label class="control-label"><?php echo Text::_('COM_EVENTTABLEEDIT_COMMENT'); ?></label>
      <div class="controls"><textarea name="comment" id="comment" cols="10" rows="5"></textarea></div>
</div>
<p>* <?php echo Text::_('COM_EVENTTABLEEDIT_STAR'); ?></p>
<br>
	<input type="hidden" name="option" value="com_eventtableedit" />
	<input type="hidden" name="view" value="appointmentform" />
	<input type="submit" name="submit" class="btn btn-primary" value="<?php echo Text::_('COM_EVENTTABLEEDIT_FINAL_RESERVATION'); ?>">
	<input type="button" class="btn btn-primary goback" value="<?php echo Text::_('COM_EVENTTABLEEDIT_GO_BACK'); ?>" name="goback" onclick="goback1();">
	<input type="hidden" name="task" value="appointmentform.save" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="rowcolmix" value="<?php echo $postget['rowcolmix']; ?>" />
	<!--<input type="hidden" name="col" value="<?php //echo $postget['col'];?>" />
	-->
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="dateappointment" value="<?php echo implode(',', $appointmentsdate); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
<div style="clear:both"></div>