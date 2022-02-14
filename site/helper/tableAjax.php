<?php
defined('_JEXEC') or die;
?>
<script>
var var_access_<?php echo $this->unique; ?> = null;
var var_tableProperties_<?php echo $this->unique; ?> = null;

jQuery(window).on('load',function(){
	
	var_access_<?php echo $this->unique; ?> = new Access_<?php echo $this->unique; ?>();
	var_tableProperties_<?php echo $this->unique; ?> = new TableProperties_<?php echo $this->unique; ?>();
	
	others = new Others_<?php echo $this->unique; ?>();
	lang = new Language();
	
	
	// Add the linecolors
	if (var_tableProperties_<?php echo $this->unique; ?>.nmbCells != 0) {
		BuildPopupWindow.prototype.updateAllLineColors(var_tableProperties_<?php echo $this->unique; ?>);
		initEvents_<?php echo $this->unique; ?>();
	}
})

/**
 * Add the neccessary Events to the new table 
 */ 
function initEvents_<?php echo $this->unique; ?>() {
	
	if (var_tableProperties_<?php echo $this->unique; ?>.nmbRows != 0) {
		
		for (q = 0; q < var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows.length; q++) {
			
			addClickEvent_<?php echo $this->unique; ?>(q);
			addAnchorEvent_<?php echo $this->unique; ?>(q);
		}
	
		addActionRow_<?php echo $this->unique; ?>(0, null);
		addActionRow2_<?php echo $this->unique; ?>(0, null);
	}
	
	addNewRowEvent_<?php echo $this->unique; ?>();
}

function initClickEvent_<?php echo $this->unique; ?>() {
	if (var_tableProperties_<?php echo $this->unique; ?>.nmbRows != 0) {
		
		for (q = 0; q < var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows.length; q++) {
			
			addActionEvent_<?php echo $this->unique; ?>(q);
		}
	
		
	}
}

function addActionEvent_<?php echo $this->unique; ?>(row){
	
	$('#etetable-table_<?php echo $this->unique; ?> tbody tr').each(function(index, element){
		$(this).find('#etetable-ordering').val(var_tableProperties_<?php echo $this->unique; ?>.ordering[index]);
	})
	
	$('#etetable-table_<?php echo $this->unique; ?> #rowId_' + row).find('#etetable-saveicon').bind('click',function(){
		document.adminForm.task.value = 'etetable.saveOrder';
		document.adminForm.submit();
	});
	$('#etetable-table_<?php echo $this->unique; ?> #rowId_' + row).find('#etetable-delete').bind('click',function(){
		if(var_access_<?php echo $this->unique; ?>.deleteRowR){
			
			deleteRow_<?php echo $this->unique; ?>($('#etetable-table_<?php echo $this->unique; ?> #rowId_' + row).attr('data-id'), var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows[row]);
		}
	});
}

/**
 * Add Edit Events on a single row
 */
function addClickEvent_<?php echo $this->unique; ?>(row) {
	
	// Check ACL
	//Get ID of the row
	
	var rowId = $('#etetable-table_<?php echo $this->unique; ?> #rowId_' + row).attr('data-id');
	
	if (!var_access_<?php echo $this->unique; ?>.edit && !checkAclOwnRow_<?php echo $this->unique; ?>(rowId)) return false;
	//console.log(rowId);
	var mycells = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows[row].cells;
	
	var endCell = var_tableProperties_<?php echo $this->unique; ?>.nmbCells + var_tableProperties_<?php echo $this->unique; ?>.show_first_row;
	if(endCell > 6){
		var constt = Math.round(endCell/12);
	}else if(endCell > 3 && endCell < 6){
		var constt = Math.round(endCell/6);
	}else{
		var constt = 1;	
	}
	//var constt = 2;

	var j=0;
	if(var_tableProperties_<?php echo $this->unique; ?>.show_first_row){
		var z= 1;
	}else{
		var z= 0;
	}
	
	for (a = var_tableProperties_<?php echo $this->unique; ?>.show_first_row, v = 0; a < endCell; a++, v++) {
		// Add Event
		
		$(mycells[a]).bind('click', 
			(function(rowId, v, editedCell) {
				return function () {
					openCell_<?php echo $this->unique; ?>(rowId, v, editedCell);
				}
			})(rowId, v, mycells[a])
		);
		var aa = parseInt(a);
		var dd = '';
		if(aa == 0){
			dd = ' title';
		}else{
			dd = ' tablesaw-priority-'+z;
		}
		
		// Add CSS Class that cell is editable
		$(mycells[a]).each(function(){
			$(this).addClass('editable'+dd);
		});		
		
	if(j%constt==0){
		z++;
	}
	j++;
	}
}

