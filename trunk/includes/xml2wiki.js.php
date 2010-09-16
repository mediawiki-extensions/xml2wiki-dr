/**
 * @file xml2wiki.js
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-24
 */
<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
?>
var	X2WEDITING_ID = false;

function X2WEditValue(url, id, article, debug) {
	X2WEDITING_ID = id;
	/*
	 * Getting item to process.
	 */
	var	item        = document.getElementById(id);
	/*
	 * Removing onClick event.
	 */
	item.onclick = null;
	/*
	 * Getting current data.
	 */
	var	itemContent = item.innerHTML;
	var	itemWidth   = item.clientWidth;
	/*
	 * Clearing item.
	 */
	if(item.hasChildNodes()) {
		while(item.childNodes.length >= 1) {
			item.removeChild(item.firstChild);
	    }
	}
	/*
	 * Creating inputs for the edition.
	 */
	var	input = document.createElement('input');
	input.setAttribute('id',    id+'_hinput');
	input.setAttribute('type',  'hidden');
	input.setAttribute('value', itemContent);
	item.appendChild(input);
	input = document.createElement('input');
	input.setAttribute('id',    id+'_input');
	input.setAttribute('type',  'text');
	input.setAttribute('value', itemContent);
	input.setAttribute('style', 'width:'+itemWidth+'px;');
	item.appendChild(input);
	input.onblur = function() {
		X2WEditedValue(url, id, article, debug);
	}
	input.onkeydown = X2WKeyDown;
	input.focus();
}
function X2WEditedValue(url, id, article, debug) {
	/*
	 * Getting item to process.
	 */
	var	item        = document.getElementById(id);
	/*
	 * Getting data to be saved.
	 */
	var	itemContent    = document.getElementById(id+'_input').value;
	var	itemOldContent = document.getElementById(id+'_hinput').value;
	/*
	 * Saving Data.
	 */
	sajax_do_call('X2WParser::AjaxParser', [url+'<?php echo($wgXML2WikiConfig['ajaxseparator']); ?>'+
						itemContent+'<?php echo($wgXML2WikiConfig['ajaxseparator']); ?>'+
						itemOldContent+'<?php echo($wgXML2WikiConfig['ajaxseparator']); ?>'+
						id+'<?php echo($wgXML2WikiConfig['ajaxseparator']); ?>'+
						article+'<?php echo($wgXML2WikiConfig['ajaxseparator']); ?>'+
						(debug?'on':'off')], item);
	/*
	 * Clearing item.
	 */
	if(item.hasChildNodes()) {
		while(item.childNodes.length >= 1) {
			item.removeChild(item.firstChild);
	    }
	}
	/*
	 * Creating the input as it was with the new data.
	 */
	var	text = document.createTextNode(itemContent);
	item.appendChild(text);
	/*
	 * Restoring onClick event.
	 */
	item.onclick = function() {
		X2WEditValue(url, id, article, debug);
	}
	
	X2WEDITING_ID = false;
}
function X2WKeyDown(event) {
	/*
	 * MSIE hack
	 */
	if(window.event) {
		event = window.event;
	}
	/*
	 * Getting items to process.
	 */
	var	item         = document.getElementById(X2WEDITING_ID);
	var	contentInput = document.getElementById(X2WEDITING_ID+'_input');
	/*
	 * Getting data to be saved.
	 */
	var	itemContent    = contentInput.value;
	var	itemOldContent = document.getElementById(X2WEDITING_ID+'_hinput').value;
	
	/*
	 * Checking key event.
	 */
	var KeyID = event.keyCode;
	if(KeyID == 13) {
		contentInput.blur();
	} else if(KeyID == 27) {
		contentInput.value = itemOldContent;
		contentInput.blur();
		item.innerHTML = itemOldContent;
	}

	return true;
}
