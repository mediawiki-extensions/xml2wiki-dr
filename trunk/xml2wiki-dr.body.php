<?php
/**
 * @file xml2wiki-dr.body.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 */

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'config.php');

/**
 * @class Xml2Wiki
 */
class Xml2Wiki {
	protected static	$_Instance   = NULL;
	protected static	$_Properties = array(
						'name'            => 'Xml2Wiki',
						'version'         => '0.1b',
						'date'            => '2010-07-06',
						'_description'    => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.",
						'description'     => "XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.<sup>[{{SERVER}}{{SCRIPTPATH}}/extensions/xml2wiki-dr/xml2wiki-dr.php?info more]</sup>",
						'descriptionmsg'  => 'xml2wiki-desc',
						'author'          => array('Alejandro Darío Simi'),
						'url'             => 'http://wiki.daemonraco.com/wiki/xml2wiki-dr',
	);

	protected static	$ERROR_PREFIX = 'DR_XML2Wiki Error: ';

	protected	$_data;
	protected	$_class;
	protected	$_filename;
	protected	$_localDirectory;
	protected	$_style;
	protected	$_showAttrs;
	protected	$_translations;
	protected	$_translator;
	protected	$_varDefaults = array(
				'file'		=> '',		//! file to parse and transform.
				'class'         => 'wikitable',	//!
				'showattrs'	=> 'off',	//!
				'style'		=> 'pre',	//! parsing style.
				'translator'	=> '',		//! tag translator XML.
	);

	protected	$_auxTableData;

	protected	$_lastError;

