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
function X2WEditValue(url, id, debug) {
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
	item.appendChild(input);
	input.onblur = function() {
		X2WEditedValue(url, id, debug);
	}
	input.focus();
}
function X2WEditedValue(url, id, debug) {
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
		X2WEditValue(url, id, debug);
	}
	
	X2WEditingXML = null;
}
