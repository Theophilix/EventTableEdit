<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;


// no direct access
defined('_JEXEC') or die;


$main = Factory::getApplication()->input;
$Itemid = $main->getInt('Itemid', '');

if (1 === (int)$this->item->sorting) {
    ?>
<script>
		/*
 * Natural Sort algorithm for Javascript - Version 0.8.1 - Released under MIT license
 * Author: Jim Palmer (based on chunking idea from Dave Koelle)
 */
function naturalSort (a, b) {
			    var re = /(^([+\-]?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?(?=\D|\s|$))|^0x[\da-fA-F]+$|\d+)/g,
			        sre = /^\s+|\s+$/g,   // trim pre-post whitespace
			        snre = /\s+/g,        // normalize all whitespace to single ' ' character
			        dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
			        hre = /^0x[0-9a-f]+$/i,
			        ore = /^0/,
			        i = function(s) {
			            return (naturalSort.insensitive && ('' + s).toLowerCase() || '' + s).replace(sre, '');
			        },
			        // convert all to strings strip whitespace
			        x = i(a),
			        y = i(b),
			        // chunk/tokenize
			        xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
			        yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
			        // numeric, hex or date detection
			        xD = parseInt(x.match(hre), 16) || (xN.length !== 1 && Date.parse(x)),
			        yD = parseInt(y.match(hre), 16) || xD && y.match(dre) && Date.parse(y) || null,
			        normChunk = function(s, l) {
			            // normalize spaces; find floats not starting with '0', string or 0 if not defined (Clint Priest)
			            return (!s.match(ore) || l == 1) && parseFloat(s) || s.replace(snre, ' ').replace(sre, '') || 0;
			        },
			        oFxNcL, oFyNcL;
			    // first try and sort Hex codes or Dates
			    if (yD) {
			        if (xD < yD) { return -1; }
			        else if (xD > yD) { return 1; }
			    }
			    // natural sorting through split numeric strings and default strings
			    for(var cLoc = 0, xNl = xN.length, yNl = yN.length, numS = Math.max(xNl, yNl); cLoc < numS; cLoc++) {
			        oFxNcL = normChunk(xN[cLoc] || '', xNl);
			        oFyNcL = normChunk(yN[cLoc] || '', yNl);
			        // handle numeric vs string comparison - number < string - (Kyle Adams)
			        if (isNaN(oFxNcL) !== isNaN(oFyNcL)) {
			            return isNaN(oFxNcL) ? 1 : -1;
			        }
			        // if unicode use locale comparison
			        if (/[^\x00-\x80]/.test(oFxNcL + oFyNcL) && oFxNcL.localeCompare) {
			            var comp = oFxNcL.localeCompare(oFyNcL);
			            return comp / Math.abs(comp);
			        }
			        if (oFxNcL < oFyNcL) { return -1; }
			        else if (oFxNcL > oFyNcL) { return 1; }
			    }
			}
			    
    
	</script>
<?php 

	$document = Factory::getDocument();
	if ($this->item->show_pagination) { 
		$style = '//#etetable-table_'.$this->unique.'{display: none;}';
		$document->addStyleDeclaration( $style );
	} 
	
	$style = 'span.filter-head{display: none;}
div.etetable-button{display: none;}';
	$document->addStyleDeclaration( $style );
	?>

</style>
<?php

foreach ($this->heads as $headSort) {
    $sortcalssscript = '';
    if ('text' === $headSort->datatype) {
        $sortcalssscript = 'custom-sort'.$headSort->id; ?>
		<script >
			jQuery(function() {
				jQuery( "#<?php echo $sortcalssscript; ?>" ).data( "tablesaw-sort", function( ascending ) {
					return  function( a, b ) {
						// a.cell
						// a.element
						// a.rowNum
						
						var a = a.cell;
						var b = b.cell;
						//console.log(a);
						var re = /(^([+\-]?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?(?=\D|\s|$))|^0x[\da-fA-F]+$|\d+)/g,
					        sre = /^\s+|\s+$/g,   // trim pre-post whitespace
					        snre = /\s+/g,        // normalize all whitespace to single ' ' character
					        dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
					        hre = /^0x[0-9a-f]+$/i,
					        ore = /^0/,
					        i = function(s) {
					            return (naturalSort.insensitive && ('' + s).toLowerCase() || '' + s).replace(sre, '');
					        },
					        x = i(a),
					        y = i(b),
					        xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
					        yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
					        xD = parseInt(x.match(hre), 16) || (xN.length !== 1 && Date.parse(x)),
					        yD = parseInt(y.match(hre), 16) || xD && y.match(dre) && Date.parse(y) || null,
					        normChunk = function(s, l) {
					            return (!s.match(ore) || l == 1) && parseFloat(s) || s.replace(snre, ' ').replace(sre, '') || 0;
					        },
					        oFxNcL, oFyNcL;
					    if (yD) {
					        if (xD < yD) { 
					        	return -1;
					        }
					        else if (xD > yD) { 
					        	return 1;
					        }
					    }
					    for(var cLoc = 0, xNl = xN.length, yNl = yN.length, numS = Math.max(xNl, yNl); cLoc < numS; cLoc++) {
					        oFxNcL = normChunk(xN[cLoc] || '', xNl);
					        oFyNcL = normChunk(yN[cLoc] || '', yNl);
					        if (isNaN(oFxNcL) !== isNaN(oFyNcL)) {
					            return isNaN(oFxNcL) ? 1 : -1;
					        }
					        if (/[^\x00-\x80]/.test(oFxNcL + oFyNcL) && oFxNcL.localeCompare) {
					            var comp = oFxNcL.localeCompare(oFyNcL);
					            return comp / Math.abs(comp);
					        }
					        if (oFxNcL < oFyNcL) { 
					        	if( ascending ) {
					        	return -1;
					        	}else{
					        	return 1;	
					        	}
					        }
					        else if (oFxNcL > oFyNcL) {
					        	if( ascending ) {
					        	return 1;
					        	}else{
					        	return -1;
					        	}
					        	
					        }
					    }
					    


					};
				});
			});

		</script>

<?php
    }
} ?>

<?php
} ?>
<div class="eventtableedit<?php echo $this->params->get('pageclass_sfx'); ?>" id="<?php echo $this->unique; ?>">

