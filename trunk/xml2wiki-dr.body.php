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

require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php');
require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'X2WAllowedPaths.php');
require_once($wgXML2WikiExtensionSysDir.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'X2WParser.php');

/**
 * @todo doc
 * @param $magicWords
 * @param $langCode
 * @return @todo doc
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
 * @todo doc
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
						'version'              => '0.4',
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
				'editable'	=> 'off'	//! editable XMLs on table view.
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
		global	$wgXML2WikiExtensionSysDir;

		$this->_lastError     = '';
		$this->_debugEnabled  = false;
		$this->_allowedPaths  = new X2WAllowedPaths($wgXML2WikiAllowedPaths,  $wgXML2WikiConfig['allowedpathsrecursive']);
		$this->_editablePaths = new X2WAllowedPaths($wgXML2WikiEditablePaths, $wgXML2WikiConfig['editablepathsrecursive']);
		$this->_xmls          = array();

		/*
		 * Getting current directory.
		 */
		$this->_localDirectory = $wgXML2WikiExtensionSysDir;

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

	/*
	 * Public Methods
	 */
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
	 * @return @todo doc
	 */
	public function debugEnabled() {
		return $this->_debugEnabled;
	}
	/**
	 * Inherited method. Please check parent class 'SpecialPage'.
	 * @param $par
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
	 * @param $message @todo doc
	 * @param $force @todo doc
	 * @return @todo doc
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
	 * @param $message @todo doc
	 * @return @todo doc
	 */
	public function formatErrorMessage($message) {
		return "<span style=\"color:red;font-weight:bold;\">".$this->ERROR_PREFIX."$message</span>";
	}
	/**
	 * This method allows the get a full path for a file. It's able to
	 * distinguish between a system file and a mediawiki file (this means
	 * something like [[File:...]].
	 * This method also checks if it's an allowed path or not.
	 * @param $in File path.
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

		global	$wgOut;

		$this->appendTOC($out);

		//		$wgOut->addHTML("\t\t<div style=\"float:right;text-align:center;\"><a href=\"http://wiki.daemonraco.com/\"><img src=\"http://wiki.daemonraco.com/wiki/dr.png\"/></a><br/><a href=\"http://wiki.daemonraco.com/\">DAEMonRaco</a></div>");

		$this->getInfoExtensionInformation($out);
		$this->getInfoStatus($out);
		$this->getInfoAllowedPaths($out);
		$this->getInfoEditablePaths($out);
		$this->getInfoSystemInformation($out);
		$this->getInfoModules($out);
		$this->getInfoRequiredExtensions($out);
		$this->getInfoConfiguration($out);
		$this->getInfoLinks($out);

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
	 * @param $parser @todo doc
	 * @param $frame @todo doc
	 * @param $args @todo doc
	 * @return @todo doc
	 */
	public function parseMasterTag(&$parser, $frame, array $args) {
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
	/**
	 * Tag Interpreter.
	 * This method is in charge of analyzing and generating the output
	 * content for tags &lt;xml2wiki&gt; and &lt;/xml2wiki&gt;.
	 * @param $input @todo doc
	 * @param $params @todo doc
	 * @param $parser @todo doc
	 * @return @todo doc
	 */
	public function parseSimpleTag($input, array $params, $parser) {
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
	/**
	 * @todo doc
	 * @param $enabled @todo doc
	 * @return @todo doc
	 */
	public function setDebugEnabled($enabled=true) {
		return $this->_debugEnabled = $enabled;
	}
	/**
	 * Sets last error message.
	 * @param $msg Message to set.
	 * @return Returns the message set.
	 */
	public function setLastError($msg="") {
		$this->_lastError = $msg;
		return $this->getLastError();
	}
	/**
	 * @todo doc
	 * @param $name @todo doc
	 * @return @todo doc
	 */
	public function varDefault($name) {
		return (isset($this->_varDefaults[$name])?$this->_varDefaults[$name]:'');
	}

	/*
	 * Protected Methods
	 */
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function appendAuthorLogo(&$out) {
		global	$wgXML2WikiConfig;
		if($wgXML2WikiConfig['show']['authorslogo']) {
			$this->enableTagHTML();
			$out.= "\t\t<html>\n";
			$out.= "\t\t\t<div style=\"float:right;text-align:center;\">\n";
			$out.= "\t\t\t\t<a href=\"http://wiki.daemonraco.com/\">\n";
			$out.= "\t\t\t\t\t<img src=\"http://wiki.daemonraco.com/wiki/dr.png\"/>\n";
			$out.= "\t\t\t\t</a><br/>\n";
			$out.= "\t\t\t\t<a href=\"http://wiki.daemonraco.com/\">DAEMonRaco</a>\n";
			$out.= "\t\t\t</div>\n";
			$out.= "\t\t</html>\n";
		}
	}
	/**
	 * This method appends a some magic words to enable a table of contents.
	 * @param $out Output text to be appended with a new information.
	 */
	protected function appendTOC(&$out) {
		$this->appendAuthorLogo($out);
		$out.= "__TOC__\n";
		$out.= "__NOEDITSECTION__\n";
	}
	/**
	 * @todo doc
	 * @param $parser @todo doc
	 * @param $frame @todo doc
	 * @param $args @todo doc
	 * @return @todo doc
	 */
	protected function cmdDebug(&$parser, $frame, array $args) {
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
	 * @param $parser @todo doc
	 * @param $frame @todo doc
	 * @param $args @todo doc
	 * @return @todo doc
	 */
	protected function cmdLoad(&$parser, $frame, array $args) {
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
			 *	- editable
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
	/**
	 * @todo doc
	 * @param $parser @todo doc
	 * @param $frame @todo doc
	 * @param $args @todo doc
	 * @return @todo doc
	 */
	protected function cmdNoCache(&$parser, $frame, array $args) {
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
	/**
	 * @todo doc
	 * @param $parser @todo doc
	 * @param $frame @todo doc
	 * @param $args @todo doc
	 * @return @todo doc
	 */
	protected function cmdValue(&$parser, $frame, array $args) {
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
	/**
	 * This mathod activates raw html insertion on mediawiki pages. In other
	 * words, it enables tags &lt;html&gt; and &lt;/html&gt;.
	 */
	protected function enableTagHTML() {
		global	$wgRawHtml;
		global	$wgParser;

		$wgRawHtml = true;
		/*
		 * Resetting core tags to enable tag <html>
		 * Only, from version 1.17 and above.
		 * @{
		 */
		if(class_exists('CoreTagHooks')) {
			CoreTagHooks::register($wgParser);
		}
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoAllowedPaths(&$out) {
		global	$wgXML2WikiConfig;

		/*
		 * Section: Allowed Paths
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-allowed-paths')." ==\n";
		if($wgXML2WikiConfig['show']['allowedpaths']) {
			$out.= "<p>".wfMsg('sinfo-allowed-paths-info').".</p>\n";
			$out.= "{|class=\"wikitable\"\n";
			$out.= "|-\n";
			$out.= "!colspan=\"2\"|".wfMsg('sinfo-allowed-paths')."\n";
			$list = $this->_allowedPaths->directories();
			$len  = count($list);
			if($len) {
				$out.= "|-\n";
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('directories')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_allowedPaths->files();
			$len  = count($list);
			if($len) {
				$out.= "|-\n";
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('files')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_allowedPaths->noAccess();
			$len  = count($list);
			if($len) {
				$out.= "|-\n";
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('noaccess')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|style=\"text-decoration:line-through;\"|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_allowedPaths->unknown();
			$len  = count($list);
			if($len) {
				$out.= "|-\n";
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('unknown')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|style=\"color:#a60000\"|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$out.= "|}\n";
		} else {
			$out.= "<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoConfiguration(&$out) {
		global	$wgXML2WikiConfig;
		global	$wgUser;
		global	$wgGroupPermissions;

		/*
		 * Section: Configuration
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-configs')." ==\n";
		$out.= "{|class=\"wikitable\"\n";
		$out.= "|-\n";
		$out.= "!colspan=\"3\"|".wfMsg('sinfo-attributes')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" rowspan=\"2\"|".wfMsg('sinfo-prefix')."\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-normal')."\n";
		$out.= "|\"{$wgXML2WikiConfig['attributesprefix']}\"\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-translated')."\n";
		$out.= "|\"{$wgXML2WikiConfig['transattributesprefix']}\"\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" rowspan=\"2\"|".wfMsg('sinfo-suffix')."\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-normal')."\n";
		$out.= "|\"{$wgXML2WikiConfig['attributessuffix']}\"\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-translated')."\n";
		$out.= "|\"{$wgXML2WikiConfig['transattributessuffix']}\"\n";
		$out.= "|-\n";
		$out.= "!colspan=\"3\"|".wfMsg('sinfo-permissions')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-show-allowedpaths')."\n";
		$out.= "|".($wgXML2WikiConfig['show']['allowedpaths']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-show-installdir')."\n";
		$out.= "|".($wgXML2WikiConfig['show']['installdir']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-show-sysinfo')."\n";
		$out.= "|".($wgXML2WikiConfig['show']['sysinfo']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-show-modules')."\n";
		$out.= "|".($wgXML2WikiConfig['show']['modules']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-allowedpathsrecursive')."\n";
		$out.= "|".($wgXML2WikiConfig['allowedpathsrecursive']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-editablepathsrecursive')."\n";
		$out.= "|".($wgXML2WikiConfig['editablepathsrecursive']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" colspan=\"2\"|".wfMsg('sinfo-allownocache')."\n";
		$out.= "|".($wgXML2WikiConfig['allownocache']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!colspan=\"3\"|".wfMsg('sinfo-user-permissions')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\" rowspan=\"6\"|x2w-tableedit\n";
		$out.= "!style=\"text-align:left;\"|<nowiki>*</nowiki>\n";
		$out.= "|".($wgGroupPermissions['*']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|user\n";
		$out.= "|".($wgGroupPermissions['user']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|bot\n";
		$out.= "|".($wgGroupPermissions['bot']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|sysop\n";
		$out.= "|".($wgGroupPermissions['sysop']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|bureaucrat\n";
		$out.= "|".($wgGroupPermissions['bureaucrat']['x2w-tableedit']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-your-permissions')."\n";
		$out.= "|".(in_array('x2w-tableedit',$wgUser->getRights())?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|}\n";
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoEditablePaths(&$out) {
		global	$wgXML2WikiConfig;

		/*
		 * Section: Editable Paths
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-editable-paths')." ==\n";
		if($wgXML2WikiConfig['show']['editablepaths']) {
			$out.= "{|class=\"wikitable\"\n";
			$out.= "|-\n";
			$out.= "! colspan=\"2\"|".wfMsg('sinfo-editable-paths')."\n";
			$list = $this->_editablePaths->directories();
			$len  = count($list);
			if($len) {
				$out.= "|-\n";
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('directories')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_editablePaths->files();
			$len  = count($list);
			if($len) {
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('files')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_editablePaths->noAccess();
			$len  = count($list);
			if($len) {
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('noaccess')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|style=\"text-decoration:line-through;\"|{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$list = $this->_editablePaths->unknown();
			$len  = count($list);
			if($len) {
				$out.= "!rowspan=\"{$len}\" style=\"vertical-align:top;text-align:left;\"|".wfMsg('unknown')."\n";
				for($i=0, $j=1; $i<$len; $i++, $j++) {
					$out.= "|style=\"color:#a60000\">{$list[$i]}\n";
					if($j < $len) {
						$out.= "|-\n";
					}
				}
			}
			$out.= "|}\n";
		} else {
			$out.= "<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoExtensionInformation(&$out) {
		global	$wgXML2WikiConfig;
		global	$wgXML2WikiExtensionSysDir;

		/*
		 * Section: Extension information.
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-extension-information')." ==\n";
		$out.= "*'''".wfMsg('sinfo-name').":''' ".Xml2Wiki::Property('name')."\n";
		$out.= "*'''".wfMsg('sinfo-version').":''' ".Xml2Wiki::Property('version')."\n";
		$out.= "*'''".wfMsg('sinfo-description').":''' ".Xml2Wiki::Property('_description')."\n";
		$out.= "*'''".wfMsg('sinfo-author').":'''\n";
		foreach(Xml2Wiki::Property('author') as $author) {
			$out.= "**{$author}\n";
		}
		$out.= "*'''".wfMsg('sinfo-url').":''' ".Xml2Wiki::Property('url')."\n";
		if($wgXML2WikiConfig['show']['installdir']) {
			$out.= "*'''".wfMsg('sinfo-installation-directory').":''' {$wgXML2WikiExtensionSysDir}\n";
		}
		$out.= "*'''".wfMsg('sinfo-svn').":'''\n";
		$aux = str_replace('$', '', Xml2Wiki::Property('svn-revision'));
		$aux = str_replace('LastChangedRevision: ', '', $aux);
		$out.= "**'''".wfMsg('sinfo-svn-revision').":''' r{$aux}\n";
		$aux = str_replace('$', '', Xml2Wiki::Property('svn-date'));
		$aux = str_replace('LastChangedDate: ', '', $aux);
		$out.= "**'''".wfMsg('sinfo-svn-date').":''' {$aux}\n";
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoLinks(&$out) {
		/*
		 * Section: Links
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-links')." ==\n";
		$out.= "*'''MediaWiki Extensions:''' http://www.mediawiki.org/wiki/Extension:XML2Wiki\n";
		$out.= "*'''Official Documentation:''' http://wiki.daemonraco.com/wiki/Xml2wiki-dr\n";
		$out.= "*'''GoogleCode Proyect Site:''' http://code.google.com/p/xml2wiki-dr/\n";
		$out.= "*'''GoogleCode Issues Trak:''' http://code.google.com/p/xml2wiki-dr/issues\n";
		$out.= "*'''GPL License:''' http://www.gnu.org/licenses/gpl.txt\n";
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoModules(&$out) {
		global	$wgXML2WikiConfig;

		/*
		 * Section: Modules
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-modules')." ==\n";
		if($wgXML2WikiConfig['show']['modules']) {
			$out.= "*'''SimpleXml:''' ".($this->checkSimpleXML()?wfMsg('sinfo-is-installed'):wfMsg('sinfo-not-installed'))."\n";
		} else {
			$out.= "<p>".wfMsg('sinfo-information-disabled').".</p>\n";
		}
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoRequiredExtensions(&$out) {
		global	$wgParser;

		$tags   = $wgParser->getTags();

		/*
		 * Section: Required Extensions
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-required-extensions')." ==\n";
		$tag = "";
		if(in_array('syntaxhighlight', $tags)) {
			$tag = 'syntaxhighlight';
		} elseif(in_array('source', $tags)) {
			$tag = 'source';
		}
		$out.= "*'''SyntaxHighlight:''' ".($tag?wfMsg('sinfo-is-installed-tag', $tag):wfMsg('sinfo-not-installed')."(".wfMsg('stylecode-extension2').")")."\n";
		/* @} */
	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoStatus(&$out) {
		global	$wgXML2WikiConfig;
		global	$wgParser;
		global	$wgUseAjax;

		$tags   = $wgParser->getTags();
		$mwords = $wgParser->getFunctionHooks();

		/*
		 * Section: Extension Status
		 * @{
		 */
		$out.= "== ".wfMsg('sinfo-status')." ==\n";
		$out.= "{|class=\"wikitable\"\n";
		$out.= "|-\n";
		$out.= "!colspan=\"2\"|".wfMsg('sinfo-status')."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('tag','xml2wiki')."\n";
		$out.= "|".(in_array('xml2wiki', $tags)?wfMsg('present'):wfMsg('not-present'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('magicword','#x2w')."\n";
		$out.= "|".(in_array('x2w', $mwords)?wfMsg('present'):wfMsg('not-present'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".get_class($wgParser)."::SFH_OBJECT_ARGS\n";
		$out.= "|".(defined(get_class($wgParser).'::SFH_OBJECT_ARGS')?wfMsg('present'):wfMsg('not-present'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-useajax')."\n";
		$out.= "|".($wgUseAjax?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|-\n";
		$out.= "!style=\"text-align:left;\"|".wfMsg('sinfo-internal-css')."\n";
		$out.= "|".($wgXML2WikiConfig['autocss']?wfMsg('enabled'):wfMsg('disabled'))."\n";
		$out.= "|}\n";
		/* @} */

	}
	/**
	 * @todo doc
	 * @param $out @todo doc
	 */
	protected function getInfoSystemInformation(&$out) {
		global	$wgXML2WikiConfig;
		global	$wgDBtype;

		/*
		 * Section: System Information
		 * @{
		 */
		if($wgXML2WikiConfig['show']['sysinfo']) {
			$dbr = wfGetDB( DB_SLAVE );

			$out.= "== ".wfMsg('sinfo-system-information')." ==\n";
			$out.= "*'''".wfMsg('sinfo-php-version').":''' ".phpversion()."\n";
			$out.= "*'''".wfMsg('sinfo-db-type').":''' ".$wgDBtype." (".$dbr->getSoftwareLink()." ".$dbr->getServerVersion().")\n";
			$out.= "*'''[http://www.mediawiki.org/ MediaWiki]:''' ".SpecialVersion::getVersionLinked()."\n";
		}
		/* @} */
	}

	/*
	 * Public Class Methods
	 */
	/**
	 * @todo doc
	 * @return  @todo doc
	 */
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
