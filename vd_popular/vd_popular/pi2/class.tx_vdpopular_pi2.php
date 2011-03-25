<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Cedric Aellen <support.typo3@vd.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib . 'class.t3lib_pagetree.php');


/**
 * Plugin 'Popular page list' for the 'vd_popular' extension.
 *
 * @author	Cedric Aellen <support.typo3@vd.ch>
 * @package	TYPO3
 * @subpackage	tx_vdpopular
 */
class tx_vdpopular_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_vdpopular_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_vdpopular_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'vd_popular';	// The extension key.

	var $table = 'tx_vdpopular_counter';

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		/*
		 * Flexform
		 */

		// Init flexform configuration of the plugin
		$this->pi_initPIflexForm();


		/*
		 * Configuration TS
		 */

		//get number of page to show
		$this->fetchConfigurationTSFF('pagesToShow');
		//get exclude pages
		$this->fetchConfigurationTSFF('excludedPages');
		//get number of page to show
		$this->fetchConfigurationTSFF('depth');
		//get minimum visits
		$this->fetchConfigurationTSFF('minVisits');
		//get template file
		$this->fetchConfigurationTSFF('templateFile');
		// get extension confArr
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['vd_popular']);

		/*
		 * Variables
		 */

		//get startinPoints pages
		$pidList = $this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']);
		
		//day to keep statistics
		$olderThan = intval($this->extConf['olderThan']);
		// security to avoid DB explosion. Max statistics day is 30 days.
		if ($olderThan > 30) {
			$olderThan = 30;
		} 		

		/*
		 * Function calls
		 */

		// all pages of the tree
		$pages = $this->getTree($pidList);
		// all pages of the tree without excluded pages
		$withoutExcludedPages = $this->withoutExcludedPages($pages);
		// visited pages of the tree without excluded pages
		$visitedPages = $this->getVisitedPages($withoutExcludedPages);
		// view
		$content.= $this->viewCounterResult($visitedPages);
		// delete old records
		$this->deleteOldRecords($olderThan);
		
		/*
		 * Returns
		 */

		return $content;
	}

	/**
	 * Returns the child page
	 *
	 * @param unknown_type $pidList
	 * @return An array of page ID
	 */
	function getTree($pidList){

		$finalTree = array();

		//check if starting point is empty
		if (!$pidList) {
			return $finalTree;
		}

		$startingPoints = explode(',',$pidList);
		foreach ($startingPoints as $startingPoint) {

			//get startingpoint
			$startingPointRecord = t3lib_BEfunc::getRecord('pages', $startingPoint);

			//get childrens
			$tree = t3lib_div::makeInstance('t3lib_pageTree');
			$tree->init(t3lib_BEfunc::deleteClause('pages'));

			//add nav title
			$tree->addField('nav_title',1);

			//get value of the Typoscript
			$depth = intval($this->conf['depth']);
			$c = $tree->getTree($startingPoint, $depth, '');

			//ajout du startingPoint dans le tree
			$temp['row'] = $startingPointRecord;
			$tree->tree[] = $temp;
			$finalTree = array_merge ($finalTree,$tree->tree);
		}
		return $finalTree;

	}

	/**
	 * Removes deleted pages by the plugin configuration from a liste of page IDs
	 *
	 * @param $pages
	 * @return An array of page ID
	 */
	function withoutExcludedPages($pages) {

		// if no page to exclude
		if (!$this->conf['excludedPages']) {
			return $pages;
		}

		$excludedpages = explode(',', $this->conf['excludedPages']);

		foreach ($pages as $key => $page){
			if (in_array($page['row']['uid'],$excludedpages)) {
				unset ($pages[$key]);
			}
		}

		return $pages;
	}

	/**
	 * Returns the IDs of the most visited pages order per visit
	 *
	 * @param unknown_type $pages
	 * @param unknown_type $minCounter
	 * @returns An array of page ID
	 */
	function getVisitedPages($pages, $minCounter=0){

		$visitedPages = array();
		$counters = array();

		//tableau qu'avec les IDs
		foreach ($pages as $page) {
			$pagesId[] = $page['row']['uid'];
		}

		// minVisits to be popular
		$minCounter = intval($this->conf['minVisits']);

		// TS
		$pagestoshow = intval($this->conf['pagesToShow']);

		//hard-coded security to avoid to view > 100 pages
		//if the value change, don t forget to change the max value in static/default_template/constants.txt and flexform_ds_pi2.xml
		$pagestoshow = $pagestoshow > 100 ? 100 : $pagestoshow;

		$countPagetoShow = 0;


		unset ($counters);
		$counters = $this->getRecordsCounter($minCounter);

		// if no result
		if (count($counters) <= 0) {
			$stop = true;
		}

		// Populate $visitedPages array
		// If there is no data, it returns an empty array
		foreach ($counters as $counter) {
			if (in_array($counter['pid'],$pagesId)) {
				$countPagetoShow++;
				$visitedPages[]=$counter;
			}

			if ($countPagetoShow >= $pagestoshow){
				$stop = true;
				break;
			}
		} // end foreach

		return $visitedPages;
	}

	/**
	 * Returns all the pages their visit counter and description
	 *
	 * @param $minCounter
	 */
	function getRecordsCounter($minCounter = 1){

		$counters = array();

		// query to get the list of page
		$fields		= 'p.uid, p.title, p.description ,vdpop1.pid, vdpop1.totalCounter';
		$table 		= 'pages AS p,
						(SELECT vdpop.pid, SUM(vdpop.counter) AS totalCounter FROM tx_vdpopular_counter vdpop GROUP BY vdpop.pid HAVING SUM(vdpop.counter) > '.$minCounter.') 
						AS vdpop1';
		$where 		= 'p.uid = vdpop1.pid';
		$groupBy 	= '';
		$orderBy 	= 'vdpop1.totalCounter DESC, vdpop1.pid ASC';
		$limit 		= '';

		$counterRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy,$orderBy,$limit);

		if ($counterRes == true) {
			// retrieves the entire array
			while($row = mysql_fetch_assoc($counterRes)){
				$counters[]=$row;
			}
		}
			
		return $counters;
	}

	/**
	 * View results
	 *
	 * @param $result
	 * @return HTML ordered list <ol>
	 */
	function viewCounterResult($results) {
		
		// Get template content
		$templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		
		// Extract surounded by ###LIST###
		$resultsList = 	$this->cObj->getSubpart($templateCode,'###LIST###');
		
		// Extract parts surounded by ###ITEM###
		$resultItem = 	$this->cObj->getSubpart($templateCode,'###ITEM###');
		$fieldList = '';
		
		if (sizeof($results) <= 0) {
			$fieldList = "<li>no records</li>";
		} else {
		
			// Loop
			foreach ($results as $row => $value) {
				$fieldList .= 	$this->cObj->substituteMarkerArray (
									$resultItem, 
									array(
										'###PAGE###' 			=> ($this->cObj->getTypoLink('',$value["pid"])),
										'###COUNTER###' 		=> htmlspecialchars($value['totalCounter']),
										'###DESCRIPTION###'		=> htmlspecialchars($value['description'])
									)
								);
			}
		} // else [end]		
		
		$output = $this->cObj->substituteSubpart($resultsList, '###ITEM###', $fieldList);
		
		return $output;
	}

	/**
	 * Delete records older than a certain number of days
	 *
	 * @param int $olderThan
	 * @return nothing
	 */
	protected function deleteOldRecords($olderThan) {

		// set in millisec.
		$olderThan = time() - ($olderThan * 86400);

		$res = $GLOBALS['TYPO3_DB']->sql_query('
				DELETE FROM tx_vdpopular_counter 
				WHERE crdate < '.$olderThan.';'
				);
	}


	/**
	 * Fetches configuration value given its name. Merges flexform and TS configuration values.
	 *
	 * @param	string	$param	Configuration value name
	 * @return	string	Parameter value
	 */
	function fetchConfigurationTSFF($param, $sheet='sDEF') {
		$value = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], $param, $sheet),"'");
		if(isset($this->conf[$param]) && isset($value)){
			$value = $value ? $value : $this->conf[$param];
			$this->conf[$param] = $value;
		}
		return $value;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/pi2/class.tx_vdpopular_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/pi2/class.tx_vdpopular_pi2.php']);
}

?>