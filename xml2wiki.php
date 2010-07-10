<?php
/**
* List of allowed Paths.
*/
if(!isset($wgXML2WikiAllowdPaths)) {
	$wgXML2WikiAllowdPaths = array();
}
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');

/**
* @class Xml2Wiki
*/
class Xml2Wiki {
	protected static	$_Instance   = NULL;
	protected static	$_Properties = array(
						'name'            => 'DR XML2Wiki',
						'version'         => '0.1b',
						'date'            => '2010-07-06',
						'_description'    => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.",
						'description'     => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.<sup>[{{SERVER}}{{SCRIPTPATH}}/extensions/xml2wiki-dr/xml2wiki.php?info more]</sup>",
						'descriptionmsg'  => 'drxml2wiki-desc',
						'author'          => array('Alejandro Darío Simi'),
						'url'             => 'http://wiki.daemonraco.com/wiki/DR_XML2Wiki',
					);

	protected static	$ERROR_PREFIX = 'DR_XML2Wiki Error: ';

	protected	$_data;
	protected	$_filename;
	protected	$_localDirectory;
	protected	$_style;
	protected	$_showAttrs;
	protected	$_translations;
	protected	$_translator;

	protected	$_lastError;

	public function __construct() {
		$this->_lastError = '';

		$this->_localDirectory = dirname(__FILE__);

		$this->clear();

		if(defined('MEDIAWIKI')) {
			global	$wgParser;
			$wgParser->setHook('xml2wiki', array(&$this, 'parse'));
		}
	}

	/**
	 * Tag Interpreter.
	 */
	public function parse($input, $params, $parser) {
		$out = '';

		global	$wgUploadDirectory;

		$this->clear();

		$this->_filename   = $this->getVariable($input, 'file',       false);
		$this->_translator = $this->getVariable($input, 'translator', false);
		$this->_style      = $this->getVariable($input, 'style',      false);
		$this->_showAttrs  = (strtolower($this->getVariable($input, 'showattrs', false)) == 'on');

		if($this->_filename) {
			$filepath = $this->getFilePath($this->_filename);
			if(!$filepath) {
				$out = $this->_lastError;
			}
			if(!$out && $this->_translator) {
				$tfilepath = $this->getFilePath($this->_translator);
				if($tfilepath) {
					$out = $this->loadTranslations($tfilepath);
				} else {
					$out = $this->_lastError;
				}
			}
			if(!$out) {
				if(is_readable($filepath)) {
					$this->_data = file_get_contents($filepath);
					switch($this->_style) {
						case 'code':
							$out = $this->showAsCode();
							break;
						case 'direct':
							$out = $this->showAsDirect();
							break;
						case 'pre':
						case '':
							$out = $this->showAsPre();
							break;
						case 'list':
							$this->_lastError = "";
							if($this->checkSimpleXML()) {
								$out = $this->showAsList();
							} else {
								$out = $this->_lastError;
							}
							break;
						default:
							$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Unknown style '{$this->_style}'.</span>";
					}
				} else {
					$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Unable to read '$filepath'</span>";
				}
			}
		} else {
			$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Filename not specified</span>";
		}

		return $out;
	}

	public function modulesCheck() {
		$mods = get_loaded_extensions();
		if($_REQUEST['modules']) {
			$mod = $_REQUEST['modules'];
			echo "Module {$mod}... ";
			if(in_array($mod, $mods)) {
				echo "Ok\n";
			} else {
				echo "Failed\n";
			}
		} else {
			echo "Modules:<ul>\n";
			foreach($mods as $mod) {
				echo "<li>$mod</li>\n";
			}
			echo "</ul>\n";
		}
	}

