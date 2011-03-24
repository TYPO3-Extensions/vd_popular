<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_vdpopular_counter'] = array (
	'ctrl' => $TCA['tx_vdpopular_counter']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'counter'
	),
	'feInterface' => $TCA['tx_vdpopular_counter']['feInterface'],
	'columns' => array (
		'counter' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:vd_popular/locallang_db.xml:tx_vdpopular_counter.counter',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'counter;;;;1-1-1')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>