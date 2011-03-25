<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
$TCA['tx_vdpopular_counter'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:vd_popular/locallang_db.xml:tx_vdpopular_counter',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_vdpopular_counter.gif',
	),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';


// Add plugin
t3lib_extMgm::addPlugin(array(
	'LLL:EXT:vd_popular/locallang_db.xml:tt_content.list_type_pi2',
	$_EXTKEY . '_pi2',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

// Add flexform field to plugin options
$TCA[ 'tt_content' ][ 'types' ][ 'list' ][ 'subtypes_addlist' ][ $_EXTKEY . '_pi2' ] = 'pi_flexform';

// Add flexform DataStructure
t3lib_extMgm::addPiFlexFormValue(
    $_EXTKEY . '_pi2',
    'FILE:EXT:' . $_EXTKEY . '/flexform_ds_pi2.xml'
);

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_vdpopular_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_vdpopular_pi2_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/default_template/', 'Default template');
?>