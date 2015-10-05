<?php
if ( ! is_admin() ) { die( 'Access denied.' ); }

$backupSerial = pb_backupbuddy::_POST( 'serial' );
$profileID = pb_backupbuddy::_POST( 'profileID' );

if ( '0' == pb_backupbuddy::_POST( 'step' ) ) {
	$backupFiles = glob( backupbuddy_core::getBackupDirectory() . 'backup*' . $backupSerial . '*.zip' );
	if ( ! is_array( $backupFiles ) ) { $backupFiles = array(); }
	if ( count( $backupFiles ) > 0 ) {
		$backupFile = $backupFiles[0];
		die( json_encode( array(
			'statusStep' => 'backupComplete',
			'stepTitle' => 'Backup finished. File: ' . $backupFile . ' -- Next step start sending the file chunks to remote API server via curl.',
			'nextStep' => '-1', // Finished.
		) ) );
	}

	$lastBackupStats = backupbuddy_api::getLatestBackupStats();
	if ( $backupSerial != $lastBackupStats['serial'] ) {
		die( json_encode( array( 'statusStep' => 'Waiting for backup to begin.' ) ) );
	} else { // Last backup stats is our deploy backup.
		die( json_encode( array(
			'stepTitle' => $lastBackupStats['processStepTitle'] . ' with profile "' . pb_backupbuddy::$options['profiles'][ $profileID ]['title'] . '".',
			'statusStep' => 'backupStats',
			'stats' => $lastBackupStats,
		) ) );
		
	}

}