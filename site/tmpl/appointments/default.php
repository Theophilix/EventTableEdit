<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

// no direct access
defined('_JEXEC') or die;

$main = Factory::getApplication()->input;

$Itemid = $main->getInt('Itemid', '');

$postget = $main->getArray();



if (isset($requests['print'])) {
	$document = JFactory::getDocument();
	$style = '.appointmentsbtn{display: none;}';
	$document->addStyleDeclaration( $style );
}
 ?>

<?php if (!$this->option_id && $this->item->add_option_list) {
	$document = JFactory::getDocument();
	$style = '.etetable-outtable, input.btn.btn-primary.appointmentsbtn{display:none;}';
	$document->addStyleDeclaration( $style );
    
 }?>

<div class="eventtableedit<?php echo $this->params->get('pageclass_sfx'); ?>">

<ul class="actions">
	<?php if ($this->item->show_print_view) :?>
	<li class="print-icon">
		<?php if (!$this->print) : ?>
			<?php echo str_replace('view=etetable', 'view=appointments', JHtml::_('icon.print_popup', $this->item, $this->params)); ?>
			<?php //echo JHtml::_('icon.print_popup',  $this->item, $this->params);?>
		<?php else : ?>
			<?php echo JHtml::_('icon.print_screen', $this->item, $this->params); ?>
		<?php endif; ?>
	</li>
	<?php endif; ?>

	<?php if ($this->params->get('access-create_admin')) :?>
	<li class="admin-icon">
		<?php /* if ($this->heads) :?>
			<?php echo JHtml::_('icon.adminTable', $this->item, JText::_('COM_EVENTTABLEEDIT_ETETABLE_ADMIN')); ?>
		<?php else: ?>
			<?php echo JHtml::_('icon.adminTable', $this->item, JText::_('COM_EVENTTABLEEDIT_ETETABLE_CREATE')); ?>
		<?php endif; */ ?>
		
		<?php if ($this->heads) :?>
			<?php 
			
			$url = 'index.php?option=com_eventtableedit&view=changetable&id='.$this->item->slug.'&Itemid=' . $Itemid;
			$button = JHTML::_('image', JURI::base().'/components/com_eventtableedit/template/images/edit.png', JText::_('COM_EVENTTABLEEDIT_ETETABLE_ADMIN'), null, true);
			$attribs['title'] = JText::_('COM_EVENTTABLEEDIT_ETETABLE_ADMIN');
			echo JHTML::_('link', Route::_($url), $button, $attribs);
			?>
		<?php else: ?>
			<?php 
			$url = 'index.php?option=com_eventtableedit&view=changetable&id='.$this->item->slug.'&Itemid=' . $Itemid;
			$button = JHTML::_('image', JURI::base().'/components/com_eventtableedit/template/images/edit.png', JText::_('COM_EVENTTABLEEDIT_ETETABLE_ADMIN'), null, true);
			$attribs['title'] = JText::_('COM_EVENTTABLEEDIT_ETETABLE_CREATE');
			echo JHTML::_('link', Route::_($url), $button, $attribs);
			//echo JHTML::_('icon.adminTable', $this->item, JText::_('COM_EVENTTABLEEDIT_ETETABLE_CREATE')); ?>
		<?php endif; ?>
	</li>
	<?php endif; ?>
	<?php if ($this->params->get('access-csv')) :?>
	<li class="admin-icon">
		<a href="<?php echo Route::_('index.php?option=com_eventtableedit&view=csvexport&id=' . $this->item->id .'&Itemid=' . $Itemid . '&return=' . base64_encode(JUri::getInstance()))?>" title="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_EXPORT')?>">
			<img src="components/com_eventtableedit/template/images/csv-download.png" alt="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_EXPORT')?>"/>
		</a>
	</li>
	<li class="admin-icon">
		<a href="<?php echo Route::_('index.php?option=com_eventtableedit&view=csvimport&id=' . $this->item->id .'&Itemid=' . $Itemid . '&checkfun=1&return=' . base64_encode(JUri::getInstance())) . ''?>" title="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_IMPORT')?>">
			<img src="components/com_eventtableedit/template/images/csv-upload.png" alt="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_IMPORT')?>"/>
		</a>
	</li>
	<?php endif; ?>
