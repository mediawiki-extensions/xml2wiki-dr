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
 * @todo doc
 */
class X2WParser {
	/**
	 * @var integer
	 */
	protected static	$_ParserId = 0;

	/**
	 * @var Xml2Wiki
	 */
	protected	$_x2wInstance;
	/**
	 * @var string
	 */
	protected	$_data;
	/**
	 * @var boolean
	 */
	protected	$_editableFlaged;
	/**
	 * @var string
	 */
	protected	$_class;
	/**
	 * @var string
	 */
	protected	$_filename;
	/**
	 * @var string
	 */
	protected	$_filepath;
	/**
	 * @var boolean
	 */
	protected	$_isEditable;
	/**
	 * @var boolean
	 */
	protected	$_isEditableChecked;
	/**
	 * @var boolean
	 */
	protected	$_localDebugEnabled;
	/**
	 * @var boolean
	 */
	protected	$_showAttrs;
	/**
	 * @var string
	 */
	protected	$_style;
	/**
	 * @var array
	 */
	protected	$_translations;
	/**
	 * @var string
	 */
	protected	$_translator;
	/**
	 * @var SimpleXMLElement
	 */
	protected	$_xmlData;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->_x2wInstance = Xml2Wiki::Instance();
		X2WParser::$_ParserId++;

