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


/**
 * Plugin 'Counter' for the 'vd_popular' extension.
 *
 * @author	Cedric Aellen <support.typo3@vd.ch>
 * @package	TYPO3
 * @subpackage	tx_vdpopular
 */
class tx_vdpopular_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_vdpopular_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_vdpopular_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'vd_popular';	// The extension key.

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)	{

		$this->conf = $conf;
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->updateCounter();
	}
	

	/**
	 * Visit counter per day
	 * have to be include by Typoscript in the template
	 * 
	 * 
	 */
	protected function updateCounter()
	{

		// uid current page
		$pid = intval($GLOBALS['TSFE']->id);
		$date = date(U);
		
		// not exclude pages
		if( in_array($pid, explode(',', $this->conf['excludePages']))==0 ) {

			// refresh statistic
			// one records per page per day
			$res = $GLOBALS['TYPO3_DB']->sql_query('
				UPDATE tx_vdpopular_counter 
				SET 	counter=counter+1,
					 	tstamp = '.date(U).'
				WHERE pid = '.$pid.'
				AND DATE_FORMAT(FROM_UNIXTIME(crdate,"%Y-%m-%d"), "%Y-%m-%d") = CURDATE();'
			);

			if (!$GLOBALS['TYPO3_DB']->sql_affected_rows($res)) {

				// insert into table tx_vdpopular_counter
				$field_values = array(
					'pid'			=>	$pid,
					'counter'		=> 	1,
					'tstamp'		=>	date(U),
					'crdate'		=>	date(U));
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_vdpopular_counter', $field_values);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/pi1/class.tx_vdpopular_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/pi1/class.tx_vdpopular_pi1.php']);
}

?>