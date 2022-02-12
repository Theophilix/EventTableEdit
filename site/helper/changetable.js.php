<?php
/**
 * $Id: $.
 *
 * @copyright (C) 2007 - 2020 Manuel Kaspar and Theophilix
 * @license GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;
?>

<script >
<!--

theads = null;

jQuery(window).on('load',function(){
	
	theads = new Theads();
	theads.loadTheads();
	
	addNewEvent();
	addSaveEvent();
	addCancelEvent();
});

function Thead(id, name, datatype) {
	// A reference to the row
	this.row = null;
	this.rowNumber = null;

	this.id = id;
	this.name = name;
	this.datatype = datatype;
	
	// Contains defaultSorting['number'] and defaultSorting['direction']
	this.defaultSorting = new Array();
	
}

function Theads() {
	this.table = document.getElementById("changetable-table").tBodies[0];
	
	this.theads = new Array();
	
	// Counts how many default sortings are added
	this.sortingNumber = 0;
}

function addNewEvent() {
	jQuery('#changetable-newrow').on('click', function() {
		var thead = new Thead(0, '', 'text');
		theads.theads.push(thead);
		thead.prepareDefaultSorting('');
		theads.addRows(theads.theads.length - 1, -1);
		
		// Update ordering of last row if exists
		if(theads.theads.length > 1) {
			var lastRow = theads.theads[theads.theads.length - 2];
			var cell = lastRow.row.cells[2];
			lastRow.updateCell(cell, lastRow.addOrdering());
		}
			
		jQuery(thead.row.cells[0].firstChild).trigger('click');
	});
}

function addSaveEvent() {
	jQuery('#changetable-save').on('click', function() {
		theads.serializeToForm();
		jQuery('#adminForm').submit(); 
	});
}

function addCancelEvent() {
	jQuery('#changetable-cancel').on('click', function() {
		window.history.back();
	});
}

/**
 * Loads the available table heads
 */
Theads.prototype.loadTheads = function() {
	this.generateObjects();
	this.addRows(0, -1);
}

/**
 * Load the thead data from the php variables to javascript objects
 */
Theads.prototype.generateObjects = function() {
	var thead = null;
	
	<?php foreach ($this->item as $item) : ?>
		thead = new Thead(<?php echo $item->id.", '".$item->name."', '".$item->datatype."'"; ?>);
		thead.prepareDefaultSorting('<?php echo $item->defaultSorting; ?>');
		this.theads.push(thead);
	<?php endforeach; ?>
}

/**
 * Prepare DefaultSorting (table_id:asc or desc, 2nd sorting)
 */
Thead.prototype.prepareDefaultSorting = function(data) {
	// If no sorting is set for the row
	if (data == '' || data == ':') {
		this.defaultSorting['number'] = '';
		this.defaultSorting['direction'] = '';
		return;
	}
	
	var sp = data.split(':');
	this.defaultSorting['number'] = sp[0];
	this.defaultSorting['direction'] = sp[1];
	theads.sortingNumber++;
}

/**
 * Adds the rows in the html table
 * If endRow is -1 all rows are added
 */
Theads.prototype.addRows = function(startRow, endRow) {
	// If used for reordering
	if (endRow == -1) {
		endRow = this.theads.length;
	}

	for (var a = startRow; a < endRow; a++) {
		var row = this.table.insertRow(a);
		this.theads[a].row = row;
		this.theads[a].rowNumber = a;
		
		this.theads[a].addCell(this.theads[a].addName());
		this.theads[a].addCell(this.theads[a].addDatatype());
		this.theads[a].addCell(this.theads[a].addOrdering());
		//this.theads[a].addCell(this.theads[a].addDefaultSorting());
		this.theads[a].addCell(this.theads[a].addDeleteIcon());
	}
}

/**
 * For adding all the new cells
 */
Thead.prototype.addCell = function(func) {	
	var cell = this.row.insertCell(-1);
	cell.append(func);
	//func.inject(cell);
}

/**
 * For updating a cell
 */
Thead.prototype.updateCell = function(cell, func) {
	// Delete content if something there
	while (cell.childNodes.length >= 1) {
        cell.removeChild(cell.firstChild);       
    } 
	cell.append(func);
	/* func.inject(cell); */
}

/**
 * Adds the name cell
 */
Thead.prototype.addName = function() {
	
	var thistHead = this;
	var span = document.createElement("span");
	span.setAttribute("id", "changetable-name");
	span.innerText = this.name;
	
	var editImg = document.createElement("img");
	editImg.setAttribute("id", 'EditImg');
	editImg.setAttribute("src", '<?php echo JURI::root(); ?>components/com_eventtableedit/template/images/edit.png');
	
	editImg.addEventListener("click", function(){		
		thistHead.editName();		
	}, thistHead)
	
	
	span.append(editImg);
	
	//editImg.inject(span);
	
	return span;
}

/**
 * Adds the datatype cell
 */