	public function showInfo() {
		global	$wgXML2WikiAllowdPaths;
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		echo "<html>\n\t<head>\n\t\t<title></title>\n\t<body>\n";
		echo "\t\t<h2>Extension Information:</h2>\n";
		echo "\t\t<ul>\n";
		echo "\t\t\t<li><strong>Name:</strong> ".Xml2Wiki::Property('name')."</li>\n";
		echo "\t\t\t<li><strong>Version:</strong> ".Xml2Wiki::Property('version')."</li>\n";
		echo "\t\t\t<li><strong>Description:</strong> ".Xml2Wiki::Property('_description')."</li>\n";
		echo "\t\t\t<li><strong>Author:</strong> ".Xml2Wiki::Property('author')."</li>\n";
		echo "\t\t\t<li><strong>URL:</strong> ".Xml2Wiki::Property('url')."</li>\n";
		echo "\t\t\t<li><strong>Installation Directory:</strong> ".dirname(__FILE__)."</li>\n";
		echo "\t\t</ul>\n";
		echo "\t\t<h2>Allowed Paths:</h2>\n";
		echo "\t\t<ul>\n";
		foreach($wgXML2WikiAllowdPaths as $path) {
			echo "\t\t\t<li>{$path}</li>\n";
		}
		echo "\t\t</ul>\n";
		echo "\t\t</ul>\n";
		echo "\t\t<h2>System Information:</h2>\n";
		echo "\t\t<ul>\n";
		echo "\t\t\t<li><strong>Current PHP version:</strong> ".phpversion()."</li>\n";
		echo "\t\t</ul>\n";
		echo "\t</body>\n</html>\n";
	}

	protected function getFilePath($in) {
		$out = "";

		global	$wgUploadDirectory;

		while(strpos($in, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR)) {
			$in = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $in);
		}
		if(preg_match('/^File:/i', $in)) {
			$aux = explode(':', $in);
			$obj = wfFindFile(Title::makeTitle(NS_IMAGE, $aux[1]));
			if($obj) {
				$out = $wgUploadDirectory.DIRECTORY_SEPARATOR.$obj->getRel();
			} else {
				$this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Unable to read wiki file '{$aux[1]}'</span>";
			}
		} else {
			if($this->checkAllowPath($in)) {
				$out = $in;
			} else {
				$this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Path {$in} is not allowed. Please check variable \$wgXML2WikiAllowdPaths in your system configuration'</span>";
			}
		}

