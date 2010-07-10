<?php
/**
* Arrays.
*/
if(!isset($wgXML2WikiAllowdPaths)) {
	$wgXML2WikiAllowdPaths = array();
}
if(!isset($wgXML2WikiConfig)) {
	$wgXML2WikiConfig = array();
}

/**
* Allowed Directories.
*/
$wgXML2WikiAllowdPaths[] = dirname(__FILE__).DIRECTORY_SEPARATOR.'xmls';

/**
* Configuration.
* @{
*/
/**
* Information
*	@{
*/
$wgXML2WikiConfig['showallowpaths'] = true;
$wgXML2WikiConfig['showinstalldir'] = true;
$wgXML2WikiConfig['showsysinfo']    = true;
$wgXML2WikiConfig['showmodules']    = true;
/**	@} */

/**
* Messages
*	@{
*/
$wgXML2WikiConfig['attributesprefix']      = '@';
$wgXML2WikiConfig['attributessuffix']      = '@';
$wgXML2WikiConfig['transattributesprefix'] = '@';
$wgXML2WikiConfig['transattributessuffix'] = '';
/**	@} */
/** @} */
?>