	public function __construct() {
		$this->_lastError = '';

		$this->_localDirectory = dirname(__FILE__);

		/*
		 * Loading messages.
		 */
		wfLoadExtensionMessages('xml2wiki');

		/*
		 * Clearing status.
		 */
		$this->clear();

		/*
		 * Setting tag-kooks.
		 */
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
		$this->loadVariables($input);

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
					switch(strtolower($this->_style)) {
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
							$out = $this->showAsList();
							break;
						case 'table':
							$out = $this->showAsTable();
							break;
						default:
							$out = $this->_lastError = $this->formatErrorMessage(wfMsg('unknown-style',$this->_style));
					}
				} else {
					$out = $this->_lastError = $this->formatErrorMessage(wfMsg('forbbidenfile',$filepath));
				}
			}
		} else {
			$out = $this->_lastError = $this->formatErrorMessage(wfMsg('nofilename'));
		}

		return $out;
	}

	public function modulesCheck() {
		global	$wgXML2WikiConfig;
		if($wgXML2WikiConfig['showmodules']) {
			$mods = get_loaded_extensions();
			if($_REQUEST['modules']) {
				$mod   = $_REQUEST['modules'];
				echo "Module {$mod}... ";
				$mod   = strtolower($mod);
				$found = false;
				foreach($mods as $m) {
					if($mod == strtolower($m)) {
						$found = true;
						break;
					}
				}
				if($found) {
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
		} else {
			echo "We sorry, this information is disabled.";
		}
	}

	public function showInfo() {
		global	$wgXML2WikiAllowdPaths;
		global	$wgXML2WikiConfig;

		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		echo "<html>\n\t<head>\n\t\t<title></title>\n\t<body>\n";
		echo "\t\t<h2>Extension Information:</h2>\n";
		echo "\t\t<ul>\n";
		echo "\t\t\t<li><strong>Name:</strong> ".Xml2Wiki::Property('name')."</li>\n";
		echo "\t\t\t<li><strong>Version:</strong> ".Xml2Wiki::Property('version')."</li>\n";
		echo "\t\t\t<li><strong>Description:</strong> ".Xml2Wiki::Property('_description')."</li>\n";
		echo "\t\t\t<li><strong>Author:</strong> ".Xml2Wiki::Property('author')."</li>\n";
		echo "\t\t\t<li><strong>URL:</strong> ".Xml2Wiki::Property('url')."</li>\n";
		if($wgXML2WikiConfig['showinstalldir']) {
			echo "\t\t\t<li><strong>Installation Directory:</strong> ".dirname(__FILE__)."</li>\n";
		}
		echo "\t\t</ul>\n";
		echo "\t\t<h2>Allowed Paths:</h2>\n";
		if($wgXML2WikiConfig['showallowpaths']) {
			echo "\t\t<ul>\n";
			foreach($wgXML2WikiAllowdPaths as $path) {
				echo "\t\t\t<li>{$path}</li>\n";
			}
			echo "\t\t</ul>\n";
		} else {
			echo "\t\t<p>We sorry, this information is disabled.</p>\n";
		}
		if($wgXML2WikiConfig['showsysinfo']) {
			echo "\t\t<h2>System Information:</h2>\n";
			echo "\t\t<ul>\n";
			echo "\t\t\t<li><strong>Current PHP version:</strong> ".phpversion()."</li>\n";
			echo "\t\t</ul>\n";
		}
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
				$this->_lastError = $this->formatErrorMessage(wfMsg('forbbidenwfile',$aux[1]));
			}
		} else {
			if($this->checkAllowPath($in)) {
				$out = $in;
			} else {
				$this->_lastError = $this->formatErrorMessage(wfMsg('notallowedpath',$in));
			}
		}

		return $out;
	}

	protected function clear() {
		if(isset($this->_auxTableData)) {
			unset($this->_auxTableData);
		}
		if(isset($this->_translations)) {
			unset($this->_translations['tags']);
			unset($this->_translations['attrs']);
			unset($this->_translations);
		}

		$this->_auxTableData = array(
						'maxcols' => 1,
						'maxrows' => 1,
						'cells'   => array()
		);
		$this->data          = '';
		$this->_class        = '';
		$this->_filename     = '';
		$this->_lastError    = '';
		$this->_showAttrs    = false;
		$this->_translations = array(
						'tags'  => array(),
						'attrs' => array()
		);
		$this->_translator   = '';
	}

	protected function loadVariables($input) {
		$this->_class      = $this->getVariable($input, 'class');
		$this->_filename   = $this->getVariable($input, 'file');
		$this->_translator = $this->getVariable($input, 'translator');
		$this->_style      = $this->getVariable($input, 'style');

		$aux = strtolower($this->getVariable($input, 'showattrs'));
		$this->_showAttrs = ($aux == 'on');
	}

	protected function checkAllowPath($path) {
		global	$wgXML2WikiAllowdPaths;

		return (in_array($path, $wgXML2WikiAllowdPaths) || in_array(dirname($path), $wgXML2WikiAllowdPaths));
	}

	protected function loadTranslations($filepath) {
		$out = "";

		$this->_lastError = "";
		if($this->checkSimpleXML()) {
			$xml = simplexml_load_file($filepath);
			if($xml->getName() == 'translations') {
				foreach($xml as $t) {
					if($t->getName() == 'translation') {
						if(isset($t->tag) && isset($t->means)) {
							$this->_translations['tags']["{$t->tag}"] = "{$t->means}";
						} elseif(isset($t->attribute) && isset($t->means)) {
							$this->_translations['attrs']["{$t->attribute}"] = "{$t->means}";
						} else {
							$out = $this->_lastError = $this->formatErrorMessage(wfMsg('badtxml'));
							break;
						}
					} else {
						$out = $this->_lastError = $this->formatErrorMessage(wfMsg('badtxml',$t->getName()));
						break;
					}
				}
			} else {
				$out = $this->_lastError = $this->formatErrorMessage(wfMsg('badtxml',$xml->getName()));
			}
			unset($xml);
		} else {
			$out = $this->_lastError;
		}

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
			$out = $this->_lastError = $this->formatErrorMessage(wfMsg('stylecode-extension'));
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
		$out = "";

		$this->_lastError = "";
		if($this->checkSimpleXML()) {
			$out = "<div class=\"Xml2Wiki_list\">\n";

			$xml = simplexml_load_string($this->_data);
			$out.= "\t<span class=\"MainItem\">".$this->translate($xml->getName())."</span><ul>\n";

			if(count($xml->children())) {
				foreach($xml->children() as $child) {
					$out.= $this->showAsListChild($child);
				}
			}

			unset($xml);

			$out.= "\t</ul>\n";
			$out.= "</div>\n";
		} else {
			$out = $this->_lastError;
		}

		return $out;
	}
	protected function showAsListChild(&$child, $level=1, $space="\t\t") {
		$out = "";

		global	$wgXML2WikiConfig;

		if(count($child->children())) {
			foreach($child->children() as $c) {
				$out.= "{$space}<li class=\"ItemLevel{$level}\"><span class=\"ItemName\">".$this->translate($child->getName())."</span><ul>\n";
				$out.= $this->showAsListChild($c, $level+1, $space."\t");
				$out.= "{$space}</ul></li>\n";
			}
		} else {
			$value = "{$child}";
			$out  .= "{$space}<li class=\"ItemLevel{$level}\">\n";
			$out  .= "{$space}\t<span class=\"ItemName\">".$this->translate($child->getName()).($value?":":"")."</span>\n";
			$out  .= "{$space}\t<span class=\"ItemValue\">$value</span>\n";
			if($this->_showAttrs && count($child->attributes())) {
				$out  .= "{$space}\t<ul>\n";
				foreach($child->attributes() as $attr => $val) {
					$tattr = $this->translate("{$attr}",true);

					$out.= "{$space}\t\t<li>\n";
					if($tattr != $attr) {
						$out.= "{$space}\t\t\t<span class=\"ItemAttrName\">{$wgXML2WikiConfig['transattributesprefix']}{$tattr}{$wgXML2WikiConfig['transattributessuffix']}</span>\n";
					} else {
						$out.= "{$space}\t\t\t<span class=\"ItemAttrName\">{$wgXML2WikiConfig['attributesprefix']}{$attr}{$wgXML2WikiConfig['attributessuffix']}</span>\n";
					}
					$out.= "{$space}\t\t\t<span class=\"ItemAttrValue\">$val</span>\n";
					$out.= "{$space}\t\t</li>\n";
				}
				$out  .= "{$space}\t</ul>\n";
			}
			$out.= "{$space}</li>\n";
		}

		return $out;
	}
	protected function showAsPre() {
		return "<div class=\"Xml2Wiki_pre\"><pre>".htmlspecialchars($this->_data)."</pre></div>";
	}
	protected function showAsTable() {
		$out = "";

		$this->_lastError = "";
		if($this->checkSimpleXML()) {
			$out.= "<div class=\"Xml2Wiki_table\">\n";
			$out.= "\t<table class=\"{$this->_class}\">\n";

			$xml  = simplexml_load_string($this->_data);
			$tree = $this->showAsTableChild($xml);
			$aux  = $this->showAsTableTreeDig($tree);
			$this->_auxTableData['maxrows'] = $tree['rows'];
			$out.= "\t\t<tr>\n";
			$out.= "\t\t\t<th colspan=\"{$this->_auxTableData['maxcols']}\">".$this->translate($xml->getName())."</th>\n";
			$out.= "\t\t</tr>\n";
			for($y=1; $y<=$this->_auxTableData['maxrows']; $y++) {
				$out.= "\t\t<tr>\n";
				for($x=1, $colSpan=$this->_auxTableData['maxcols']; $x<=$this->_auxTableData['maxcols']; $x++, $colSpan--) {
					$id     = "{$x}-{$y}";
					if(isset($this->_auxTableData['cells'][$id])) {
						$cell = $this->_auxTableData['cells'][$id];
						if(isset($cell['value'])) {
							$out.= "\t\t\t<th rowspan=\"{$cell['rows']}\">{$cell['title']}</th>\n";
							$out.= "\t\t\t<td colspan=\"".($colSpan-1)."\">{$cell['value']}</td>\n";
						} elseif(isset($cell['nochildren'])) {
							$out.= "\t\t\t<td class=\"NoText\" colspan=\"2\" rowspan=\"{$cell['rows']}\">{$cell['title']}</td>\n";
						} else {
							$out.= "\t\t\t<th rowspan=\"{$cell['rows']}\">{$cell['title']}</th>\n";
						}
					}
				}
				$out.= "\t\t</tr>\n";
			}
			unset($xml);

			$out.= "\t</table>\n";
			$out.= "</div>\n";
		} else {
			$out = $this->_lastError;
		}

		return $out;
	}
	protected function showAsTableChild(&$child, $level=1, $space="\t\t") {
		$tree = array(
				'level' => $level,
				'space' => $space,
				'title' => $this->translate($child->getName())
		);
		if(count($child->children())) {
			foreach($child->children() as $c) {
				$aux = $this->showAsTableChild($c, $level+1, $space."\t");
				$tree[] = $aux;
			}
		} else {
			$value  = "{$child}";
			if($value) {
				$tree[] = $value;
			}
		}

		return $tree;
	}
	protected function showAsTableTreeDig(&$tree, $maxcols=0, &$x=0, &$y=1) {
		$maxcols++;
		if($this->_auxTableData['maxcols'] < $maxcols) {
			$this->_auxTableData['maxcols'] = $maxcols;
		}

		$tree['x'] = $x;
		$tree['y'] = $y;
		$cellId    = "{$tree['x']}-{$tree['y']}";
		$this->_auxTableData['cells'][$cellId] = array();

		$cols        = 0;
		$rows        = 0;
		$hasNumerics = false;
		foreach($tree as $k => $c) {
			if(is_numeric($k)) {
				$rows++;
				$hasNumerics = true;
				if(is_array($c)) {
					$x++;
					$r = $this->showAsTableTreeDig($c, $maxcols, $x, $y);
					$y++;
					$x--;
					$tree[$k] = $c;

					if($r > 1) {
						$rows+=$r-1;
					}
				}
			}
		}
		if(!$hasNumerics) {
			$cols = 2;
			$rows = 1;
			$y--;
			$tree['nochildren'] = 'NOCHILDREN';
			$this->_auxTableData['cells'][$cellId]['nochildren'] = 'NOCHILDREN';
		} else {
			if(!is_array($tree[0])) {
				$this->_auxTableData['cells'][$cellId]['value'] = "{$tree[0]}";
			}
			$cols = 1;
		}
		$tree['cols'] = $cols;
		$tree['rows'] = $rows;

		$this->_auxTableData['cells'][$cellId]['title'] = $tree['title'];
		$this->_auxTableData['cells'][$cellId]['cols']  = $tree['cols'];
		$this->_auxTableData['cells'][$cellId]['rows']  = $tree['rows'];
		$this->_auxTableData['cells'][$cellId]['level'] = $tree['level'];
		$this->_auxTableData['cells'][$cellId]['space'] = $tree['space'];

		return $rows;
	}

	/**
	 * Return parameters from mediaWiki;
	 *	use Default if parameter not provided;
	 *	use '' or 0 if Default not provided
	 */
	protected function getVariable($input, $name, $isNumber=false) {
		if(isset($this->_varDefaults[$name])) {
			$out = $this->_varDefaults[$name];
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

	protected function translate($name, $isAttr=false) {
		$out = $name;
		if($isAttr) {
			if(isset($this->_translations['attrs'][$name])) {
				$out = $this->_translations['attrs'][$name];
			}
		} else {
			if(isset($this->_translations['tags'][$name])) {
				$out = $this->_translations['tags'][$name];
			}
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
			$this->_lastError = $this->formatErrorMessage(wfMsg('simplexml-required'));
			$out = false;
		}

		return $out;
	}

	protected function formatErrorMessage($message) {
		return "<span style=\"color:red;font-weight:bold;\">".Xml2Wiki::$ERROR_PREFIX."$message</span>";
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
