<?php
/**
 * @file X2WParser.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-16
 */

/**
 * @class X2WParser
 */
class X2WParser {
	protected	$_x2wInstance;

	protected	$_data;
	protected	$_class;
	protected	$_filename;
	protected	$_localDebugEnabled;
	protected	$_showAttrs;
	protected	$_style;
	protected	$_translations;
	protected	$_translator;
	protected	$_xmlData;

	protected	$_auxTableData;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->_x2wInstance = Xml2Wiki::Instance();

		$this->_localDebugEnabled = false;
		/*
		 * Clearing status.
		 */
		$this->clear();
	}

	public function load() {
		/*
		 * This variable will hold the content to be retorned. Eighter
		 * some formatted XML text or an error message.
		 */
		$out   = '';
		$error = false;

		$out.=$this->formatDebugMessage("class = '{$this->_class}'");
		$out.=$this->formatDebugMessage("debug = '{$this->_localDebugEnabled}'");
		$out.=$this->formatDebugMessage("file = '{$this->_filename}'");
		$out.=$this->formatDebugMessage("showAttrs = '{$this->_showAttrs}'");
		$out.=$this->formatDebugMessage("style = '{$this->_style}'");
		$out.=$this->formatDebugMessage("translator ='{$this->_translator}'");

		if($this->_filename) {
			/*
			 * Getting and checking file-path to read.
			 * @{
			 */
			$filepath = $this->getFilePath($this->_filename);
			if(!$filepath) {
				$out  .= $this->getLastError();
				$error = true;
			} else {
				$out.=$this->formatDebugMessage("Loading XML from '{$filepath}'");
			}

			/* @} */
			/*
			 * Getting and checking translation-xml file-path to
			 * read.
			 * @{
			 */
			if(!$error && $this->_translator) {
				$tfilepath = $this->getFilePath($this->_translator);
				if($tfilepath) {
					$out.=$this->formatDebugMessage("Loading translations from '{$tfilepath}'");
					$out.= $this->loadTranslations($tfilepath);
				} else {
					$out  .= $this->getLastError();
					$error = true;
				}
			}
			/* @} */
			if(!$error) {
				if(is_readable($filepath)) {
					/*
					 * Loading file contents.
					 */
					$this->_data = file_get_contents($filepath);
				} else {
					$out  .= $this->setLastError($this->formatErrorMessage(wfMsg('forbbidenfile',$filepath)));
					$error = true;
				}
			}
		} else {
			$out  .= $this->getLastError($this->formatErrorMessage(wfMsg('nofilename')));
			$error = true;
		}

		return $out;
	}
	public function loadFromList($conf) {
		/*
		 * Clearing status.
		 */
		$this->clear();

		if(is_array($conf)) {
			/*
			 * Loading the configuration.
			 */
			$this->_class             = (isset($conf['class'])?$conf['class']:$this->_x2wInstance->varDefault('class'));
			$this->_filename          = (isset($conf['file'])?$conf['file']:$this->_x2wInstance->varDefault('file'));
			$this->_translator        = (isset($conf['translator'])?$conf['translator']:$this->_x2wInstance->varDefault('translator'));
			$this->_style             = (isset($conf['style'])?$conf['style']:$this->_x2wInstance->varDefault('style'));
			$this->_showAttrs         = (isset($conf['showattrs'])?$conf['showattrs']:$this->_x2wInstance->varDefault('showattrs'));
			$this->_localDebugEnabled = (isset($conf['debug'])?$conf['debug']:$this->_x2wInstance->varDefault('debug'));

			$this->_showAttrs         = (strtolower($this->_showAttrs) == 'on');
			$this->_localDebugEnabled = (strtolower($this->_localDebugEnabled) == 'on');
			return $this->load();
		} else {
			return '';
		}
	}
	public function loadFromTags($input, $params, $parser) {
		/*
		 * Clearing status.
		 */
		$this->clear();

		/*
		 * Loading the configuration set between tags.
		 */
		$this->loadVariables($input);

		return $this->load();
	}
	public function runCommand($cmd) {
		$out = '';

		$this->loadXMLData();
		eval("\$out.= \$this->_xmlData->{$cmd};");

		return $out;
	}
	public function show() {
		/*
		 * This variable will hold the content to be retorned. Eighter
		 * some formatted XML text or an error message.
		 */
		$out   = '';
		$error = false;
		/*
		 * Choosing style to be applied.
		 */
		switch(strtolower($this->_style)) {
			case 'code':
				$out.= $this->showAsCode();
				break;
			case 'direct':
				$out.= $this->showAsDirect();
				break;
			case 'pre':
			case '':
				$out.= $this->showAsPre();
				break;
			case 'list':
				$out.= $this->showAsList();
				break;
			case 'table':
				$out.= $this->showAsTable();
				break;
			default:
				$out  .= $this->setLastError($this->formatErrorMessage(wfMsg('unknown-style',$this->_style)));
				$error = true;
		}

		return $out;
	}
	protected function checkSimpleXML() {
		return $this->_x2wInstance->checkSimpleXML();
	}
	/**
	 * Clears all data concerning the file to be shown.
	 */
	protected function clear() {
		$this->_xmlData = false;

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
		$this->_showAttrs    = false;
		$this->_translations = array(
						'tags'  => array(),
						'attrs' => array()
		);
		$this->_translator   = '';
	}
	protected function debugEnabled() {
		return ($this->_x2wInstance->debugEnabled() || $this->_localDebugEnabled);
	}
	protected function formatDebugMessage($msg) {
		return $this->_x2wInstance->formatDebugMessage($msg, $this->_localDebugEnabled);
	}
	protected function formatErrorMessage($msg) {
		return $this->_x2wInstance->formatErrorMessage($msg);
	}
	protected function getFilePath($filename) {
		return $this->_x2wInstance->getFilePath($filename);
	}
	protected function getLastError() {
		return $this->_x2wInstance->getLastError();
	}
	/**
	 * Return parameters from mediaWiki;
	 *	use Default if parameter not provided;
	 *	use '' or 0 if Default not provided
	 */
	protected function getVariable($input, $name, $isNumber=false) {
		if($this->_x2wInstance->varDefault($name)) {
			$out = $this->_x2wInstance->varDefault($name);
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
	protected function loadTranslations($filepath) {
		$out = "";

		$this->setLastError();
		if($this->checkSimpleXML()) {
			$xml = @simplexml_load_file($filepath);
			if($xml) {
				if($xml->getName() == 'translations') {
					foreach($xml as $t) {
						if($t->getName() == 'translation') {
							if(isset($t->tag) && isset($t->means)) {
								$this->_translations['tags']["{$t->tag}"] = "{$t->means}";
							} elseif(isset($t->attribute) && isset($t->means)) {
								$this->_translations['attrs']["{$t->attribute}"] = "{$t->means}";
							} else {
								$out = $this->setLastError($this->formatErrorMessage(wfMsg('badtxml')));
								break;
							}
						} else {
							$out = $this->setLastError($this->formatErrorMessage(wfMsg('badtxml',$t->getName())));
							break;
						}
					}
				} else {
					$out = $this->setLastError($this->formatErrorMessage(wfMsg('badtxml',$xml->getName())));
				}
				unset($xml);
			} else {
				$out = $this->setLastError($this->formatErrorMessage(wfMsg('xml-noparsing',$filepath)));
			}
		} else {
			$out = $this->getLastError();
		}

		return $out;
	}

	/**
	 * This method tries to load all the useful information set between tags
	 * <xml2wiki> and </xml2wiki>.
	 * @param $input Configuration text to be analyzed.
	 */
	protected function loadVariables($input) {
		$this->_class      = $this->getVariable($input, 'class');
		$this->_filename   = $this->getVariable($input, 'file');
		$this->_translator = $this->getVariable($input, 'translator');
		$this->_style      = $this->getVariable($input, 'style');

		$aux = strtolower($this->getVariable($input, 'showattrs'));
		$this->_showAttrs = ($aux == 'on');

		$aux = strtolower($this->getVariable($input, 'debug'));
		$this->_localDebugEnabled = ($aux == 'on');
	}
	protected function loadXMLData() {
		if($this->_xmlData === false) {
			$this->_xmlData = simplexml_load_string($this->_data);
		}
	}
	protected function setLastError($msg="") {
		return $this->_x2wInstance->setLastError($msg);
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
			$out = $this->setLastError($this->formatErrorMessage(wfMsg('stylecode-extension')));
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

		$this->setLastError();
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
			$out = $this->getLastError();
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

		$this->setLastError();
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
			$out = $this->getLastError();
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
}

?>