<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Jean-Luc Thirot <jean-luc.thirot@vd.ch>
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

require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'stddb/tables.php');

if (!t3lib_extMgm::isLoaded('cms')) {
	require_once(t3lib_extMgm::extPath('cms', 'ext_tables.php'));
}

// the engine is here
require_once(t3lib_extMgm::extPath("vd_popular") . 'pi1/class.tx_vdpopular_pi1.php');


/**
 * Plugin 'Counter' via eid for the 'vd_popular' extension.
 *
 * @author    JL Thirot <support.typo3@vd.ch>
 * @package    TYPO3
 * @subpackage    tx_vdpopular
 */
class tx_vdpopular_eID {


	public function main() {
		$this->initDBObject();
		// run counter
		$this->runCounter();
	}

	private function initDBObject() {
		tslib_eidtools::connectDB();
	}

	private function runCounter() {
		// Get page to count
		$pageid = trim(t3lib_div::_GP('vdpopularpageid'));

		$counter = t3lib_div::makeInstance('tx_vdpopular_pi1');
		$counter->updateCounter($pageid);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/class.tx_vdpopular_eid.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/vd_popular/class.tx_vdpopular_eid.php']);
}

$SOBE = t3lib_div::makeInstance('tx_vdpopular_eID');
$SOBE->main();

?>