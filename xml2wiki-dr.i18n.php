<?php
/**
 * @file xml2wiki-dr.i18n.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 */
//$obj = wfFindFile(Title::makeTitle(NS_SPECIAL, 'Upload'));
//if($obj) {
//	$out = $wgUploadDirectory.DIRECTORY_SEPARATOR.$obj->getRel();
//}
//echo '<pre>';
//var_dump(Title::makeTitle(NS_SPECIAL, 'Upload')->escapeLocalUrl("wpDestFile="));
//die;

$messages = array();
$messages['en'] = array(
	'badtxml'		=> 'Bad formed translation XML',
	'badtxml-unknowntag'	=> 'Bad formed translation XML. Unknown tag \'$1\'',
	'forbbidenfile'		=> 'Unable to read path \'$1\'',
	'forbbidenwfile'	=> 'Unable to read wiki-file \'$1\' (<a href="$2">upload it</a>)',
	'nofilename'		=> 'Filename not specified',
	'notallowedpath'	=> 'Path \'$1\' is not allowed. Please check variable \$wgXML2WikiAllowdPaths in your system configuration',
	'notyetsupported'	=> 'We sorry, this feature is not supported, yet',
	'simplexml-required'	=> 'Module SimpleXML is required ([[Special:Xml2wiki#Modules|check module status]])',
	'stylecode-extension'	=> 'Style \'code\' requires <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'unknown-style'		=> 'Unknown style \'$1\'',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags.<sup>[[Special:Xml2Wiki|more]]</sup>',
	'xml2wiki'		=> 'XML2Wiki',

	'sinfo-allowed-paths'		=> 'Allowed Paths',
	'sinfo-author'			=> 'Author',
	'sinfo-description'		=> 'Description',
	'sinfo-extension-information'	=> 'Extension Information',
	'sinfo-information-disabled'	=> 'We sorry, this information is disabled',
	'sinfo-installation-directory'	=> 'Installation Directory',
	'sinfo-is-installed'		=> 'is installed',
	'sinfo-is-installed-tag'	=> 'is installed (tag \'\'\'&lt;$1&gt;\'\'\')',
	'sinfo-links'			=> 'Links',
	'sinfo-modules'			=> 'Modules',
	'sinfo-name'			=> 'Name',
	'sinfo-not-installed'		=> 'is NOT installed',
	'sinfo-php-version'		=> 'Current PHP version',
	'sinfo-required-extensions'	=> 'Required Extensions',
	'sinfo-system-information'	=> 'System Information',
	'sinfo-url'			=> 'URL',
	'sinfo-version'			=> 'Version',
);
$messages['es'] = array(
	'badtxml'		=> 'XML de traducciones mal formado',
	'badtxml-unknowntag'	=> 'XML de traducciones mal formado. Identificador \'$1\' desconocido',
	'forbbidenfile'		=> 'No es posible leer la ruta \'$1\'',
	'forbbidenwfile'	=> 'No es posible leer el archivo-wiki \'$1\' (<a href="$2">subirlo</a>)',
	'nofilename'		=> 'Nombre de archivo no especificado',
	'notallowedpath'	=> 'La ruta \'$1\' no está permitida. Controle la variable \$wgXML2WikiAllowdPaths en su configuración de sistema',
	'notyetsupported'	=> 'Lo sentimos, esta característica no esta soportada, aún',
	'simplexml-required'	=> 'El módulo SimpleXML es requerido ([[Special:Xml2wiki#M.C3.B3dulos|ver el estado del módulo]])',
	'stylecode-extension'	=> 'El stilo \'code\' requiere <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'unknown-style'		=> 'Estilo \'$1\' desconocido',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provee los identificadores <tt>&lt;xml2wiki&gt;</tt> y <tt>&lt;/xml2wiki&gt;</tt>.<sup>[[Special:Xml2Wiki|more]]</sup>',
	'xml2wiki'		=> 'XML2Wiki',

	'sinfo-allowed-paths'		=> 'Rutas Permitidas',
	'sinfo-author'			=> 'Autor',
	'sinfo-description'		=> 'Descripción',
	'sinfo-extension-information'	=> 'Información de la Extensión',
	'sinfo-information-disabled'	=> 'Lo sentimos, esta imformanción se encuentra deshabilitada.',
	'sinfo-installation-directory'	=> 'Directorio de Installación',
	'sinfo-is-installed'		=> 'está instalado',
	'sinfo-is-installed-tag'	=> 'está instalado (identificador \'\'\'&lt;$1&gt;\'\'\')',
	'sinfo-links'			=> 'Enlaces',
	'sinfo-modules'			=> 'Módulos',
	'sinfo-name'			=> 'Nombre',
	'sinfo-not-installed'		=> 'NO está instalado',
	'sinfo-php-version'		=> 'Versión Actual de PHP',
	'sinfo-required-extensions'	=> 'Extensiones Requeridas',
	'sinfo-system-information'	=> 'Información del Sistema',
	'sinfo-url'			=> 'URL',
	'sinfo-version'			=> 'Versión',
);
?>