		return $out;
	}

	protected function clear() {
		unset($this->_translations);

		$this->data          = '';
		$this->_filename     = '';
		$this->_lastError    = '';
		$this->_showAttrs    = false;
		$this->_translations = array();
		$this->_translator   = '';
	}

	protected function checkAllowPath($path) {
		global	$wgXML2WikiAllowdPaths;

		return (in_array($path, $wgXML2WikiAllowdPaths) || in_array(dirname($path), $wgXML2WikiAllowdPaths));
	}
	
	protected function loadTranslations($filepath) {
		$out = "";

		$xml = simplexml_load_file($filepath);
		if($xml->getName() == 'translations') {
			foreach($xml as $t) {
				if($t->getName() == 'translation') {
					if(isset($t->tag) && isset($t->means)) {
						$this->_translations["{$t->tag}"] = "{$t->means}";
					} else {
						$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Bad formed translation XML</a></span>";
						break;
					}
				} else {
					$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Bad formed translation XML. Unknown tag '".$xml->getName()."'</a></span>";
					break;
				}
			}
		} else {
			$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Bad formed translation XML. Unknown tag '".$xml->getName()."'</a></span>";
		}
		unset($xml);

		return $out;
	}

	protected function showAsCode() {
		$out = '';

		global	$wgParser;
		$tags = $wgParser->getTags();
		$tag  = '';
		$hook = NULL;

		if(in_array('syntaxhighlight', $tags)) {
			$tag = 'syntaxhighlight';
		} elseif(in_array('source', $tags)) {
			$tag = 'source';
		}

		if(!$tag) {
			$out = $this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Style 'code' requires <a target=\"_blank\" href=\"http://www.mediawiki.org/wiki/Extension:SyntaxHighlight\">SyntaxHighlight Extension</a></span>";
		} else {
			$out = $wgParser->recursiveTagParse("<$tag lang=\"xml\">{$this->_data}</$tag>");
			$out = "<div class=\"Xml2Wiki_code\">".$out."</div>";
		}

		return $out;
	}
	protected function showAsDirect() {
		return "<div class=\"Xml2Wiki_direct\">".htmlspecialchars($this->_data)."</div>";
	}
	protected function showAsList() {
		$out = "<div class=\"Xml2Wiki_list\">\n";

		$xml = simplexml_load_string($this->_data);
		$out.= "\t<span class=\"MainItem\">".$this->translateTagName($xml->getName())."</span><ul>\n";

		if(count($xml->children())) {
			foreach($xml->children() as $child) {
				$out.= $this->showAsListChild($child);
			}
		}

		unset($xml);

		$out.= "\t</ul>\n";
		$out.= "</div>\n";
		
		return $out;
	}
	protected function showAsListChild(&$child, $level=1, $space="\t\t") {
		$out = "";

		if(count($child->children())) {
			foreach($child->children() as $c) {
				$out.= "{$space}<li class=\"ItemLevel{$level}\"><span class=\"ItemName\">".$this->translateTagName($child->getName())."</span><ul>\n";
				$out.= $this->showAsListChild($c, $level+1, $space."\t");
				$out.= "{$space}</ul></li>\n";
			}
		} else {
			$value = "{$child}";
			$out  .= "{$space}<li class=\"ItemLevel{$level}\">\n";
			$out  .= "{$space}\t<span class=\"ItemName\">".$this->translateTagName($child->getName()).($value?":":"")."</span>\n";
			$out  .= "{$space}\t<span class=\"ItemValue\">$value</span>\n";
			$out  .= "{$space}</li>\n";
		}
		
		return $out;
	}
	protected function showAsPre() {
		return "<div class=\"Xml2Wiki_pre\"><pre>".htmlspecialchars($this->_data)."</pre></div>";
	}

	/**
	 * Return parameters from mediaWiki;
	 *	use Default if parameter not provided;
	 *	use '' or 0 if Default not provided
	 */
	protected function getVariable($input, $name, $isNumber=false) {
		$defaults = array(
			'file'		=> '',		//! file to parse and transform.
			'showattrs'	=> 'off',	//! tag translator XML.
			'style'		=> 'pre',	//! parsing style.
			'translator'	=> '',		//! tag translator XML.
		);

		if(isset($defaults[$name])) {
			$out = $defaults[$name];
		} else {
			$out = ($isNumber) ? 0 : '';
		}

		if(preg_match("/^\s*$name\s*=\s*(.*)/mi", $input, $matches)) {
			if($isNumber) {
				$out = intval($matches[1]);
			} elseif($matches[1] != null) {
				$out = htmlspecialchars($matches[1]);
			}
		}

		return $out;
	}

	protected function translateTagName($name) {
		$out = $name;
		if(isset($this->_translations[$name])) {
			$out = $this->_translations[$name];
		}
		return $out;
	}

	protected function checkSimpleXML() {
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
			$this->_lastError = "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."Module SimpleXML is required (<a target=\"_blank\" href=\"extensions/xml2wiki-dr/xml2wiki.php?modules=SimpleXML\">check module status</a>)</span>";
			$out = false;
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
	$wgExtensionFunctions[] = 'Xml2Wiki_Hooker';

	/**
	 * MediaWiki Extension Description.
	 */
	$wgExtensionCredits['parserhook'][] = array(
		'name'            => Xml2Wiki::Property('name'),
		'version'         => Xml2Wiki::Property('version'),
		'date'            => Xml2Wiki::Property('date'),
		'description'     => Xml2Wiki::Property('description'),
		'author'          => Xml2Wiki::Property('author'),
		'url'             => Xml2Wiki::Property('url'),
	);

}

?>