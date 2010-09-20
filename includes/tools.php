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

/**
 * @todo doc
 * @param $xml @todo doc
 * @param $find @todo doc
 * @param $replace @todo doc
 * @param $noelement @todo doc
 * @param $list @todo doc
 * @return @todo doc
 */
function buildXMLStruct(&$xml, array $find=null, $replace=null, $noelement=true, $list=false) {
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
/**
 * @todo doc
 * @param $xml @todo doc
 * @param $auxSt @todo doc
 * @return @todo doc
 */
function buildStructDig(&$xml, &$auxSt) {
	if(!isset($auxSt['found'])) {
		/*
		 * Creating item structure.
		 */
		$st = array(
			'tag'		=> $xml->getName(),
			'x'		=> $auxSt['x'],
			'y'		=> $auxSt['y'],
			'children'	=> array(),
		);
		/*
		 * Attaching xml-element.
		 */
		if(!isset($auxSt['noelement'])) {
			$st['element'] = &$xml;
		}
		/*
		 * Build items list (auxilliar structure).
		 */
		if($auxSt['list'] !== false) {
			if(!isset($auxSt['list'][$st['y']])) {
				$auxSt['list'][$st['y']] = array();
			}
			$auxSt['list'][$st['y']][$st['x']] = array(
				'tag' => $st['tag'],
			);

		}
		/*
		 * Check if it is the item that is been looking for.
		 */
		if($auxSt['find'] != null) {
			if($st['x'] == $auxSt['find']['x'] && $st['y'] == $auxSt['find']['y']) {
				$auxSt['found'] = true;
			}
		}

		$auxSt['x']++;

		/*
		 * Follow every child.
		 */
		$first = true;
		foreach($xml->children() as $c) {
			if($first) {
				$first = false;
			} else {
				$auxSt['y']++;
			}
			$st['children'][] = buildStructDig($c, $auxSt);
		}

		if(!count($st['children'])) {
			$text = trim(''.$xml);
			if($text) {
				/*
				 * Create text child.
				 */
				$st['children'][] = array(
					'x'	=> $auxSt['x'],
					'y'	=> $st['y'],
					'text'	=> $text,
				);
				/*
				 * Build items list (auxilliar structure).
				 */
				if($auxSt['list'] !== false) {
					if(!isset($auxSt['list'][$st['children'][0]['y']])) {
						$auxSt['list'][$st['children'][0]['y']] = array();
					}
					$auxSt['list'][$st['children'][0]['y']][$st['children'][0]['x']] = array(
						'text' => $st['children'][0]['text'],
					);
				}
				/*
				 * Check if it is the item that is been looking for.
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
				/*
				 * Recalculate columns.
				 */
				if($auxSt['columns'] < ($auxSt['x'])) {
					$auxSt['columns'] = $auxSt['x'];
				}
			}
		} else {
			/*
			 * Recalculate columns.
			 */
			if($auxSt['columns'] < ($auxSt['x'] - 1)) {
				$auxSt['columns'] = $auxSt['x'] - 1;
			}
		}

		$auxSt['x']--;

		/*
		 * Recalculate rows.
		 */
		if($auxSt['rows'] < ($auxSt['y'])) {
			$auxSt['rows'] = $auxSt['y'];
		}

		return $st;
	} else {
		return null;
	}
}
/**
 * @todo doc
 * @param $st @todo doc
 */
function buildXMLStructSpan(&$st) {
	$cols = $st['stat']['columns'];
	$rows = $st['stat']['rows'];
	$list = &$st['stat']['list'];
	/*
	 * Removing master-tag's information.
	 */
	for($x=0; $x<=$cols; $x++) {
		if(isset($list[$x][0])) {
			unset($list[$x][0]);
		}
	}
	/*
	 * Checking every item.
	 */
	for($y=$rows; $y>0; $y--) {
		for($x=1; $x<=$cols; $x++) {
			if(isset($list[$y][$x]) && !isset($list[$y][$x]['dummy'])) {
				/*
				 * Calculating colspan.
				 */
				$list[$y][$x]['colspan'] = 1;
				if(!isset($list[$y][$x+1])) {
					/*
					 * Filling with dummy item to the right.
					 * Needed to calculate rowspans.
					 */
					for($_x=$x+1; $_x<=$cols; $_x++) {
						$list[$y][$_x]['dummy'] = true;
						$list[$y][$x]['colspan']++;
					}
				}

				/*
				 * Calculating rowspan.
				 */
				$list[$y][$x]['rowspan'] = 1;
				for($_y=$y+1; $_y<=$rows; $_y++) {
					if(isset($list[$_y][$x])) {
						break;
					} else {
						$list[$y][$x]['rowspan']++;
					}
				}
			}
		}
	}
}

?>