		$this->_localDebugEnabled = false;
		/*
		 * Clearing status.
		 */
		$this->clear();
	}

	/*
	 * Public methods.
	 */
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	public function debugEnabled() {
		return ($this->_x2wInstance->debugEnabled() || $this->_localDebugEnabled);
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	public function isEditable() {
		if(!$this->_isEditableChecked) {
			$this->_isEditable        = false;
			$this->_isEditableChecked = true;

			if($this->_editableFlaged && $this->_x2wInstance->checkEditablePath($this->_filepath)) {
				global	$wgUseAjax;
				global	$wgRequest;

				//$action = $wgRequest->getVal('action', 'view');
				//if(!in_array($action,array('edit','ajax','submit')) && $wgUseAjax) {
				if($wgUseAjax) {
					global	$wgUser;
					$this->_isEditable = in_array('x2w-tableedit', $wgUser->getRights());
				}
			}
		}

		return $this->_isEditable;
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
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
		$out.=$this->formatDebugMessage("editable ='{$this->_editableFlaged}'");

		if($this->_filename) {
			/*
			 * Getting and checking file-path to read.
			 * @{
			 */
			$this->_filepath = $this->getFilePath($this->_filename);
			if(!$this->_filepath) {
				$out  .= $this->getLastError();
				$error = true;
			} else {
				$out.=$this->formatDebugMessage("Loading XML from '{$this->_filepath}'");
			}
			/* @} */
			/*
			 * Cheking if it is editable.
			 * @{
			 */
			$out.=$this->formatDebugMessage("XML is ".($this->isEditable()?'':'not ')."editable");
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
				if(is_readable($this->_filepath)) {
					/*
					 * Loading file contents.
					 */
					$this->_data = file_get_contents($this->_filepath);
				} else {
					$out  .= $this->setLastError($this->formatErrorMessage(wfMsg('forbbidenfile',$this->_filepath)));
					$error = true;
				}
			}
		} else {
			$out  .= $this->setLastError($this->formatErrorMessage(wfMsg('nofilename')));
			$error = true;
		}

		return $out;
	}
	/**
	 *  @todo doc
	 *  @param $conf @todo doc
	 *  @return @todo doc
	 */
	public function loadFromList(array $conf) {
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
			$this->_editableFlaged    = (isset($conf['editable'])?$conf['editable']:$this->_x2wInstance->varDefault('editable'));

			$this->_showAttrs         = (strtolower($this->_showAttrs)         == 'on');
			$this->_localDebugEnabled = (strtolower($this->_localDebugEnabled) == 'on');
			$this->_editableFlaged    = (strtolower($this->_editableFlaged)    == 'on');
			return $this->load();
		} else {
			return '';
		}
	}
	/**
	 * @todo doc
	 * @param $input @todo doc
	 * @param $params @todo doc
	 * @param $parser @todo doc
	 * @return @todo doc
	 */
	public function loadFromTags($input, array $params, $parser) {
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
	/**
	 * @todo doc
	 * @param $cmd @todo doc
	 * @return @todo doc
	 */
	public function runCommand($cmd) {
		$out = '';

		$this->loadXMLData();
		eval("\$out.= \$this->_xmlData->{$cmd};");

		return $out;
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
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

	/*
	 * Protected Methods.
	 */
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function checkSimpleXML() {
		return $this->_x2wInstance->checkSimpleXML();
	}
	/**
	 * Clears all data concerning the file to be shown.
	 */
	protected function clear() {
		$this->_xmlData = false;

		if(isset($this->_translations)) {
			unset($this->_translations['tags']);
			unset($this->_translations['attrs']);
			unset($this->_translations);
		}

		$this->data          = '';
		$this->_class        = '';
		$this->_filename     = '';
		$this->_filepath     = '';
		$this->_showAttrs    = false;
		$this->_translations = array(
						'tags'  => array(),
						'attrs' => array()
		);
		$this->_translator   = '';

		$this->_isEditable        = false;
		$this->_isEditableChecked = false;
	}
	/**
	 * @todo doc
	 * @param $msg @todo doc
	 * @return @todo doc
	 */
	protected function formatDebugMessage($msg) {
		return $this->_x2wInstance->formatDebugMessage($msg, $this->_localDebugEnabled);
	}
	/**
	 * @todo doc
	 * @param $msg @todo doc
	 * @return @todo doc
	 */
	protected function formatErrorMessage($msg) {
		return $this->_x2wInstance->formatErrorMessage($msg);
	}
	/**
	 * @todo doc
	 * @param $filename @todo doc
	 * @return @todo doc
	 */
	protected function getFilePath($filename) {
		return $this->_x2wInstance->getFilePath($filename);
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function getLastError() {
		return $this->_x2wInstance->getLastError();
	}
	/**
	 * Return parameters from mediaWiki;
	 *	use Default if parameter not provided;
	 *	use '' or 0 if Default not provided
	 * @param $input @todo doc
	 * @param $name @todo doc
	 * @param $isNumber @todo doc
	 * @return @todo doc
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
	/**
	 * @todo doc
	 * @param $filepath @todo doc
	 * @return @todo doc
	 */
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
	 * &lt;xml2wiki&gt; and &lt;/xml2wiki&gt;.
	 * @param $input Configuration text to be analyzed.
	 */
	protected function loadVariables($input) {
		$this->_class      = trim($this->getVariable($input, 'class'));
		$this->_filename   = trim($this->getVariable($input, 'file'));
		$this->_translator = trim($this->getVariable($input, 'translator'));
		$this->_style      = trim($this->getVariable($input, 'style'));

		$aux = strtolower(trim($this->getVariable($input, 'showattrs')));
		$this->_showAttrs = ($aux == 'on');

		$aux = strtolower(trim($this->getVariable($input, 'debug')));
		$this->_localDebugEnabled = ($aux == 'on');

		$aux = strtolower(trim($this->getVariable($input, 'editable')));
		$this->_editableFlaged = ($aux == 'on');
	}
	/**
	 * @todo doc
	 */
	protected function loadXMLData() {
		if($this->_xmlData === false) {
			$this->_xmlData = simplexml_load_string($this->_data);
		}
	}
	/**
	 * @todo doc
	 * @param $msg @todo doc
	 * @return @todo doc
	 */
	protected function setLastError($msg="") {
		return $this->_x2wInstance->setLastError($msg);
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
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
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function showAsDirect() {
		return "<div class=\"Xml2Wiki_direct\">".htmlspecialchars($this->_data)."</div>";
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function showAsList() {
		$out = "";

		$this->setLastError();
		if($this->checkSimpleXML()) {
			$out = "<div class=\"Xml2Wiki_list\">\n";

			$this->loadXMLData();
			$out.= "\t<span class=\"MainItem\">".$this->translate($this->_xmlData->getName())."</span><ul>\n";

			if(count($this->_xmlData->children())) {
				foreach($this->_xmlData->children() as $child) {
					$out.= $this->showAsListChild($child);
				}
			}

			$out.= "\t</ul>\n";
			$out.= "</div>\n";
		} else {
			$out = $this->getLastError();
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param $child @todo doc
	 * @param $level @todo doc
	 * @param $space @todo doc
	 * @return @todo doc
	 */
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
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function showAsPre() {
		return "<div class=\"Xml2Wiki_pre\"><pre>".htmlspecialchars($this->_data)."</pre></div>";
	}
	/**
	 * @todo doc
	 * @return @todo doc
	 */
	protected function showAsTable() {
		$out = "";

		global	$wgUseAjax;
		global	$wgTitle;

		$this->setLastError();
		if($this->checkSimpleXML()) {
			$out.= "<div class=\"Xml2Wiki_table\">\n";
			$out.= "\t<table class=\"{$this->_class}\">\n";

			$this->loadXMLData();
			/*
			 * Analysing XML structure and items.
			 * @{
			 */
			$xmlSt = buildXMLStruct($this->_xmlData, null, null, true, true);
			buildXMLStructSpan($xmlSt);
			/* @} */
			$out.= "\t\t<tr>\n";
			$out.= "\t\t\t<th colspan=\"{$xmlSt['stat']['columns']}\">".$this->translate($this->_xmlData->getName())."</th>\n";
			$out.= "\t\t</tr>\n";
			for($y=1; $y<=$xmlSt['stat']['rows']; $y++) {
				$out.= "\t\t<tr>\n";
				for($x=1; $x<=$xmlSt['stat']['columns']; $x++) {
					if(isset($xmlSt['stat']['list'][$y][$x])) {
						$id    = "{$y}-{$x}";
						$value = $xmlSt['stat']['list'][$y][$x];
						if(isset($value['tag'])) {
							$text = $this->translate($value['tag']);
							$out.= "\t\t\t<th colspan=\"{$value['colspan']}\" rowspan=\"{$value['rowspan']}\">{$text}</th>\n";
						} elseif(isset($value['text'])) {
							if($this->isEditable()) {
								$itemId = "item_".X2WParser::$_ParserId."_{$x}_{$y}";
								$out.= "\t\t\t<td class=\"Editable\" colspan=\"{$value['colspan']}\" rowspan=\"{$value['rowspan']}\" id=\"{$itemId}\" onKeyDown=\"X2WKeyDown()/*X2WKeyDown('{$itemId}')*/\" onClick=\"X2WEditValue('{$this->_filename}','{$itemId}','".$wgTitle->getArticleID()."',".($this->debugEnabled()?'true':'false').");\">{$value['text']}</td>\n";
							} else {
								$out.= "\t\t\t<td colspan=\"{$value['colspan']}\" rowspan=\"{$value['rowspan']}\">{$value['text']}</td>\n";
							}
						}
					}
				}
				$out.= "\t\t</tr>\n";
			}
			$out.= "\t</table>\n";
			$out.= "</div>\n";
		} else {
			$out = $this->getLastError();
		}

		return $out;
	}
	/**
	 * @todo doc
	 * @param $name @todo doc
	 * @param $isAttr @todo doc
	 * @return @todo doc
	 */
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

	/*
	 * Public class methods.
	 */
	/**
	 * @todo doc
	 * @param $params @todo doc
	 * @return @todo doc
	 */
	public static function AjaxParser($params) {
		$out = '';

		global	$wgXML2WikiConfig;
		Xml2Wiki::Instance();

		$params    = explode($wgXML2WikiConfig['ajaxseparator'], $params);
		$xml       = $params[0];
		$value     = $params[1];
		$oldValue  = $params[2];
		$position  = $params[3];
		$articleID = $params[4];
		$debug     = $params[5];

		$out = $oldValue;

		if($value != $oldValue) {
			$conf = array(
				'file'	=> $xml,
				'debug'	=> $debug,
			);
			/*
			 *	- class
			 *	- file
			 *	- translator
			 *	- style
			 *	- showattrs
			 *	- class
			 *	- debug
			 */
			$x2wParser = new X2WParser();
			$x2wParser->setLastError();
			$x2wParser->loadFromList($conf);
			if($x2wParser->getLastError()) {
				if($x2wParser->debugEnabled()) {
					$out = $x2wParser->getLastError().'<br/>'.$oldValue;
				} else {
					$out = $oldValue;
				}
			} else {
				$conf['full_path'] = $x2wParser->getFilePath($xml);

				$x2wParser->loadXMLData();
				/*
				 * Analysing XML structure and items.
				 * @{
				 */
				$aux        = explode("_", $position);
				$xyPosition = array(
					'x' => $aux[2],  
					'y' => $aux[3],  
				);
				$xmlSt = buildXMLStruct($x2wParser->_xmlData, $xyPosition, $value, false);
				if(!$x2wParser->_xmlData->asXML($conf['full_path'])) {
					$out = $x2wParser->formatErrorMessage(wfMsg('forbbideneditfile', $conf['file'])).'<br/>'.$oldValue;
				} else {
					/*
					 * GhostBuster's Code: This piece of code
					 * avoids some unpleasant results when
					 * cache is enabled.
					 * @{
					 */
					$title = Title::newFromID($articleID, GAID_FOR_UPDATE);
					$title->invalidateCache();
					/* @} */

					$out = $value;
				}
			}
		}

		return $out;
	}
}

?>