</ul>


<?php
if (1 === (int)$this->item->addtitle) { ?>
<h2 class="etetable-title">
	<?php echo $this->item->name; ?>
</h2>
<?php } ?>

<?php if ('' !== $this->item->pretext) :?>
	<div class="etetable-pretext">
		<?php echo $this->item->pretext; ?>
	</div>
<?php endif; ?>

<div style="clear:both"></div>
<!-- etetable-tform -->
<form name="adminForm" id="adminForm_<?php echo $this->unique; ?>" method="post">
	<?php if ($this->item->add_option_list) :
        //$session = JFactory::getSession();
        //$corresponding_table = $session->get('corresponding_table');
    ?>
		<div class="etetable-options" style="position: absolute;top: 10px;left: 0;">
			<?php
            $corresptables = json_decode($this->item->corresptable, true);
            if (!empty($corresptables)) {
                ?>
				<select name="corresponding_table" id="corresponding_table">
					<option value=""><?php echo JText::_('COM_EVENTTABLEEDIT_CHOOSE_YOUR_OPTION'); ?></option>
					<?php
                foreach ($corresptables as $global_option => $corresptable) {
                    ?><option <?php if ($this->option_id === $corresptable) {
                        echo 'selected=selected';
                    } ?> value="<?php echo $corresptable; ?>"><?php echo $global_option; ?></option><?php
                } ?>
				</select>
				<?php
            }
            ?>
		</div>
	<?php endif;  //etetable-tform?>
	<?php
    //If there is already a table set up
    if ($this->heads) :?>
  		<input type="button" name="appointments" value="<?php echo JText::_('COM_EVENTTABLEEDIT_BOOK_BUTTON'); ?>" style="float:right;" onclick="subappointments();" class="btn btn-primary appointmentsbtn" />
		<div class="etetable-outtable">
			<?php include 'default_table.php'; ?>
		</div>
	<?php endif; ?>
	<input type="hidden" name="option" value="com_eventtableedit" />
	<input type="hidden" name="view" value="appointmentform" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="rowcolmix" id="rowcolmix" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
/**
 * Adding a new row.
 */
?>
<?php if ($this->params->get('access-add') && $this->heads) : ?>
	<!--<div id="etetable-add" title="<?php echo JText::_('COM_EVENTTABLEEDIT_NEW_ROW'); ?>"></div>
-->
<?php endif; ?>

<?php if ('' !== $this->item->aftertext) :?>
	<div class="etetable-aftertext">
		<?php echo $this->item->aftertext; ?>
	</div>
<?php endif; ?>

</div>
<div style="clear:both"></div>

<script >
jQuery(document).ready(function() {
    
  	var isMouseDown = false,
    isHighlighted;
    var array = [];
  	jQuery(document).on('mousedown', '#etetable-table_<?php echo $this->unique;?> td.tdblue', function() {
      isMouseDown = true;
      jQuery(this).toggleClass("highlighted");
      isHighlighted =jQuery(this).hasClass("highlighted");
      return false; // prevent text selection
    })
  	.on('mouseover', '#etetable-table_<?php echo $this->unique;?> td.tdblue', function () {
      if (isMouseDown) {
        jQuery(this).toggleClass("highlighted", isHighlighted);
      }
    })
  	.bind("selectstart", function () {
      return false;
    })
	jQuery(document)
    .mouseup(function () {
      isMouseDown = false;
    });
    
});

jQuery(document).ready(function(){
	jQuery("#corresponding_table").change(function(){
		var val = jQuery(this).val();
		jQuery.post( "<?php echo JURI::root(); ?>/index.php?option=com_eventtableedit&task=etetable.setSessionOption", {'corresponding_table':val} , function( data ) {
			window.location.reload();
		});
	})
})