Thead.prototype.addDatatype = function() {
	var thistHead = this;
	var select = document.createElement("select");
	select.setAttribute("id", "changetable-datatype");
	select.addEventListener("click", function(){
		var sel = thistHead.row.cells[1].firstChild;
		thistHead.datatype = sel.options[sel.selectedIndex].value;		
	})
	
	
	var option = null;
	var selextedIndex = 0;
	
	<?php
    for ($a = 0; $a < count($this->additional['datatypes']); ++$a) :?>
		
		option = document.createElement("option");
		option.setAttribute("value", "<?php echo $this->additional['datatypes'][$a]; ?>");
		option.innerText = "<?php echo $this->additional['datatypes_desc'][$a]; ?>";
		select.append(option);
		//option.inject(select);	
		
		if ('<?php echo $this->additional['datatypes'][$a]; ?>' == this.datatype) {
			selectedIndex = <?php echo $a; ?>;
		} 
	<?php endfor; ?>
	
	select.selectedIndex = selectedIndex;
	
	return select;
}

/**
 * Adds the ordering cell
 */
Thead.prototype.addOrdering = function() {
	/* var span = new Element('span', {
		'class'	: 'changetable-ordering-span'
	}); */
	
	var span = document.createElement("span");
	span.setAttribute("class", "changetable-ordering-span");
	
	
	if (this.rowNumber > 0) {
		span.append(this.getOrderingImg("up"))
		/* this.getOrderingImg("up").inject(span); */
	}
	if (this.rowNumber < theads.theads.length - 1) {
		span.append(this.getOrderingImg("down"));
		/* this.getOrderingImg("down").inject(span); */
	}
	
	return span;
}

/**
 * Helper function for adding the ordering images
 */
Thead.prototype.getOrderingImg = function(direction) {
	/* var imgUp = new Element('span', {
		'id'	: 'changetable-ordering',
		'class'	: direction + 'arrow',
		'events': {
			'click': (function(thead, direction) {
				return function () {
					theads.reorder(thead, direction);
				}
			})(this, direction)
		}		
	}); */
	var thistHead = this;
	var thistHeads = theads;
	var imgUp = document.createElement("span");
	imgUp.setAttribute("id", "changetable-ordering");
	imgUp.setAttribute("class", direction + 'arrow');
	imgUp.addEventListener("click", function (){
		thistHeads.reorder(thistHead, direction);
	})
	
	return imgUp;
}

/**
 * Adds the default sorting cell
 */
Thead.prototype.addDefaultSorting = function() {
	/* var span = new Element('span', {'id'	: 'changetable-defaultSorting'}); */
	var span = document.createElement("span");
	span.setAttribute("id", "changetable-defaultSorting");
	
	/* var checkBox = new Element('div', {
		'id'	: 'changetable-checkBox',
		'text'	: this.defaultSorting['number']
	}); */
	var checkBox = document.createElement("div");
	checkBox.setAttribute("id", "changetable-checkBox");
	checkBox.innerText = this.defaultSorting['number'];
	
	span.append(checkBox);
	/* checkBox.inject(span); */
	
	var dirText = '';
	if (this.defaultSorting['direction'] == 'asc') {
		dirText = '<?php echo JTEXT::_('COM_EVENTTABLEEDIT_ASCENDING'); ?>';
	}
	else if (this.defaultSorting['direction'] == 'desc') {
		dirText = '<?php echo JTEXT::_('COM_EVENTTABLEEDIT_DESCENDING'); ?>';
	}
	else {
		dirText = '';
	}
	
	/* var direction = new Element('span', {
		'id'	: 'changetable-direction',
		'text'	: dirText
	}); */
	
	var direction = document.createElement("span");
	direction.setAttribute("id", "changetable-direction");
	direction.innerText = dirText;
	
	span.append(direction);
	/* direction.inject(span); */
	
	span.addEventListener('click', 
		(function(thead, elem) {
			return function () {
				thead.switchDefaultOrdering(elem);
			}
		})(this, span)
	);
	
	return span;
}

/**
 * Adds the icon to delete a row
 */
Thead.prototype.addDeleteIcon = function() {
	var thistHead = this;
	var deleteIcon = document.createElement("img");
	deleteIcon.setAttribute("id", "changetable-deleteIcon");
	deleteIcon.setAttribute("alt", "cross");
	deleteIcon.setAttribute("src", '<?php echo JURI::root(); ?>components/com_eventtableedit/template/images/cross.png');
	deleteIcon.addEventListener("click", function (){
		theads.deleteRow(thistHead);
	});
	
	return deleteIcon;
}

/**
 * Edit the name of a table head
 */