/**
 * Adds a action row and the neccessary events
 * if a user has the rights for that
 *
 * @param row: The row from that the action should be started
 */
function addActionRow_<?php echo $this->unique; ?>(row, singleOrdering) {
	// If the user has not engough rights
	if (!var_access_<?php echo $this->unique; ?>.reorder && !var_access_<?php echo $this->unique; ?>.ownRows) {
		if (!var_tableProperties_<?php echo $this->unique; ?>.show_pagination) {
			return false;
		}
		else if (var_tableProperties_<?php echo $this->unique; ?>.defaultSorting) {
			return false;
		}
	}
	showLoad();
	
	// Add table head for action row if it's the first time
	var ordering = new Array();
	if (singleOrdering == null) {
		ordering = addActionRowFirstTime_<?php echo $this->unique; ?>();
	}
	// If there's a new row to be added
	else {
		ordering[row] = singleOrdering; 
	}
	
	// Add the column
	var tempTable = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0];
	for(var a = row; a < tempTable.rows.length; a++ ) {
		var cell = document.createElement("td");
		cell.setAttribute("class", "editable tablesaw-priority-50 sort_col");
		cell.setAttribute("id", "etetable-action");
		cell.setAttribute("data-tablesaw-priority", 60);
		cell.setAttribute("data-tablesaw-sortable-col", "col");
		
		var elem = tempTable.rows[a].appendChild(cell);
		
		//addDeleteButton(a);
		addOrdering_<?php echo $this->unique; ?>(a, elem, ordering[a]);
	}
	removeLoad();
}

/**
 * Adds a action row and the neccessary events
 * if a user has the rights for that
 *
 * @param row: The row from that the action should be started
 */
function addActionRow2_<?php echo $this->unique; ?>(row, singleOrdering) {
	
	// If the user has not engough rights
	if (!var_access_<?php echo $this->unique; ?>.deleteRow && !var_access_<?php echo $this->unique; ?>.ownRows) {
		return false;
	}
	showLoad();
	
	// Add table head for action row if it's the first time
	//if(var_access_<?php echo $this->unique; ?>.deleteRow){	
		var ordering = new Array();
		if (singleOrdering == null) {
			ordering = addActionDeleteRowFirstTime_<?php echo $this->unique; ?>();
		}
		// If there's a new row to be added
		else {
			ordering[row] = singleOrdering; 
		}
		// Add the column
		var tempTable = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0];
		for(var a = row; a < tempTable.rows.length; a++ ) {
			var cell = document.createElement("td");
			cell.setAttribute("class", "editable tablesaw-priority-50 del_col");
			cell.setAttribute("id", "etetable-action-delete");
			cell.setAttribute("data-tablesaw-priority", 50);
			cell.setAttribute("data-tablesaw-sortable-col", "col");
			var elem = tempTable.rows[a].appendChild(cell);
			addDeleteButton_<?php echo $this->unique; ?>(a);
			//addOrdering(a, elem, ordering[a]);
		}				
	//}
	removeLoad();
}
/**
 * Executed when the whole action column has to be added the first time
 */
