<?php
/**
 * @file xml2wiki-dr.i18n.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 */

$messages = array();
$messages['en'] = array(
	'badtxml'		=> 'Bad formed translation XML',
	'badtxml-unknowntag'	=> 'Bad formed translation XML. Unknown tag \'$1\'',
	'forbbidenfile'		=> 'Unable to read path \'$1\'',
	'forbbidenwfile'	=> 'Unable to read wiki-file \'$1\'',
	'nofilename'		=> 'Filename not specified',
	'notallowedpath'	=> 'Path \'$1\' is not allowed. Please check variable \$wgXML2WikiAllowdPaths in your system configuration',
	'notyetsupported'	=> 'We sorry, this feature is not supported, yet',
	'simplexml-required'	=> 'Module SimpleXML is required (<a target="_blank" href="extensions/xml2wiki-dr/xml2wiki-dr.php?modules=SimpleXML">check module status</a>)',
	'stylecode-extension'	=> 'Style \'code\' requires <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'unknown-style'		=> 'Unknown style \'$1\'',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.<sup>[{{SERVER}}{{SCRIPTPATH}}/extensions/xml2wiki-dr/xml2wiki-dr.php?info more]</sup>',
);
$messages['es'] = array(
	'badtxml'		=> 'XML de traducciones mal formado',
	'badtxml-unknowntag'	=> 'XML de traducciones mal formado. Identificador \'$1\' desconocido',
	'forbbidenfile'		=> 'No es posible leer la ruta \'$1\'',
	'forbbidenwfile'	=> 'No es posible leer el archivo-wiki \'$1\'',
	'nofilename'		=> 'Nombre de archivo no especificado',
	'notallowedpath'	=> 'La ruta \'$1\' no está permitida. Controle la variable \$wgXML2WikiAllowdPaths en su configuración de sistema',
	'notyetsupported'	=> 'Lo sentimos, esta característica no esta soportada, aún',
	'simplexml-required'	=> 'El módulo SimpleXML es requerido (<a target="_blank" href="extensions/xml2wiki-dr/xml2wiki-dr.php?modules=SimpleXML">ver el estado del módulo</a>)',
	'stylecode-extension'	=> 'El stilo \'code\' requiere <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'unknown-style'		=> 'Estilo \'$1\' desconocido',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provee los identificadores <tt>&lt;xml2wiki&gt;</tt> y <tt>&lt;/xml2wiki&gt;</tt>.<sup>[{{SERVER}}{{SCRIPTPATH}}/extensions/xml2wiki-dr/xml2wiki-dr.php?info más]</sup>',
);
?>