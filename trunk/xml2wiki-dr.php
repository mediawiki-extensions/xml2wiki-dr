<?php
/**
 * @file xml2wiki-dr.php
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
$wgXML2WikiExtensionSysDir = dirname(__FILE__);
$wgXML2WikiExtensionWebDir = $wgScriptPath.'/extensions/'.basename(dirname(__FILE__));

require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php');
require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'tools.php');
require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'xml2wiki-dr.body.php');

/**
 * Register function.
 */
function Xml2Wiki_Hooker() {
	Xml2Wiki::Instance();
}
function Xml2Wiki_HeadHooker(&$out, &$sk) {
	global	$wgScriptPath;
	global	$wgUseAjax;
	global	$wgXML2WikiConfig;
	global	$wgXML2WikiExtensionWebDir;
	
	if($wgUseAjax) {
		$script = $wgXML2WikiExtensionWebDir.'/includes/xml2wiki.js.php';
		$out->addScript('<script type="text/javascript" src="'.$script.'"></script>');
	}

	if($wgXML2WikiConfig['autocss']) {
	
		$script = $wgXML2WikiExtensionWebDir.'/includes/style.css';
		$out->addScript('<link type="text/css" rel="stylesheet" href="'.$script.'"/>');
	}
	
	return true;
}

if(!defined('MEDIAWIKI')) {
	die();
} else {
	/**
	 * MediaWiki Extension hooks Setter.
	 */
	$wgExtensionFunctions[]               = 'Xml2Wiki_Hooker';
	$wgHooks['BeforePageDisplay'][]       = 'Xml2Wiki_HeadHooker';
	$wgAjaxExportList[]                   = 'X2WParser::AjaxParser';
	$wgExtensionMessagesFiles['xml2wiki'] = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'xml2wiki-dr.i18n.php';

	$wgAutoloadClasses  ['xml2wiki'] = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'xml2wiki-dr.body.php';
	$wgSpecialPages     ['xml2wiki'] = 'xml2wiki';
	$wgSpecialPageGroups['xml2wiki'] = 'other';

	/**
	 * MediaWiki Extension Description.
	 */
	$wgExtensionCredits['parserhook'][] = array(
		'name'            => Xml2Wiki::Property('name'),
		'version'         => Xml2Wiki::Property('version'),
		'date'            => Xml2Wiki::Property('date'),
		'description'     => Xml2Wiki::Property('description'),
		'descriptionmsg'  => Xml2Wiki::Property('descriptionmsg'),
		'author'          => Xml2Wiki::Property('author'),
		'url'             => Xml2Wiki::Property('url'),
		'svn-date'        => Xml2Wiki::Property('svn-date'),
		'svn-revision'    => Xml2Wiki::Property('svn-revision'),
	);
	$wgExtensionCredits['specialpage'][] = array(
		'name'            => Xml2Wiki::Property('name'),
		'version'         => Xml2Wiki::Property('version'),
		'date'            => Xml2Wiki::Property('date'),
		'description'     => Xml2Wiki::Property('sinfo-description'),
		'descriptionmsg'  => Xml2Wiki::Property('sinfo-descriptionmsg'),
		'author'          => Xml2Wiki::Property('author'),
		'url'             => Xml2Wiki::Property('url'),
	);
}
?>