function addActionRowFirstTime_<?php echo $this->unique; ?>() {

	
	var thead = document.createElement("th");
	thead.innerText = lang.actions;
	thead.setAttribute("class", "evth50 tablesaw-priority-50 tablesaw-sortable-head sort_col");
	thead.setAttribute("data-tablesaw-priority", 50);
	thead.setAttribute("data-tablesaw-sortable-col", "col");
	
	
	var_tableProperties_<?php echo $this->unique; ?>.myTable.tHead.rows[0].appendChild(thead);
	
	
		var orderingLink = document.createElement("span");
		orderingLink.setAttribute("id", 'etetable-orderingLink');
		orderingLink.innerHTML = others.orderingLink;
		//orderingLink.inject(thead);
		thead.append(orderingLink);
	
		
		
	// Add order save icon if allowed
	if (var_access_<?php echo $this->unique; ?>.reorder && !var_tableProperties_<?php echo $this->unique; ?>.defaultSorting) {
		var saveIcon = document.createElement("div");
		saveIcon.setAttribute("id", "etetable-saveicon");
		saveIcon.setAttribute("class", "etetable-saveicon");
		//saveIcon.setAttribute("title", lang.saveOrder);
		saveIcon.addEventListener("click", function(){
			showLoad();
			var orderData = [];
			$("#adminForm_<?php echo $this->unique; ?> input[name='order[]']").each(function(){
				orderData.push($(this).val());
			})
			var rowIds = [];
			$("#adminForm_<?php echo $this->unique; ?> input[name='rowId[]']").each(function(){
				rowIds.push($(this).val());
			})
			
			var url = '<?php echo JURI::base(); ?>index.php?option=com_eventtableedit' +
					  '&task=etetable.ajaxSaveOrder';
			var post = "rowId=" + rowIds.join() +
						"&order=" + orderData.join() +
						"&id=" + var_tableProperties_<?php echo $this->unique; ?>.id;
			
			var request = jQuery.ajax({
				url: url,
				type: "POST",
				data: post,
			}).done(function(response) {
				removeLoad();
				if(response){
					window.location.reload();
				}
			});
		});
		thead.append(saveIcon);
	}

	return var_tableProperties_<?php echo $this->unique; ?>.ordering;
}

function addActionDeleteRowFirstTime_<?php echo $this->unique; ?>() {
	
	
	var thead2 = document.createElement("th");
	thead2.innerText = lang.deletetext;
	thead2.setAttribute("class", "evth50 tablesaw-priority-60 tablesaw-sortable-head del_col");
	thead2.setAttribute("data-tablesaw-priority", 60);
	thead2.setAttribute("data-tablesaw-sortable-col", "col");
	
	var_tableProperties_<?php echo $this->unique; ?>.myTable.tHead.rows[0].appendChild(thead2);
	return var_tableProperties_<?php echo $this->unique; ?>.ordering;
}

/**
 * Add the Delete Event on a single row
 */
