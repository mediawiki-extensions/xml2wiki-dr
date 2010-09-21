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
global	$wgXML2WikiExtensionSysDir;
$wgXML2WikiAllowedPaths[]  = $wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'xmls';
$wgXML2WikiEditablePaths[] = $wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'xmls';
/** @} */

/**
 * Configuration.
 * @{
 */
/**
 * Allows to enable/disable internal CSS-file inclution.
 * @var boolean
 */
$wgXML2WikiConfig['autocss']	= true;
/**
 * Information
 *	@{
 */
$wgXML2WikiConfig['show']			= array();
$wgXML2WikiConfig['show']['authorslogo']	= true;
$wgXML2WikiConfig['show']['allowedpaths']	= true;
$wgXML2WikiConfig['show']['editablepaths']	= true;
$wgXML2WikiConfig['show']['installdir']		= true;
$wgXML2WikiConfig['show']['sysinfo']		= true;
$wgXML2WikiConfig['show']['modules']		= true;
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
