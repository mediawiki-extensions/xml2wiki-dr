<?php
/**
 * @file xml2wiki-dr.i18n.php
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

$messages = array();
$messages['en'] = array(
	'badtxml'		=> 'Bad formed translation XML',
	'badtxml-unknowntag'	=> 'Bad formed translation XML. Unknown tag \'$1\'',
	'directories'		=> 'Directories',
	'disabled'		=> 'Disabled',
	'enabled'		=> 'Enabled',
	'files'			=> 'Files',
	'forbbidenfile'		=> 'Unable to read path \'$1\'',
	'forbbidenwfile'	=> 'Unable to read wiki-file \'$1\' (<a href="$2">upload it</a>)',
	'magicword'		=> 'MagicWord \'$1\'',
	'noaccess'		=> 'Inaccessible',
	'nofilename'		=> 'Filename not specified',
	'notallowedpath'	=> 'Path \'$1\' is not allowed. Please check variable $wgXML2WikiAllowdPaths in your system configuration',
	'not-present'		=> 'not present',
	'notyetsupported'	=> 'We sorry, this feature is not supported, yet',
	'present'		=> 'present',
	'simplexml-required'	=> 'Module SimpleXML is required ([[Special:Xml2wiki#Modules|check module status]])',
	'stylecode-extension2'	=> 'Style \'code\' requires [http://www.mediawiki.org/wiki/Extension:SyntaxHighlight SyntaxHighlight Extension]',
	'stylecode-extension'	=> 'Style \'code\' requires <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'tag'			=> 'Tag \'$1\'',
	'unknown-style'		=> 'Unknown style \'$1\'',
	'unknown'		=> 'Unknown',
	'x2w-default'		=> 'Unknown subcommand \'$1\'',
	'x2w-load-duplicated-id'=> 'The ID \'$1\' is already used',
	'x2w-load-no-id'	=> 'There is no XML loaded with ID \'$1\'',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provides <tt>&lt;xml2wiki&gt;</tt> and <tt>&lt;/xml2wiki&gt;</tt> tags and MagicWord #x2w.<sup>[[Special:Xml2Wiki|more]]</sup>',
	'xml2wiki'		=> 'XML2Wiki',
	'xml-noparsing'		=> 'Unable to parse XML \'$1\'',

	'sinfo-allowed-paths'		=> 'Allowed Paths',
	'sinfo-allowedpathsrecursive'	=> 'Recursive allowed paths',
	'sinfo-allownocache'		=> 'Disable cache',
	'sinfo-attributes'		=> 'Attributes',
	'sinfo-author'			=> 'Author',
	'sinfo-configs'			=> 'Configuration',
	'sinfo-description'		=> 'Description',
	'sinfo-extension-information'	=> 'Extension Information',
	'sinfo-information-disabled'	=> 'We sorry, this information is disabled',
	'sinfo-installation-directory'	=> 'Installation Directory',
	'sinfo-is-installed'		=> 'is installed',
	'sinfo-is-installed-tag'	=> 'is installed (tag \'\'\'&lt;$1&gt;\'\'\')',
	'sinfo-links'			=> 'Links',
	'sinfo-modules'			=> 'Modules',
	'sinfo-name'			=> 'Name',
	'sinfo-normal'			=> 'Normal',
	'sinfo-not-installed'		=> 'is NOT installed',
	'sinfo-permissions'		=> 'Permissions',
	'sinfo-php-version'		=> 'Current PHP version',
	'sinfo-prefix'			=> 'Prefix',
	'sinfo-required-extensions'	=> 'Required Extensions',
	'sinfo-showallowpaths'		=> 'Show allowed paths',
	'sinfo-showinstalldir'		=> 'Show installation directory',
	'sinfo-showmodules'		=> 'Show modules',
	'sinfo-showsysinfo'		=> 'Show system information',
	'sinfo-status'			=> 'Extension Status',
	'sinfo-suffix'			=> 'Suffix',
	'sinfo-svn-date'		=> 'Last changed date',
	'sinfo-svn-revision'		=> 'Last changed revision',
	'sinfo-svn'			=> 'Subversion',
	'sinfo-system-information'	=> 'System Information',
	'sinfo-translated'		=> 'Translated',
	'sinfo-url'			=> 'URL',
	'sinfo-version'			=> 'Version',
	'sinfo-xml2wiki-desc'		=> 'XML to Wiki \'\'special page\'\'. Visit [[Special:Xml2Wiki]]',
);
$messages['es'] = array(
	'badtxml-unknowntag'	=> 'XML de traducciones mal formado. Identificador \'$1\' desconocido',
	'badtxml'		=> 'XML de traducciones mal formado',
	'directories'		=> 'Directorios',
	'disabled'		=> 'Deshabilitado',
	'enabled'		=> 'Habilitado',
	'files'			=> 'Archivos',
	'forbbidenfile'		=> 'No es posible leer la ruta \'$1\'',
	'forbbidenwfile'	=> 'No es posible leer el archivo-wiki \'$1\' (<a href="$2">subirlo</a>)',
	'magicword'		=> 'PalabraMagica \'$1\'',
	'noaccess'		=> 'Inaccesible',
	'nofilename'		=> 'Nombre de archivo no especificado',
	'notallowedpath'	=> 'La ruta \'$1\' no está permitida. Controle la variable $wgXML2WikiAllowdPaths en su configuración de sistema',
	'not-present'		=> 'ausente',
	'notyetsupported'	=> 'Lo sentimos, esta característica no esta soportada, aún',
	'present'		=> 'presente',
	'simplexml-required'	=> 'El módulo SimpleXML es requerido ([[Special:Xml2wiki#M.C3.B3dulos|ver el estado del módulo]])',
	'stylecode-extension2'	=> 'El stilo \'code\' requiere [http://www.mediawiki.org/wiki/Extension:SyntaxHighlight" SyntaxHighlight Extension]',
	'stylecode-extension'	=> 'El stilo \'code\' requiere <a target="_blank" href="http://www.mediawiki.org/wiki/Extension:SyntaxHighlight">SyntaxHighlight Extension</a>',
	'tag'			=> 'Etiqueta \'$1\'',
	'unknown'		=> 'Desconocido',
	'unknown-style'		=> 'Estilo \'$1\' desconocido',
	'x2w-default'		=> 'Subcomando \'$1\' desconocido',
	'x2w-load-duplicated-id'=> 'El ID \'$1\' ya se encuentra en uso.',
	'x2w-load-no-id'	=> 'No hay un XML cargado con el ID \'$1\'',
	'xml2wiki-desc'		=> 'XML to Wiki<br/>Provee los identificadores <tt>&lt;xml2wiki&gt;</tt> y <tt>&lt;/xml2wiki&gt;</tt> y la PalabraMágica #x2w.<sup>[[Special:Xml2Wiki|more]]</sup>',
	'xml2wiki'		=> 'XML2Wiki',
	'xml-noparsing'		=> 'No se puede descomponer el XML \'$1\'',

	'sinfo-allowedpathsrecursive'	=> 'Recursive allowed paths',
	'sinfo-allowed-paths'		=> 'Rutas Permitidas',
	'sinfo-allownocache'		=> 'Deshabilitar cache',
	'sinfo-attributes'		=> 'Atributos',
	'sinfo-author'			=> 'Autor',
	'sinfo-configs'			=> 'Configuración',
	'sinfo-description'		=> 'Descripción',
	'sinfo-extension-information'	=> 'Información de la Extensión',
	'sinfo-information-disabled'	=> 'Lo sentimos, esta imformanción se encuentra deshabilitada.',
	'sinfo-installation-directory'	=> 'Directorio de Installación',
	'sinfo-is-installed'		=> 'está instalado',
	'sinfo-is-installed-tag'	=> 'está instalado (identificador \'\'\'&lt;$1&gt;\'\'\')',
	'sinfo-links'			=> 'Enlaces',
	'sinfo-modules'			=> 'Módulos',
	'sinfo-name'			=> 'Nombre',
	'sinfo-normal'			=> 'Normal',
	'sinfo-not-installed'		=> 'NO está instalado',
	'sinfo-permissions'		=> 'Permisos',
	'sinfo-php-version'		=> 'Versión Actual de PHP',
	'sinfo-prefix'			=> 'Prefijo',
	'sinfo-required-extensions'	=> 'Extensiones Requeridas',
	'sinfo-showallowpaths'		=> 'Mostrar rutas permitidas',
	'sinfo-showinstalldir'		=> 'Mostrar directorio de instalación',
	'sinfo-showmodules'		=> 'Mostrar módulos',
	'sinfo-showsysinfo'		=> 'Mostrar información de sistema',
	'sinfo-status'			=> 'Estado de la Extensión',
	'sinfo-suffix'			=> 'Sufijo',
	'sinfo-svn-date'		=> 'Última fecha de cambio',
	'sinfo-svn-revision'		=> 'Última número de revisión',
	'sinfo-svn'			=> 'Subversion',
	'sinfo-system-information'	=> 'Información del Sistema',
	'sinfo-translated'		=> 'Traducido',
	'sinfo-url'			=> 'URL',
	'sinfo-version'			=> 'Versión',
	'sinfo-xml2wiki-desc'		=> 'XML to Wiki \'\'página especial\'\'. Visite [[Special:Xml2Wiki]]',
);

?>