function addDeleteButton_<?php echo $this->unique; ?>(row) {
	// Check ACL
	// Get ID of the row
	var rowId = $('#rowId_' + row).attr('data-id');
	
	if (!var_access_<?php echo $this->unique; ?>.deleteRow && !checkAclOwnRow_<?php echo $this->unique; ?>(rowId)) return false;

	var insertRows = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows[row];
	
	spanclass = "";
	if(!var_access_<?php echo $this->unique; ?>.deleteRowR){
		spanclass = "disabled";
	}
	
	var span = document.createElement("span");
	span.setAttribute("class", spanclass);
	span.setAttribute("id", "etetable-delete");
	span.addEventListener('click', function(){
		if(var_access_<?php echo $this->unique; ?>.deleteRowR){
			//console.log(rowId);
			deleteRow_<?php echo $this->unique; ?>(rowId, insertRows);
		}
	})
	
	
	
	var img = document.createElement("img");
	img.setAttribute("src", others.rootUrl + 'components/com_eventtableedit/template/images/cross.png');
	img.setAttribute("id", "etetable-delete-img");
	img.setAttribute("alt", "cross");
	img.setAttribute("title", lang.deleteRow);
	
	var insertCell = insertRows.cells[insertRows.cells.length - 1];
	
	if(!var_access_<?php echo $this->unique; ?>.deleteRowR){
		$(insertCell).addClass("disabled")
	}
	
	
	var isStack = (jQuery('#change_mode').val() == 'stack') ? true : false;
	
	if(isStack){
		var tempTable = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0];
		var labelHtml = $(tempTable.rows[0]).find('.del_col').find('.tablesaw-cell-label').html()

		var chtml = '<b class="tablesaw-cell-label">'+labelHtml+'</b><span class="tablesaw-cell-content"></span>';
		
		img.inject(span);
		
		$(insertCell).html(chtml);
		$(insertCell).find('.tablesaw-cell-content').html(span);
		
		$(insertCell).find('.tablesaw-cell-content').find('#etetable-delete').bind('click',function(){
			if(var_access_<?php echo $this->unique; ?>.deleteRowR){
				deleteRow_<?php echo $this->unique; ?>($('#rowId_' + row).attr('data-id'), var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows[row]);
			}
		});
	}else{
		span.append(img)
		insertCell.append(span)
		
	}
	
}
 
/**
 * Add the Ordering Input fields
 */
function addOrdering_<?php echo $this->unique; ?>(row, cell, ordering) {
	/** 
	 * Check ACL (edit rights are used for ordering)
	 * Check if ordering fields should be there, this is if
	 * there's no automatic ordering
	 */
	//console.log(others.listOrder);
	if ((!var_access_<?php echo $this->unique; ?>.reorder || var_tableProperties_<?php echo $this->unique; ?>.defaultSorting) && others.listOrder != 'a.ordering') return false;
	
	
	//Get ID of the row
	var rowId = $('#etetable-table_<?php echo $this->unique; ?> #rowId_' + row).attr('data-id');
	
	var disabled = true;
	if (var_access_<?php echo $this->unique; ?>.reorder && others.listOrder == 'a.ordering') {
		disabled = false;
	}
	
	var isStack = (jQuery('#change_mode').val() == 'stack') ? true : false;
	
	if(isStack){
		var tempTable = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0];
		var labelHtml = $(tempTable.rows[0]).find('.sort_col').find('.tablesaw-cell-label').html()

		var chtml = '<b class="tablesaw-cell-label">'+labelHtml+'</b><span class="tablesaw-cell-content"></span>';
		
		var orderInput = document.createElement("input");
		orderInput.setAttribute("type", "text");
		orderInput.setAttribute("id", "etetable-ordering");
		orderInput.setAttribute("name", "order[]");
		orderInput.setAttribute("value", ordering);
		if(disabled){
			orderInput.setAttribute("disabled", "disabled");
		}
		var hiddenInput = document.createElement("input");
		hiddenInput.setAttribute("type", "hidden");
		hiddenInput.setAttribute("id", "etetable-hidden");
		hiddenInput.setAttribute("name", "rowId[]");
		hiddenInput.setAttribute("value", rowId);
		
		
		$(cell).html(chtml);
		$(cell).find('.tablesaw-cell-content').append(orderInput);
		$(cell).find('.tablesaw-cell-content').append(hiddenInput);
		$(cell).find('#etetable-ordering').val(ordering);
	}else{
		
		var orderInput = document.createElement("input");
		orderInput.setAttribute("type", 'text');
		orderInput.setAttribute("id", 'etetable-ordering');
		orderInput.setAttribute("name", 'order[]');
		orderInput.setAttribute("value", ordering);
		if(disabled){
			orderInput.setAttribute("disabled", disabled);
		}
		
		
		
		var hiddenInput = document.createElement("input");
		hiddenInput.setAttribute("type", 'hidden');
		hiddenInput.setAttribute("id", 'etetable-ordering');
		hiddenInput.setAttribute("name", 'rowId[]');
		hiddenInput.setAttribute("value", rowId);
		
		cell.append(orderInput);
		cell.append(hiddenInput);
	}
	
}

