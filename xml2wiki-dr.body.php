<?php
/**
 * @file xml2wiki-dr.body.php
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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'X2WAllowedPaths.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'X2WParser.php');

/**
 * @todo doc
 * @param unknown_type $magicWords
 * @param unknown_type $langCode
 */
function wfXml2WikiLanguageGetMagic(&$magicWords, $langCode="en") {
	switch($langCode) {
		default:
			$magicWords['x2w'] = array(0, 'x2w');
	}
	return true;
}

/**
 * @class Xml2Wiki
 */
class Xml2Wiki extends SpecialPage {
	/**
	 * Singleton instance holder.
	 * @var Xml2Wiki
	 */
	protected static	$_Instance   = NULL;
	/**
	 * Extension properties holder.
	 * @var array
	 */
	protected static	$_Properties = array(
						'name'                 => 'Xml2Wiki',
						'version'              => '0.3',
						'date'                 => '2010-07-06',
						'_description'         => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags and MagicWord #x2w.",
						'description'          => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags and MagicWord #x2w.<sup>[[Special:Xml2Wiki|more]]</sup>",
						'descriptionmsg'       => 'xml2wiki-desc',
						'sinfo-description'    => "XML to Wiki \'\'special page\'\'. Visit [[Special:Xml2Wiki]]",
						'sinfo-descriptionmsg' => 'sinfo-xml2wiki-desc',
						'author'               => array('Alejandro Darío Simi'),
						'url'                  => 'http://wiki.daemonraco.com/wiki/xml2wiki-dr',
						'svn-date'             => '$LastChangedDate$',
						'svn-revision'         => '$LastChangedRevision$',
	);
	/**
	 * Debug messages prefix.
	 * @var string
	 */
	protected	$DEBUG_PREFIX = 'x2w-dbg: ';
	/**
	 * Error messages prefix.
	 * @var string
	 */
	protected	$ERROR_PREFIX = 'DR_XML2Wiki Error: ';
	/**
	 * List of default values for several variables.
	 * @var array
	 */
	protected	$_varDefaults = array(
				'file'		=> '',		//! file to parse and transform.
				'class'         => 'wikitable',	//!
				'debug'		=> 'off',	//! 
				'showattrs'	=> 'off',	//!
				'style'		=> 'pre',	//! parsing style.
				'translator'	=> '',		//! tag translator XML.
	);
	/**
	 * Allowed paths holder and checker.
	 * @var X2WAllowedPaths
	 */
	protected	$_allowedPaths;
	protected	$_editablePaths;
	protected	$_debugEnabled = false;
	protected	$_localDirectory;
	protected	$_lastError;
	/**
	 * List of loaded XMLs.
	 * @var array
	 */
	protected	$_xmls;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct('xml2wiki');

		global	$wgXML2WikiAllowedPaths;
		global	$wgXML2WikiEditablePaths;
		global	$wgXML2WikiConfig;

		$this->_lastError     = '';
		$this->_debugEnabled  = false;
		$this->_allowedPaths  = new X2WAllowedPaths($wgXML2WikiAllowedPaths,  $wgXML2WikiConfig['allowedpathsrecursive']);
		$this->_editablePaths = new X2WAllowedPaths($wgXML2WikiEditablePaths, $wgXML2WikiConfig['editablepathsrecursive']);
		$this->_xmls          = array();

		/*
		 * Getting current directory.
		 */
		$this->_localDirectory = dirname(__FILE__);

		/*
		 * Loading messages.
		 */
		wfLoadExtensionMessages('xml2wiki');