function subappointments(){
	var array = [];
	jQuery('.highlighted').each(function(){
	  	var rowcolmixs = jQuery(this).data('id').split('row_');
		console.log(rowcolmixs);
	  	array.push(rowcolmixs[1]);
	  	
	});
	jQuery('#rowcolmix').val(array.toString());
	if(jQuery('#rowcolmix').val() !=''){
		document.adminForm.submit();
	}
}
</script>


<?php
$document = Factory::getDocument();
$style = '
#etetable-table_'.$this->unique.' {width: 100%;font-size: 12px;border-collapse: collapse;}
#etetable-table_'.$this->unique.' td {padding: 2px;}
#etetable-table_'.$this->unique.' td:hover {background-color: #F4F4F4;}
#etetable-table_'.$this->unique.' th {font-weight: bold;text-align: center;padding-left: 3px !important;padding-right: 3px !important;}
#etetable-table_'.$this->unique.' thead tr {border: none;}
#etetable-table_'.$this->unique.' th a {font-weight: bold;}
#etetable-table_'.$this->unique.' tr td {text-align: center;border: 1px solid #DDDDDD;overflow: hidden;}
#etetable-table_'.$this->unique.' div[class^="first_row"] {font-weight: bold;width: 8px;}
#etetable-table_'.$this->unique.' th a, #etetable-table_'.$this->unique.' th a:link, #etetable-table_'.$this->unique.' th a:visited {color: #444444 !important;text-decoration: none;}
#etetable-table_'.$this->unique.' tfoot td {text-align: center;background-color: #F4F4F4;font-size: 0.9em;}
#etetable-table_'.$this->unique.' #container {clear: both;text-align: center;}
#etetable-table_'.$this->unique.' .pagination-start, #etetable-table_'.$this->unique.' .pagination-prev {background: url("../images/pagination/j_button2_right.png") no-repeat scroll 100% 0 transparent;float: left;margin-left: 5px;margin-right: 10px;}
#etetable-table_'.$this->unique.' .pagination-end, #etetable-table_'.$this->unique.' .pagination-next {background: url("../images/pagination/j_button2_left.png") no-repeat scroll 0 0 transparent;float: left;margin-left: 5px;margin-right: 10px;}
#etetable-table_'.$this->unique.' .pagination-prev .pagenav, #_'.$this->unique.' .pagination-start .pagenav {padding: 0 6px 0 24px;display: block;height: 22px;line-height: 22px;}
#etetable-table_'.$this->unique.' a.pagenav {text-decoration: none;}
#etetable-table_'.$this->unique.' .pagination {/*float: left;*/padding-top: 3px;}
#etetable-table_'.$this->unique.' .pagination-prev {margin-right: 5px;}
#etetable-table_'.$this->unique.' .pagination-next .pagenav, #etetable-table_'.$this->unique.' .pagination-end .pagenav {padding: 0 24px 0 6px;text-decoration: none;display: block;height: 22px;line-height: 22px;float: left;}
#etetable-table_'.$this->unique.' .pagination-start .pagenav {background: url("../images/pagination/j_button2_first_off.png") no-repeat scroll 0 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-prev .pagenav {background: url("../images/pagination/j_button2_prev_off.png") no-repeat scroll 0 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-start a.pagenav {background: url("../images/pagination/j_button2_first.png") no-repeat scroll 0 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-prev a.pagenav {background: url("../images/pagination/j_button2_prev.png") no-repeat scroll 0 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-next .pagenav {background: url("../images/pagination/j_button2_next_off.png") no-repeat scroll 100% 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-end .pagenav {background: url("../images/pagination/j_button2_last_off.png") no-repeat scroll 100% 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-next a.pagenav {background: url("../images/pagination/j_button2_next.png") no-repeat scroll 100% 0 transparent;}
#etetable-table_'.$this->unique.' .pagination-end a.pagenav {background: url("../images/pagination/j_button2_last.png") no-repeat scroll 100% 0 transparent;}
#etetable-table_'.$this->unique.' tbody td{font-size: 9pt !important;}
#etetable-table_'.$this->unique.' tr td.highlighted {background-color: #c4c1c1;border: 1px solid #dddddd;}
';
$document->addStyleDeclaration( $style );
?>