//Ads the click Event to the new row button
function addNewRowEvent_<?php echo $this->unique; ?>() {
	// Check ACL
	if (!var_access_<?php echo $this->unique; ?>.add) return;
	
	$('#adminForm_<?php echo $this->unique; ?> .etetable-add').bind('click', function() {
		newRow_<?php echo $this->unique; ?>();
	});

	
}

/**
 * Open a window to edit a cell
 */
function openCell_<?php echo $this->unique; ?>(rowId, cell, editedCell) {
	//Check that only one instance of the window is opened
	if (!others.doOpen()) return;
	showLoad();
	
	var url = 'index.php?option=com_eventtableedit' +
			  '&task=etetable.ajaxGetCell' +
			  '&id=' + var_tableProperties_<?php echo $this->unique; ?>.id +
			  '&cell=' + cell +
			  '&rowId=' + rowId;
	
	var request = jQuery.ajax({
		url: url,
		type: "POST",
	}).done(function(response) {
		var parsed = response.split('|');			
		var cellContent = parsed[0];
		var datatype	= parsed[1];	
		var popup = new BuildPopupWindow(datatype, rowId);		
		if (datatype != "boolean" && datatype != "four_state") {
			popup.constructNormalPopup(cellContent, cell, editedCell, var_tableProperties_<?php echo $this->unique; ?>);
		} else {
			popup.constructBoolean(cellContent, cell, editedCell, datatype, var_tableProperties_<?php echo $this->unique; ?>);
		}		
		removeLoad();
	});
}

/**
 * Show the AJAX-Loading Symbol
 */
function showLoad() {
	var loadDiv = '<div id="loadDiv"><img src="'+others.rootUrl+'/components/com_eventtableedit/template/images/ajax-loader.gif"/></div>';	
	$('body').append(loadDiv);
}

function removeLoad() {
	$('#loadDiv').remove();
}

/**
 * Deletes a row
 */
function deleteRow_<?php echo $this->unique; ?>(rowId, rowIdentifier) {
	
	if (!others.doOpen()) return false;
	showLoad();
	
	// Build the popup
	var popup = new BuildPopupWindow("delete", rowId);
	popup.constructDeletePopup(rowIdentifier, var_tableProperties_<?php echo $this->unique; ?>);
	
	removeLoad();
}

function newRow_<?php echo $this->unique; ?>() {
	if (!others.doOpen()) return false;
	showLoad();
	
	var myUrl = 'index.php?option=com_eventtableedit' +
				'&task=etetable.ajaxNewRow' +
				'&id=' + var_tableProperties_<?php echo $this->unique; ?>.id;	
	
	var request = jQuery.ajax({
		url: myUrl,
		type: "POST",
	}).done(function(response) {
		var parsed = response.split('|');				
		var rowId 		= parsed[0];
		var rowOrder	= parsed[1];
		var nmbPageRows	= parseInt(var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows.length);
		
		addCells_<?php echo $this->unique; ?>(nmbPageRows, rowId);
		
		var_access_<?php echo $this->unique; ?>.createdRows.push(rowId);  
		
		addActionRow_<?php echo $this->unique; ?>(nmbPageRows, rowOrder);
		addActionRow2_<?php echo $this->unique; ?>(nmbPageRows, rowOrder);
		addClickEvent_<?php echo $this->unique; ?>(nmbPageRows);
		
		removeLoad();
		others.doClose();
		
		var isSwipe = (jQuery('#adminForm_<?php echo $this->unique; ?> #change_mode').val() == 'swipe') ? true : false;
		
		setTimeout(function() {
			if(isSwipe){
				$('#adminForm_<?php echo $this->unique; ?> select#change_mode').val('swipe');
				$('#adminForm_<?php echo $this->unique; ?> select#change_mode').trigger('change');
			}
			$('tr#rowId_' + nmbPageRows).css('display', 'table-row');
		}, 10);
		<?php if ($this->item->show_pagination) { ?>
		jQuery("#etetable-table_<?php echo $this->unique; ?> tbody").attr("data-firstRecord", Math.floor(jQuery("#etetable-table_<?php echo $this->unique; ?> tbody tr").length/<?php echo $this->item->pagebreak; ?>)*<?php echo $this->item->pagebreak; ?>);		
		paginate_<?php echo $this->unique; ?>(Math.floor(jQuery("#etetable-table_<?php echo $this->unique; ?> tbody tr").length/<?php echo $this->item->pagebreak; ?>)*<?php echo $this->item->pagebreak; ?>, '<?php echo $this->item->pagebreak; ?>', "#etetable-table_<?php echo $this->unique; ?>", "#etetable-table_<?php echo $this->unique; ?> tbody tr");
		<?php } ?>
	});
}