Thead.prototype.editName = function() {
	// Remove cell content
	var cell = this.row.cells[0];
	cell.innerHTML = '';
	
	// Add input field
	 
	var thistHead = this;
	
	var inputField = document.createElement("input");
	inputField.setAttribute("type", "text");
	inputField.setAttribute("id", "etetable-inputfield-active");
	inputField.setAttribute("class", "etetable-inputfield");
	inputField.setAttribute("value", this.name);
	inputField.addEventListener("blur", function (){
		thistHead.name = jQuery('#etetable-inputfield-active').val();
		
		// Add normal name text again
		thistHead.updateCell(cell, thistHead.addName());		
	}, thistHead);
	cell.append(inputField);
	
	// Only works this way in the internet explorer
	setTimeout("doFocus()", 100);
	
}
jQuery( "#etetable-inputfield-active" ).on( "keypress", function(event) {
    if (event.keyCode == 13) {
        event.preventDefault();
    }
});
function doFocus() {
	var val = $('#etetable-inputfield-active').val();        
	jQuery('#etetable-inputfield-active').focus().val('').val(val); 
}

/**
 * Reorder a row
 */
Theads.prototype.reorder = function(thead, direction) {
	// Reorder rows in Thead array
	var newRow = -1;
	if (direction == 'up') {
		newRow = thead.rowNumber - 1;
	} else {
		newRow = thead.rowNumber + 1;
	}
	
	var temp = this.theads[thead.rowNumber];
	this.theads[thead.rowNumber] = this.theads[newRow];
	this.theads[newRow] = temp;
	
	// Reorder dom rows
	// Delete old row
	$(this.theads[thead.rowNumber].row).remove();
	$(this.theads[newRow].row).remove();
	
	// Add it again
	if (direction == 'up') {
		this.addRows(newRow, newRow + 2); 
	} else {
		this.addRows(thead.rowNumber, thead.rowNumber + 2);
	}
}

/**
 * Set a default ordering for a table head
 */
Thead.prototype.switchDefaultOrdering = function(elem) {
	// Switch by state 
	switch(this.defaultSorting['direction']) {
		// No ordering set, yet
		case '':
			this.defaultSorting['number'] = ++theads.sortingNumber;
			this.defaultSorting['direction'] = 'asc';
			elem.getElements('div')[0].set('text', theads.sortingNumber);
			elem.getElements('span')[0].set('text', '<?php echo JTEXT::_('COM_EVENTTABLEEDIT_ASCENDING'); ?>');
			break;
		case 'asc':
			this.defaultSorting['direction'] = 'desc';
			elem.getElements('span')[0].set('text', '<?php echo JTEXT::_('COM_EVENTTABLEEDIT_DESCENDING'); ?>');
			break;
		case 'desc':
			elem.getElements('div')[0].set('text', '');
			elem.getElements('span')[0].set('text', '');
			
			this.updateSortingNumber();
			
			this.defaultSorting['number'] = '';
			this.defaultSorting['direction'] = '';
			break;
	}
}

/**
 * Updates the default sorting numbers
 */
Thead.prototype.updateSortingNumber = function() {
	if (this.defaultSorting['number'] == '') return;

	// Reorder other elements
	for (var a = 0; a < theads.theads.length; a++) {
		if (theads.theads[a].defaultSorting['number'] == '' ||
		    theads.theads[a].defaultSorting['number'] <= this.defaultSorting['number']) {
			continue;
		}
		theads.theads[a].defaultSorting['number']--;
		theads.theads[a].row.cells[3].getElements('div')[0].set('text', theads.theads[a].defaultSorting['number']);
	}
	theads.sortingNumber--;
}

/**
 * Delete a row
 */
Theads.prototype.deleteRow = function(thead) {
	// Delete row
	$(thead.row).remove();
	
	// Update the table numbers
	var tempRowNumber = thead.rowNumber;
	for (var a = thead.rowNumber; a < this.theads.length; a++) {
		this.theads[a].rowNumber--;
	}
	
	// Maybe the default sorting has to be updated
	thead.updateSortingNumber();
	
	// Delete from thead array
	this.theads.splice(tempRowNumber, 1);
	
	// Update ordering of first and last row if exists
	if (this.theads.length > 0) {
		var lastRow = this.theads[this.theads.length - 1];
		var cell = lastRow.row.cells[2];
		lastRow.updateCell(cell, lastRow.addOrdering());
		
		var firstRow = this.theads[0];
		cell = firstRow.row.cells[2];
		firstRow.updateCell(cell, firstRow.addOrdering());
	}
}

/**
 * Creates hidden input fields, that the form can be sent to the server
 */
Theads.prototype.serializeToForm = function() {
	for (var a = 0; a < this.theads.length; a++) {
		var thead = this.theads[a];
		this.addHiddenField('cid[]', thead.id);
		this.addHiddenField('name[]', thead.name);
		this.addHiddenField('datatype[]', thead.datatype);
		
		this.addHiddenField('defaultSorting[]', thead.defaultSorting['number'] + ':' + thead.defaultSorting['direction']);
	}
}

Theads.prototype.addHiddenField = function(name, value) {
	/* var hidden = new Element('input', {
		'type'	: 'hidden',
		'name'	: name,
		'value'	: value
	}); */
	var hidden = document.createElement("input");
	hidden.setAttribute("type", "hidden");
	hidden.setAttribute("name", name);
	hidden.setAttribute("value", value);
	jQuery('#adminForm').append(hidden);
	/* hidden.inject($('adminForm')); */
}

-->
</script>
