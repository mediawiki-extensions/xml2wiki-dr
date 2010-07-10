<?php
/**
 * @file xml2wiki-dr.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'xml2wiki-dr.body.php');

/**
 * Register function.
 */
function Xml2Wiki_Hooker() {
	Xml2Wiki::Instance();
}

if(!defined('MEDIAWIKI')) {
	if(isset($_REQUEST['modules'])) {
		Xml2Wiki::Instance()->modulesCheck();
	} elseif(isset($_REQUEST['info'])) {
		Xml2Wiki::Instance()->showInfo();
	} else {
		die();
	}
} else {
	/**
	 * MediaWiki Extension hooks Setter.
	 */
	$wgExtensionFunctions[]               = 'Xml2Wiki_Hooker';
	$wgExtensionMessagesFiles['xml2wiki'] = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'xml2wiki-dr.i18n.php';


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
	);

}
?>