function addCells_<?php echo $this->unique; ?>(nmbPageRows, rowId) {
	// Insert Row at the end and define linecolor
	var tempTable = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0];
	var tr = document.createElement('tr');
	tr.setAttribute('id', 'rowId_' + nmbPageRows);
	tr.setAttribute('data-id', rowId);
	tr.style.display = 'none';
	tempTable.appendChild(tr);
	var_tableProperties_<?php echo $this->unique; ?>.nmbRows++;
	tempTable.rows[nmbPageRows].className = 'etetable-linecolor' + (nmbPageRows % 2);		
	
	var isStack = (jQuery('#adminForm_<?php echo $this->unique; ?> #change_mode').val() == 'stack') ? true : false;
	
	
	// Insert Cells
	var totNmbCells = var_tableProperties_<?php echo $this->unique; ?>.nmbCells + var_tableProperties_<?php echo $this->unique; ?>.show_first_row 
	
	for (a = 0; a < totNmbCells; a++) {
		tempTable.rows[nmbPageRows].insertCell(-1);
	}
	
	
	// Optional first row
	if (var_tableProperties_<?php echo $this->unique; ?>.show_first_row) {
		var firstRow = tempTable.rows[nmbPageRows].cells[0];
		
		firstRow.setAttribute('class', 'first_row' + nmbPageRows, true);
		firstRow.setAttribute('id', 'first_row', true);
		
		/**
		 * The searched number is just the number from the cell above + 1
		 * If it is the first row use list start
		 */
		if (nmbPageRows > 0) {
			if(isStack){
				var frow = $(tempTable.rows[nmbPageRows - 1].cells[0]).find('.tablesaw-cell-content').html();
				frow = frow.trim();
				frow = '<b class="tablesaw-cell-label">#</b><span class="tablesaw-cell-content">' + ( parseInt(frow) + 1 ) + '</span>';
			}else{
				var frow = parseInt(tempTable.rows[nmbPageRows - 1].cells[0].innerHTML) + 1;
			}
			firstRow.innerHTML = frow;
		} else {
			firstRow.innerHTML = var_tableProperties_<?php echo $this->unique; ?>.limitstart + 1;
		}
	}
	
	// Normal cells
	for (a = var_tableProperties_<?php echo $this->unique; ?>.show_first_row, b = 0; a < totNmbCells; a++, b++) {
		var cell = tempTable.rows[nmbPageRows].cells[a];
		cell.setAttribute('id', 'etetable-row_' + rowId + '_' + b);
		cell.setAttribute('class', 'etetable-row_' + nmbPageRows + '_' + b);
		if(isStack){
			var preCell = $(tempTable.rows[nmbPageRows - 1].cells[a]).find('.tablesaw-cell-label').html();
			var chtml = '<b class="tablesaw-cell-label">'+preCell+'</b><span class="tablesaw-cell-content">&nbsp;</span>';
		}else{
			var chtml = '&nbsp;';
		}
		
		cell.innerHTML = chtml;	
		//console.log(a + " - " + b);		
	}
	
	
}

