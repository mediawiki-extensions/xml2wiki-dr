<?php
/**
 * @file tools.php
 *
 * Subversion
 *	- ID:  $Id$
 *	- URL: $URL$
 *
 * @copyright 2010 Alejandro Darío Simi
 * @license GPL
 * @author Alejandro Darío Simi
 * @date 2010-08-26
 */

function buildXMLStruct(&$xml, $find=null, $replace=null, $noelement=true, $list=false) {
	$auxSt = array(
		'columns'	=> 0,
		'rows'		=> 0,
		'x'		=> 0,
		'y'		=> 1,
		'find'		=> (is_array($find)?$find:null),
		'replace'	=> (is_string($replace)?$replace:null),
		'list'		=> ($list?array():false),
	);
	if($auxSt['find'] === null) {
		$auxSt['replace'] = null;
	}
	if($noelement) {
		$auxSt['noelement'] = 'noelement';
		$auxSt['find']      = null;
		$auxSt['replace']   = null;
	}
	$st = buildStructDig($xml, $auxSt);

	return array(
		'tree'	=> $st,
		'stat'	=> $auxSt
	);
}
function buildStructDig(&$xml, &$auxSt) {
	if(!isset($auxSt['found'])) {
		$st = array(
			'tag'		=> $xml->getName(),
			'x'		=> $auxSt['x'],
			'y'		=> $auxSt['y'],
			'children'	=> array(),
		);
		if($auxSt['list'] !== false) {
			if(!isset($auxSt['list'][$st['y']])) {
				$auxSt['list'][$st['y']] = array();
			}
			$auxSt['list'][$st['y']][$st['x']] = array(
				'tag',
				$st['tag'],
			);
			
		}
		if(!isset($auxSt['noelement'])) {
			$st['element'] = &$xml;
		}

		if($auxSt['find'] != null) {
			if($st['x'] == $auxSt['find']['x'] && $st['y'] == $auxSt['find']['y']) {
				$auxSt['found'] = true;
			}
		}

		if($auxSt['x'] > $auxSt['columns']) {
			$auxSt['columns'] = $auxSt['x'];
		}

		//$childrenLenght = $xml->count();
		$childrenLenght = count($xml->xpath('./*'));
		if($childrenLenght) {
			$auxSt['x']++;
			$auxY = $auxSt['y'];
			foreach($xml->children() as $c) {
				$st['children'][] = buildStructDig($c, $auxSt);
				$auxSt['y']++;
			}
			$auxSt['x']--;
			$auxSt['y'] = $auxY + $childrenLenght - 1;
			if($auxSt['y'] > $auxSt['rows']) {
				$auxSt['rows'] = $auxSt['y'];
			}
		} else {
			$text = ''.$xml;
			if($text) {
				$st['children'][0] = array(
					'text'		=> $text,
					'x'		=> $auxSt['x']+1,
					'y'		=> $st['y'],
				);
				if($auxSt['list'] !== false) {
					if(!isset($auxSt['list'][$st['children'][0]['y']])) {
						$auxSt['list'][$st['children'][0]['y']] = array();
					}
					$auxSt['list'][$st['children'][0]['y']][$st['children'][0]['x']] = array(
						'text',
						$st['children'][0]['text'],
					);
				}

				/*
				 * Find check.
				 */
				if($auxSt['find'] != null) {
					if($st['children'][0]['x'] == $auxSt['find']['x']
					&& $st['children'][0]['y'] == $auxSt['find']['y']) {
						$auxSt['found'] = true;
						/*
						 * Replace check.
						 */
						if($auxSt['replace'] != null && isset($st['element'])) {
							$text = $auxSt['replace'];
							$st['children'][0]['text'] = $auxSt['replace'];

							$nodeName       = $st['element']->getName();
							$parentNode     = $st['element']->xpath("parent::*");
							$parentNode     = &$parentNode[0];
							$parentChildren = &$parentNode->xpath("./*");
							$node           = &$parentNode->xpath('./'.$nodeName);
							$node[0][0]     = $text;

							$auxSt['replaced'] = true;
						}
					}
				}
				if($auxSt['x']+1 > $auxSt['columns']) {
					$auxSt['columns'] = $auxSt['x'] + 1;
				}
			}
		}

		return $st;
	} else {
		return null;
	}
}

?>