<?php
/**
 * @file config.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-07-06
 */

/**
 * Arrays.
 */
if(!isset($wgXML2WikiAllowedPaths)) {
	$wgXML2WikiAllowedPaths = array();
}
if(!isset($wgXML2WikiConfig)) {
	$wgXML2WikiConfig = array();
}

/**
 * Allowed Directories.
 * @{
 */
$wgXML2WikiAllowedPaths[]  = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'xmls';
$wgXML2WikiEditablePaths[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'xmls';
/** @} */

/**
 * Configuration.
 * @{
 */
/**
 * Information
 *	@{
 */
$wgXML2WikiConfig['showallowpaths']    = true;
$wgXML2WikiConfig['showeditablepaths'] = true;
$wgXML2WikiConfig['showinstalldir']    = true;
$wgXML2WikiConfig['showsysinfo']       = true;
$wgXML2WikiConfig['showmodules']       = true;
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

/**
 * Path Checks
 *	@{
 */
$wgXML2WikiConfig['allowedpathsrecursive']  = false;
$wgXML2WikiConfig['editablepathsrecursive'] = false;
/**	@} */

/**
 * Others
 *	@{
 */
$wgXML2WikiConfig['allownocache']          = true;
$wgXML2WikiConfig['ajaxseparator']         = '__SEPARATOR__';
/**	@} */

/**
 * Permissions
 *	@{
 */
$wgGroupPermissions['*']['x2w-tableedit']          = false;
$wgGroupPermissions['user']['x2w-tableedit']       = true;
$wgGroupPermissions['bot']['x2w-tableedit']        = false;
$wgGroupPermissions['sysop']['x2w-tableedit']      = true;
$wgGroupPermissions['bureaucrat']['x2w-tableedit'] = false;
/**	@} */
/** @} */
?>