/**
 * Checks, if a user has the right to edit or delete a row
 * that he created himself
 */
function checkAclOwnRow_<?php echo $this->unique; ?>(rowId) {

	if (var_access_<?php echo $this->unique; ?>.ownRows && var_access_<?php echo $this->unique; ?>.createdRows.indexOf(rowId) != -1) {
		return true;
	}
	return false;
}

/**
 * Add an event to every anchor element
 * in order to stop event bubbling, and if
 * someone clicks on a link not the popup window opens.
 */
function addAnchorEvent_<?php echo $this->unique; ?>(row, cell) {
	if (row != null) {
		var mycells = var_tableProperties_<?php echo $this->unique; ?>.myTable.tBodies[0].rows[row].cells;
		var endCell = var_tableProperties_<?php echo $this->unique; ?>.nmbCells + var_tableProperties_<?php echo $this->unique; ?>.show_first_row;
		for (var a = var_tableProperties_<?php echo $this->unique; ?>.show_first_row, v = 0; a < endCell; a++, v++) {
			addAnchorEventsExe_<?php echo $this->unique; ?>(mycells[a]);
		}
	}
	else {
		addAnchorEventsExe_<?php echo $this->unique; ?>(cell);
	}
}

function addAnchorEventsExe_<?php echo $this->unique; ?>(elem) {
	
	if($(elem).find('a')){
		
		$(elem).find('a').each(function(){
			$($(this)[0]).bind('click', function(event) {
				event.stopPropagation();
			});
		});
	}
}

function searchReplace_<?php echo $this->unique; ?>(){
	var filterstring = jQuery('.filterstring_<?php echo $this->unique; ?>').val();
	var replacestring = jQuery('.replacestring_<?php echo $this->unique; ?>').val();
	if(filterstring!="" && replacestring!=""){
		jQuery('#popup_confirm_<?php echo $this->unique; ?>').show();
	}
}
jQuery(document).ready(function(){
	jQuery('#confirm_no_<?php echo $this->unique; ?>').on('click',function(){
		jQuery('#popup_confirm_<?php echo $this->unique; ?>').hide();
	})

	jQuery('#confirm_yes_<?php echo $this->unique; ?>').on('click',function(){
		jQuery('#popup_confirm_<?php echo $this->unique; ?>').hide();
		doReplace_<?php echo $this->unique; ?>();
	})
})
function doReplace_<?php echo $this->unique; ?>(){
	var filterstring = jQuery('.filterstring_<?php echo $this->unique; ?>').val();
	var replacestring = jQuery('.replacestring_<?php echo $this->unique; ?>').val();
	showLoad();
	var url = '<?php echo JURI::base(); ?>index.php?option=com_eventtableedit' +
					  '&task=etetable.ajaxReplaceRows';
	var post = "filterstring=" + filterstring +
				"&replacestring=" + replacestring +
				"&tableId=" + var_tableProperties_<?php echo $this->unique; ?>.id;				
	
	var request = jQuery.ajax({
		url: url,
		type: "POST",
		data: post,
	}).done(function(response) {
		jQuery('#etetable-table_<?php echo $this->unique; ?> td:contains("'+filterstring+'")').each(function(){
			var html = jQuery(this).html();
			var newHtml = html.replaceAll(filterstring, replacestring);
			jQuery(this).html(newHtml);
			jQuery('.filterstring_<?php echo $this->unique; ?>').val('');
			jQuery('.replacestring_<?php echo $this->unique; ?>').val('');
		});
		
		removeLoad();
		window.location.reload();
	});
}
</script>