<ul class="actions">
	<?php 
	if ($this->item->show_print_view) :?>
	<li class="print-icon">
		<?php if (!$this->print) : ?>
			<?php
			$url = 'index.php?option=com_eventtableedit&id='.$this->item->slug . '&tmpl=component&print=1&view=etetable&layout=print&limit=0&limitstart=0&filterstring='.$this->params->get('filterstring').'&Itemid=' . $Itemid;
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			
			$text = JHTML::_('image', JURI::base().'/components/com_eventtableedit/template/images/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);

			$attribs['title'] = JText::_('JGLOBAL_PRINT');
			$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
			$attribs['rel'] = 'nofollow';

			echo JHTML::_('link', Route::_($url), $text, $attribs);
			?>
		<?php else : ?>
			<?php 			
			$text = JHTML::_('image', JURI::base().'/components/com_eventtableedit/template/images/printButton.png', JText::_('JGLOBAL_PRINT'), null, true);
			echo '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
			?>
		<?php endif; ?>
	</li>
	<?php endif; ?>

	<?php if ($this->params->get('access-create_admin') && !$this->print) :?>
	<li class="admin-icon">
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
			echo JHTML::_('link', Route::_($url), $button, $attribs); ?>
		<?php endif; ?>
	</li>
	<?php endif;  ?>
	<?php if ($this->params->get('access-csv') && !$this->print) :?>
	<li class="admin-icon">
		<a href="<?php echo Route::_('index.php?option=com_eventtableedit&view=csvexport&id=' . $this->item->id .'&Itemid=' . $Itemid . '&return=' . base64_encode(JUri::getInstance()))?>" title="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_EXPORT')?>">
			<img src="components/com_eventtableedit/template/images/csv-download.png" alt="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_EXPORT')?>"/>
		</a>
	</li>
	<li class="admin-icon">
		<a href="<?php echo Route::_('index.php?option=com_eventtableedit&view=csvimport&id=' . $this->item->id .'&Itemid=' . $Itemid . '&return=' . base64_encode(JUri::getInstance()))?>" title="<?php echo JText::_('COM_EVENTTABLEEDIT_ETETABLE_IMPORT')?>">
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

<?php if ($this->item->show_filter && count($this->heads) > 0) :?>
	<div class="etetable-filter">
		<?php include 'default_filter.php'; ?>
	</div>
<?php endif;  //etetable-tform?>
<div style="clear:both"></div>
<!-- etetable-tform -->
<form action="<?php echo JUri::getInstance();?>" name="adminForm" id="adminForm_<?php echo $this->unique; ?>" method="post">
	<?php 

    //If there is already a table set up
    if ($this->heads) :?>
  
		<div class="etetable-outtable">
			<?php include 'default_table.php'; ?>
		</div>
	<?php endif; ?>
	
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get($this->item->id.'.list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get($this->item->id.'.list.direction'); ?>" />
	<input type="hidden" name="filterstring" value="<?php echo $this->params->get($this->item->id.'.filterstring'); ?>" />
	<!--<input type="hidden" name="option" value="com_eventtableedit" />
	<input type="hidden" name="view" value="etetable" />
	<input type="hidden" name="task" value="" />-->
	<input type="hidden" name="task" class="task" value="" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="table_id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="<?php echo $this->item->alias; ?>change_mode" id="cmode" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<?php
    /**
     * Adding a new row.
     */
    ?>
	<?php if ($this->params->get('access-add') && $this->heads) : ?>
		<div class="etetable-add" title="<?php echo JText::_('COM_EVENTTABLEEDIT_NEW_ROW'); ?>"></div>

	<?php endif; ?>
</form>



<?php if ('' !== $this->item->aftertext) :?>
	<div class="etetable-aftertext">
		<?php echo $this->item->aftertext; ?>
	</div>
<?php endif; ?>

</div>
<div style="clear:both"></div>
<?php
if ($this->item->scroll_table) {
        $scroll_table_height = ($this->item->scroll_table_height) ? $this->item->scroll_table_height.'px' : '200px';

	$document = Factory::getDocument();
		$style = '
				div.scroller {
				  display: inline-block;
				  height: '. $scroll_table_height.';
				  overflow: auto;
				  float:left;
				  width:100%;
				  border-top: solid 1px #e5e5e4;
				}

				table#etetable-table_'.$this->unique.' th {
				  position: -webkit-sticky;
				  position: sticky;
				  top: -1px;
				  height: 34px;
				}

				#etetable-table_'. $this->unique .' thead th{
					border: 1px solid #e5e5e4;
					background: #e2dfdc;
					background-image: -webkit-linear-gradient(top, #fff, #e2dfdc);
					background-image: linear-gradient(to bottom, #fff, #e2dfdc);
				}
				';
		$document->addStyleDeclaration( $style );
    }else{
		$document = Factory::getDocument();
		$style = '
				div.scroller {
				  display: inline-block;
				  height: auto;
				  overflow: auto;
				  float:left;
				  width:100%;
				  border-top: solid 1px #e5e5e4;
				}
				';
		$document->addStyleDeclaration( $style );
	}?>
<?php if ($this->item->show_pagination) { ?>
	<div>
		<a href="#" class="paginate_<?php echo $this->unique; ?>" id="previous_<?php echo $this->unique; ?>">&laquo;</a> <a href="#" class="paginate_<?php echo $this->unique; ?>" id="next_<?php echo $this->unique; ?>">&raquo;</a>
	</div>
<?php } ?>
<?php 
$document = Factory::getDocument();
$style = '
.paginate_'. $this->unique.'{
	width: 30px;
	height: 20px;
	text-align: center;
	border: solid 1px #ccc;
	margin: 10px 5px 0 0;
	float: left;
	color: #000;
	text-decoration: none;
	line-height: 14px;
}
.paginate_'.$this->unique.':hover, .paginate_'.$this->unique.':active, .paginate_'.$this->unique.':focus{
	text-decoration: none;
}';
$document->addStyleDeclaration( $style );
 ?>
<script>
 

 
    // Starting table state
    function initTable_<?php echo $this->unique; ?>(size, myTable) {
		
		var myTableBody = myTable + " tbody";
		var myTableRows = myTableBody + " tr";
		var myTableColumn = myTable + " th";
		if(size == ""){
			size = 4;
		}
        jQuery(myTableBody).attr("data-pageSize", size);
        jQuery(myTableBody).attr("data-firstRecord", 0);
        jQuery('#previous_<?php echo $this->unique; ?>').hide();
        jQuery('#next_<?php echo $this->unique; ?>').show();
 
        // Increment the table width for sort icon support
    
 
        // Start the pagination
        paginate_<?php echo $this->unique; ?>(parseInt(jQuery(myTableBody).attr("data-firstRecord"), 10),
                 parseInt(jQuery(myTableBody).attr("data-pageSize"), 10), myTable, myTableRows);
		
		
		
		// Heading click
		jQuery(myTableColumn).click(function () {
			
	 
			// Start the pagination
			paginate_<?php echo $this->unique; ?>(parseInt(jQuery(myTableBody).attr("data-firstRecord"), 10),
					 parseInt(jQuery(myTableBody).attr("data-pageSize"), 10), myTable, myTableRows);
		});
	 
		// Pager click
		jQuery("a.paginate_<?php echo $this->unique; ?>").click(function (e) {
			e.preventDefault();
			var tableRows = jQuery(myTableRows);
			
			var tmpRec = parseInt(jQuery(myTableBody).attr("data-firstRecord"), 10);
			var pageSize = parseInt(jQuery(myTableBody).attr("data-pageSize"), 10);
			
			// Define the new first record
			if (jQuery(this).attr("id") == "next_<?php echo $this->unique; ?>") {
				tmpRec += pageSize;
			} else {
				tmpRec -= pageSize;
			}
			
			// The first record is < of 0 or > of total rows
			if (tmpRec < 0 || tmpRec > tableRows.length) return
			
			jQuery(myTableBody).attr("data-firstRecord", tmpRec);
			paginate_<?php echo $this->unique; ?>(tmpRec, pageSize, myTable, myTableRows);
		});
	 
		
    }
	
	// Paging function
	var paginate_<?php echo $this->unique; ?> = function (start, size, myTable, myTableRows) {
		var tableRows = jQuery(myTableRows).not('.musthide');
		var end = start + size;
		// Hide all the rows
		tableRows.hide();
		
		// Show a reduced set of rows using a range of indices.
		tableRows.slice(start, end).show();
		jQuery(myTable).show();
		// Show the pager
		jQuery(".paginate_<?php echo $this->unique; ?>").show();
		
		jQuery('.paginate_<?php echo $this->unique; ?>').removeAttr('disabled');
		// If the first row is visible hide prev
		if (tableRows.eq(0).is(":visible")) jQuery('#previous_<?php echo $this->unique; ?>').hide();
		// If the last row is visible hide next 
		if (tableRows.eq(tableRows.length - 1).is(":visible")) jQuery('#next_<?php echo $this->unique; ?>').hide();
	}
 
    
	
	
jQuery(function () { 
   <?php if ($this->item->show_pagination) { ?>
    initTable_<?php echo $this->unique; ?>('<?php echo $this->item->pagebreak; ?>', "#etetable-table_<?php echo $this->unique; ?>");
   <?php } ?> 
});
</script>

<script>
var $rows_<?php echo $this->unique; ?> = jQuery('#etetable-table_<?php echo $this->unique; ?> tbody tr');
jQuery(document).ready(function() {
	jQuery('.filterstring_<?php echo $this->unique; ?>').keyup(function() {
		var val = jQuery.trim(jQuery(this).val()).replace(/ +/g, ' ').toLowerCase();
		$rows_<?php echo $this->unique; ?>.show().removeClass('musthide').filter(function() {
			var text = jQuery(this).find('td:visible').text().replace(/\s+/g, ' ').toLowerCase();
			//console.log(jQuery(this).find('td:visible').text());
			return !~text.indexOf(val);
		}).hide().addClass('musthide');
		//initTable_<?php echo $this->unique; ?>(5);
		<?php if ($this->item->show_pagination) { ?>
		initTable_<?php echo $this->unique; ?>('<?php echo $this->item->pagebreak; ?>',"#etetable-table_<?php echo $this->unique; ?>");
	   <?php } ?> 
	});
});
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
#etetable-table_'.$this->unique.' tbody td{font-size: 9pt !important;}';
$document->addStyleDeclaration( $style );
?>