		/*
		 * Setting tag-kooks.
		 */
		if(defined('MEDIAWIKI')) {
			global	$wgHooks;
			global	$wgParser;

			$wgParser->setHook('xml2wiki', array(&$this, 'parseSimpleTag'));
			if(defined(get_class($wgParser).'::SFH_OBJECT_ARGS')) {
				# Add a hook to initialise the magic word.
				$wgHooks['LanguageGetMagic'][] = "wfXml2WikiLanguageGetMagic";
				
				$wgParser->setFunctionHook('x2w', array(&$this, 'parseMasterTag'),  SFH_OBJECT_ARGS);
			}
		}
	}
	/**
	 * Checks allowed paths.
	 * @param $path Path to check.
	 * @return Returns true if it's an allowed path.
	 */
	public function checkAllowPath($path) {
		return $this->_allowedPaths->check($path);
	}
	/**
	 * Checks editable paths.
	 * @param $path Path to check.
	 * @return Returns true if it's an editable path.
	 */
	public function checkEditablePath($path) {
		return $this->_editablePaths->check($path);
	}
	/**
	 * Checks if the PHP module SimpleXML is loaded.
	 * @return Returns true if it's present.
	 */
	public function checkSimpleXML() {
		$out = true;

		$mods  = get_loaded_extensions();
		$modsL = count($mods);
		$found = false;
		for($i=0; $i<$modsL && !$found; $i++) {
			if(strtolower($mods[$i]) == "simplexml") {
				$found = true;
			}
		}
		if(!$found) {
			$this->setLastError($this->formatErrorMessage(wfMsg('simplexml-required')));
			$out = false;
		}

		return $out;
	}
	/**
	 * @todo doc
	 */
	public function debugEnabled() {
		return $this->_debugEnabled;
	}
	/**
	 * Inherited method. Please check parent class 'SpecialPage'.
	 */
	public function execute($par) {
		global	$wgRequest;
		global	$wgOut;

		$this->setHeaders();

		/*
		 * Get request data from, e.g.
		 */
		$param = $wgRequest->getText('param');

		# Do stuff
		# ...
		$output = $this->getInfo();
		$wgOut->addWikiText($output);
	}
	/**
	 * @todo doc
	 * @param unknown_type $message @todo doc
	 * @param unknown_type $force @todo doc
	 */
	public function formatDebugMessage($message, $force=false) {
		if($this->debugEnabled() || $force) {
			return "<span style=\"color:purple;\">".$this->DEBUG_PREFIX."$message</span><br/>\n";
		} else {
			return '';
		}
	}
	/**
	 * @todo doc
	 * @param unknown_type $message @todo doc
	 */
	public function formatErrorMessage($message) {
		return "<span style=\"color:red;font-weight:bold;\">".$this->ERROR_PREFIX."$message</span>";
	}
	/**
	 * This method allows the get a full path for a file. It's able to
	 * distinguish between a system file and a mediawiki file (this means
	 * something like [[File:...]].
	 * This method also checks if it's an allowed path or not.
	 * @param string $in File path.
	 * @return Returns a full-path. On error, return an empty string and
	 * sets the proper error message.
	 */
	public function getFilePath($in) {
		$out = "";

		global	$wgUploadDirectory;

		/*
		 * Replacing doble-slashes.
		 */
		while(strpos($in, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR)) {
			$in = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $in);
		}
		/*
		 * Checking if it's a mediawiki file or not.
		 */
		if(preg_match('/^File:/i', $in)) {
			/*
			 * Getting file information.
			 * @{
			 */
			$aux = explode(':', $in);
			$obj = wfFindFile(Title::makeTitle(NS_IMAGE, $aux[1]));
			/* @} */
			/*
			 * Checking if it's available and generating absolute
			 * path to be returned.
			 */
			if($obj) {
				$out = $wgUploadDirectory.DIRECTORY_SEPARATOR.$obj->getRel();
			} else {
				$this->_lastError = $this->formatErrorMessage(wfMsg('forbbidenwfile',$aux[1],Title::makeTitle(NS_SPECIAL,'Upload')->escapeFullURL("wpDestFile={$aux[1]}")));
			}
		} else {
			/*
			 * Checking if it is an allowed path.
			 */
			if($this->checkAllowPath($in)) {
				$out = $in;
			} else {
				$this->_lastError = $this->formatErrorMessage(wfMsg('notallowedpath',$in));
			}
		}

		return $out;
	}
	/**
	 * This method is directly related to the special page Special:Xml2wiki.
	 * It generates the informatión to be shown into the special page.
	 * @return Returns the text to be shown.
	 */
	public function getInfo() {
		$out = "";

		global	$wgXML2WikiAllowedPaths;
		global	$wgXML2WikiEditablePaths;
		global	$wgXML2WikiConfig;
		global	$wgParser;
		global	$wgUseAjax;
		global	$wgUser;
		global	$wgGroupPermissions;
		global	$wgDBtype;
		global	$wgAllowExternalImages;

		$tags   = $wgParser->getTags();
		$mwords = $wgParser->getFunctionHooks();

		/*
		 * Section: Extension information.
		 * @{
		 */
		if($wgAllowExternalImages) {
			$out.= "\t\t<span style=\"float:right;text-align:center;\">http://wiki.daemonraco.com/wiki/dr.png<br/>[http://wiki.daemonraco.com/ DAEMonRaco]</span>\n";
		}
		$out.= "\t\t<h2>".wfMsg('sinfo-extension-information')."</h2>\n";
		$out.= "\t\t<ul>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-name').":</strong> ".Xml2Wiki::Property('name')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-version').":</strong> ".Xml2Wiki::Property('version')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-description').":</strong> ".Xml2Wiki::Property('_description')."</li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-author').":</strong><ul>\n";
		foreach(Xml2Wiki::Property('author') as $author) {
			$out.= "\t\t\t\t<li>{$author}</li>\n";
		}
		$out.= "\t\t\t</ul></li>\n";
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-url').":</strong> ".Xml2Wiki::Property('url')."</li>\n";
		if($wgXML2WikiConfig['showinstalldir']) {
			$out.= "\t\t\t<li><strong>".wfMsg('sinfo-installation-directory').":</strong> ".dirname(__FILE__)."</li>\n";
		}
		$out.= "\t\t\t<li><strong>".wfMsg('sinfo-svn').":</strong><ul>\n";
		$aux = str_replace('$', '', Xml2Wiki::Property('svn-revision'));
		$aux = str_replace('LastChangedRevision: ', '', $aux);
		$out.= "\t\t\t\t<li><strong>".wfMsg('sinfo-svn-revision').":</strong> r{$aux}</li>\n";
		$aux = str_replace('$', '', Xml2Wiki::Property('svn-date'));
		$aux = str_replace('LastChangedDate: ', '', $aux);
		$out.= "\t\t\t\t<li><strong>".wfMsg('sinfo-svn-date').":</strong> {$aux}</li>\n";
		$out.= "\t\t\t</ul></li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */
		/*
		 * Section: Extension Status
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-status')."</h2>\n";
		$out.= "\t\t<table class=\"wikitable\">\n";
		$out.= "\t\t\t<th colspan=\"2\">".wfMsg('sinfo-status')."</th>\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('tag','xml2wiki')."</th>\n";
		$out.= "\t\t\t\t<td>".(in_array('xml2wiki', $tags)?wfMsg('present'):wfMsg('not-present'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('magicword','#x2w')."</th>\n";
		$out.= "\t\t\t\t<td>".(in_array('x2w', $mwords)?wfMsg('present'):wfMsg('not-present'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".get_class($wgParser)."::SFH_OBJECT_ARGS</th>\n";
		$out.= "\t\t\t\t<td>".(defined(get_class($wgParser).'::SFH_OBJECT_ARGS')?wfMsg('present'):wfMsg('not-present'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-useajax')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgUseAjax?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr>\n";
		$out.= "\t\t</table>\n";
		/* @} */
		/*
		 * Section: Allowed Paths
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-allowed-paths')."</h2>\n";
		if($wgXML2WikiConfig['showallowpaths']) {
			$out.= "\t\t<p>".wfMsg('sinfo-allowed-paths-info').".</p>\n";
			$out.= "\t\t<table class=\"wikitable\">\n";
			$out.= "\t\t\t<th colspan=\"2\">".wfMsg('sinfo-allowed-paths')."</th>\n";
			$list = $this->_allowedPaths->directories();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('directories')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td>{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_allowedPaths->files();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('files')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td>{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_allowedPaths->noAccess();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('noaccess')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td style=\"text-decoration:line-through;\">{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_allowedPaths->unknown();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('unknown')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td style=\"color:#a60000\">{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$out.= "\t\t</table>\n";
		} else {
			$out.= "\t\t<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
		/*
		 * Section: Editable Paths
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-editable-paths')."</h2>\n";
		if($wgXML2WikiConfig['showeditablepaths']) {
			$out.= "\t\t<table class=\"wikitable\">\n";
			$out.= "\t\t\t<th colspan=\"2\">".wfMsg('sinfo-editable-paths')."</th>\n";
			$list = $this->_editablePaths->directories();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('directories')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td>{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_editablePaths->files();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('files')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td>{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_editablePaths->noAccess();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('noaccess')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td style=\"text-decoration:line-through;\">{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$list = $this->_editablePaths->unknown();
			$len  = count($list);
			if($len) {
				$out.= "\t\t\t<tr><th rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\">".wfMsg('unknown')."</th>\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "\t\t\t\t<td style=\"color:#a60000\">{$list[$i]}</td>\n";
					if($j < $len) {
						$out.= "\t\t\t</tr><tr>\n";
					}
				}
				$out.= "\t\t\t</tr>\n";
			}
			$out.= "\t\t</table>\n";
		} else {
			$out.= "\t\t<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
		/*
		 * Section: System Information
		 * @{
		 */
		if($wgXML2WikiConfig['showsysinfo']) {
			$dbr = wfGetDB( DB_SLAVE );

			$out.= "\t\t<h2>".wfMsg('sinfo-system-information')."</h2>\n";
			$out.= "\t\t<ul>\n";
			$out.= "\t\t\t<li><strong>".wfMsg('sinfo-php-version').":</strong> ".phpversion()."</li>\n";
			$out.= "\t\t\t<li><strong>".wfMsg('sinfo-db-type').":</strong> ".$wgDBtype." (".$dbr->getSoftwareLink()." ".$dbr->getServerVersion().")</li>\n";
			$out.= "\t\t\t<li><strong>[http://www.mediawiki.org/ MediaWiki]:</strong> ".SpecialVersion::getVersionLinked()."</li>\n";
			$out.= "\t\t</ul>\n";
		}
		/* @} */
		/*
		 * Section: Modules
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-modules')."</h2>\n";
		if($wgXML2WikiConfig['showmodules']) {
			$out.= "\t\t<ul>\n";
			$out.= "\t\t\t<li><strong>SimpleXml:</strong> ".($this->checkSimpleXML()?wfMsg('sinfo-is-installed'):wfMsg('sinfo-not-installed'))."</li>\n";
			$out.= "\t\t</ul>\n";
		} else {
			$out.= "\t\t<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
		/*
		 * Section: Required Extensions
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-required-extensions')."</h2>\n";
		$out.= "\t\t<ul>\n";
		$tag = "";
		if(in_array('syntaxhighlight', $tags)) {
			$tag = 'syntaxhighlight';
		} elseif(in_array('source', $tags)) {
			$tag = 'source';
		}
		$out.= "\t\t\t<li><strong>SyntaxHighlight:</strong> ".($tag?wfMsg('sinfo-is-installed-tag', $tag):wfMsg('sinfo-not-installed')."(".wfMsg('stylecode-extension2').")")."</li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */
		/*
		 * Section: Configuration
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-configs')."</h2>\n";
		$out.= "\t\t<table class=\"wikitable\">\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th colspan=\"3\">".wfMsg('sinfo-attributes')."</th>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" rowspan=\"2\">".wfMsg('sinfo-prefix')."</th>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-normal')."</th>\n";
		$out.= "\t\t\t\t<td>\"{$wgXML2WikiConfig['attributesprefix']}\"</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-translated')."</th>\n";
		$out.= "\t\t\t\t<td>\"{$wgXML2WikiConfig['transattributesprefix']}\"</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" rowspan=\"2\">".wfMsg('sinfo-suffix')."</th>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-normal')."</th>\n";
		$out.= "\t\t\t\t<td>\"{$wgXML2WikiConfig['attributessuffix']}\"</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-translated')."</th>\n";
		$out.= "\t\t\t\t<td>\"{$wgXML2WikiConfig['transattributessuffix']}\"</td>\n";
		$out.= "\t\t\t</tr>\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th colspan=\"3\">".wfMsg('sinfo-permissions')."</th>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-showallowpaths')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['showallowpaths']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-showinstalldir')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['showinstalldir']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-showsysinfo')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['showsysinfo']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-showmodules')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['showmodules']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-allowedpathsrecursive')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['allowedpathsrecursive']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-editablepathsrecursive')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['editablepathsrecursive']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" colspan=\"2\">".wfMsg('sinfo-allownocache')."</th>\n";
		$out.= "\t\t\t\t<td>".($wgXML2WikiConfig['allownocache']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t<tr>\n";
		$out.= "\t\t\t\t<th colspan=\"3\">".wfMsg('sinfo-user-permissions')."</th>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\" rowspan=\"6\">x2w-tableedit</th>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">*</th>\n";
		$out.= "\t\t\t\t<td>".($wgGroupPermissions['*']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">user</th>\n";
		$out.= "\t\t\t\t<td>".($wgGroupPermissions['user']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">bot</th>\n";
		$out.= "\t\t\t\t<td>".($wgGroupPermissions['bot']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">sysop</th>\n";
		$out.= "\t\t\t\t<td>".($wgGroupPermissions['sysop']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">bureaucrat</th>\n";
		$out.= "\t\t\t\t<td>".($wgGroupPermissions['bureaucrat']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr><tr>\n";
		$out.= "\t\t\t\t<th style=\"text-align:left;\">".wfMsg('sinfo-your-permissions')."</th>\n";
		$out.= "\t\t\t\t<td>".(in_array('x2w-tableedit',$wgUser->getRights())?wfMsg('enabled'):wfMsg('disabled'))."</td>\n";
		$out.= "\t\t\t</tr>\n";
		$out.= "\t\t</table>\n";
		/* @} */
		/*
		 * Section: Links
		 * @{
		 */
		$out.= "\t\t<h2>".wfMsg('sinfo-links')."</h2>\n";
		$out.= "\t\t<ul>\n";
		$out.= "\t\t\t<li><strong>MediaWiki Extensions:</strong> http://www.mediawiki.org/wiki/Extension:XML2Wiki</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Proyect Site:</strong> http://code.google.com/p/xml2wiki-dr/</li>\n";
		$out.= "\t\t\t<li><strong>GoogleCode Issues Trak:</strong> http://code.google.com/p/xml2wiki-dr/issues</li>\n";
		$out.= "\t\t</ul>\n";
		/* @} */

		return $out;
	}
	/**
	 * Gets last error message.
	 * @return Returns the message.
	 */
	public function getLastError() {
		return $this->_lastError;
	}
	/**
	 * @todo doc.
	 * @{
	 */
	public function parseMasterTag(&$parser, $frame, $args) {
		/*
		 * This variable will hold the content to be retorned. Eighter
		 * some formatted XML text or an error message.
		 */
		$out = '';

		$command = trim(strtolower(isset($args[0])?$frame->expand($args[0]):''));
		switch($command) {
			case 'value':
				$out.= $this->cmdValue($parser, $frame, $args);
				break;
			case 'load':
				$out.= $this->cmdLoad($parser, $frame, $args);
				break;
			case 'debug':
				$out.= $this->cmdDebug($parser, $frame, $args);
				break;
			case 'nocache':
				$out.= $this->cmdNoCache($parser, $frame, $args);
				break;
			default:
				$out.= $this->setLastError($this->formatErrorMessage(wfMsg('x2w-default',$command)));
				break;
		}

		return $out;
	}
	/** @} */
	/**
	 * Tag Interpreter.
	 * This method is in charge of analyzing and generating the output
	 * content for tags <xml2wiki> and </xml2wiki>.
	 */
	public function parseSimpleTag($input, $params, $parser) {
		/*
		 * This variable will hold the content to be retorned. Eighter
		 * some formatted XML text or an error message.
		 */
		$out = '';
		$simpleTagParser = new X2WParser();

		$this->setLastError();
		$out.= $simpleTagParser->loadFromTags($input, $params, $parser);
		if(!$this->getLastError()) {
			$out.= $simpleTagParser->show();
		}

		return $out;
	}
	public function setDebugEnabled($enabled=true) {
		return $this->_debugEnabled = $enabled;
	}
	/**
	 * Sets last error message.
	 * @param string $msg Message to set.
	 * @return Returns the message set.
	 */
	public function setLastError($msg="") {
		$this->_lastError = $msg;
		return $this->getLastError();
	}
	/**
	 * @todo doc
	 * @param unknown_type $name @todo doc
	 */
	public function varDefault($name) {
		return (isset($this->_varDefaults[$name])?$this->_varDefaults[$name]:'');
	}

	protected function cmdDebug(&$parser, $frame, $args) {
		$out = '';
		$aux = trim(strtolower(isset($args[1])?$frame->expand($args[1]):''));
		switch($aux) {
			case 'off':
			case 'false':
				$this->setDebugEnabled(false);
				break;
			case 'toggle':
				$this->setDebugEnabled(!$this->debugEnabled());
				break;
			default:
				$this->setDebugEnabled(true);
		}
		$out.= $this->formatDebugMessage('Debug Enabled');
		return $out;
	}
	/**
	 * @todo doc
	 * @param Parser $parser @todo doc
	 * @param unknown_type $frame @todo doc
	 * @param array $args @todo doc
	 */
	protected function cmdLoad(&$parser, $frame, $args) {
		$out = '';

		$id    = trim(strtolower(isset($args[1])?$frame->expand($args[1]):''));
		$xml   = trim(isset($args[2])?$frame->expand($args[2]):'');
		$trans = trim(isset($args[3])?$frame->expand($args[3]):'');

		$out.= $this->formatDebugMessage("Loading XML: id = '{$id}'");
		$out.= $this->formatDebugMessage("Loading XML: file = '{$xml}'");

		if(!isset($this->_xmls[$id])) {
			$conf = array('file' => $xml);
			/*
			 *	- class
			 *	- file
			 *	- translator
			 *	- style
			 *	- showattrs
			 *	- class
			 *	- debug
			 */
			$this->setLastError();
			$aux = new X2WParser();
			$out.= $aux->loadFromList($conf);
			if(!$this->getLastError()) {
				$this->_xmls[$id] = $aux;
				$out.= $this->formatDebugMessage("XML with ID '{$id}' added");
			}
		} else {
			$out.= $this->setLastError($this->formatErrorMessage(wfMsg('x2w-load-duplicated-id',$id)));
		}

		return $out;
	}
	protected function cmdNoCache(&$parser, $frame, $args) {
		$out = '';

		global	$wgXML2WikiConfig;

		if($wgXML2WikiConfig['allownocache']) {
			$parser->disableCache();
			$out.= $this->formatDebugMessage("Cache disabled");
		} else {
			$out.= $this->formatDebugMessage("Disable cache action is forbidden by configuration.");
		}

		return $out;
	}
	protected function cmdValue(&$parser, $frame, $args) {
		$out = '';

		$id  = trim(strtolower(isset($args[1])?$frame->expand($args[1]):''));
		$cmd = trim(isset($args[2])?$frame->expand($args[2]):'');

		if(isset($this->_xmls[$id])) {
			$out.= $this->_xmls[$id]->runCommand($cmd);
		} else {
			$out.= $this->setLastError($this->formatErrorMessage(wfMsg('x2w-load-no-id',$id)));
		}

		return $out;
	}

	public static function Instance() {
		if(!Xml2Wiki::$_Instance) {
			Xml2Wiki::$_Instance = new Xml2Wiki();
		}
		return Xml2Wiki::$_Instance;
	}
	public static function Property($name) {
		$name = strtolower($name);
		if(!isset(Xml2Wiki::$_Properties[$name])) {
			die("Xml2Wiki::Property(): Property '{$name}' does not exist (".__FILE__.":".__LINE__.").");
		}
		return Xml2Wiki::$_Properties[$name];
	}
}

?>
