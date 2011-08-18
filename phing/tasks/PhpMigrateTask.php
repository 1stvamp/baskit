<?php
/**
 * PHP script migration task package for Phing
 *
 * @package Baskit
 * @version 1.0.0
 * @author Wes Mason <wes.mason@isotoma.com>
 * @copyright 2011 Isotoma Limited
 * @license http://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

require_once 'phing/tasks/ext/dbdeploy/DbDeployTask.php';

/**
 * PHP script migration task for Phing.
 * Based on the dbdeploy task from Phing core.
 *
 * @package Baskit
 * @uses DbDeployTask
 */
class PhpMigrateTask extends DbDeployTask {
    function main() {
	try {
	    // Suffix deltaset name with _php so we don't conflict with SQL migrations
	    $this->deltaSet .= '_php';

	    // Get correct DbmsSyntax object
	    $dbms = substr($this->url, 0, strpos($this->url, ':'));
	    $dbmsSyntaxFactory = new DbmsSyntaxFactory($dbms);
	    $this->dbmsSyntax = $dbmsSyntaxFactory->getDbmsSyntax();

	    // Figure out which revisions are in the db already
	    $this->appliedChangeNumbers = $this->getAppliedChangeNumbers();
	    $this->log('Current db revision: ' . $this->getLastChangeAppliedInDb());

	    // Generate sql file needed to take db to "lastChangeToApply" version
	    $deploySql = $this->doDeploy();
	    file_put_contents($this->outputFile . '.sql', $deploySql);
	    $undoSql = $this->undoDeploy();
	    file_put_contents($this->undoOutputFile . '.sql', $undoSql);

	    $lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();     
	    $files = $this->getDeltasFilesArray();
	    if (count($files)) {
		ksort($files);
		$calls = array(
		    'migrate' => array(),
		    'undo' => array(),
		);
		$output = array(
		    '#!/usr/bin/env php',
		    '<?php',
		    'set_include_path("' . get_include_path() . '");',
		);
		foreach($files as $fileChangeNumber => $fileName) {
                    if (strpos($fileName, '.php') != strlen($fileName)-4) {
                        // Skip non-php files
                        continue;
                    } else if ($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply) {
                        // Load file content
                        $contents = file_get_contents($this->dir . '/' . $fileName);
                        // Replace "function migrate(" with "function migrate_$fileChangeNumber("
                        $contents = preg_replace('/function\s+migrate\s*\(/', 'function migrate_' . $fileChangeNumber . '(', $contents);
                        // Replace "function undo(" with "function undo_$fileChangeNumber("
                        $contents = preg_replace('/function\s+undo\s*\(/', 'function undo_' . $fileChangeNumber . '(', $contents);
                        // Replace any instance of '<?php' with ''
                        $contents = str_replace('<?php', '', $contents);
                        /* Replace any instance of '?>' with '' */
                        $contents = str_replace('?>', '', $contents);
                        // Concat modified contents
                        $output[] = $contents;
                        $calls['migrate'][] = 'migrate_' . $fileChangeNumber . '();';
                        $calls['undo'][] = 'undo_' . $fileChangeNumber . '();';
                    }
		}
		// Generate single migrate() and undo() calls that reference above calls
		$migrate = "\nfunction migrate() {\n";
		$migrate .= implode("\n", $calls['migrate']);
		$migrate .= "}\n";
		$output[] = $migrate;
		$undo = "\nfunction undo() {\n";
		$undo .= implode("\n", $calls['undo']);
		$undo .= "}\n";
		$output[] = $undo;
		// Generate command line handler for migrate() and undo()
		$handler = <<< EOT
switch (\$argv[1]) {
case 'migrate':
    migrate();
    exit(0);
case 'undo':
    undo();
    exit(0);
default:
    echo 'No command specified, must be migrate or undo';
    exit(1);
}
EOT;
		$output[] = $handler;
		// Write file out
		file_put_contents($this->outputFile, implode("\n", $output));
		$this->log('Output PHP migration script to: ' . $this->outputFile);
	    }
        } catch (Exception $e){
            throw new BuildException($e);
        }
    }

    function doDeploy() {
        $sqlToPerformDeploy = '';
        $lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();     
        $files = $this->getDeltasFilesArray();
        if (count($files)) {
            ksort($files);
            foreach(array_keys($files) as $fileChangeNumber){
                if($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply){
                    $sqlToPerformDeploy .= 'INSERT INTO ' . parent::$TABLE_NAME . ' (change_number, delta_set, start_dt, applied_by, description, complete_dt)'.
                        ' VALUES ('. $fileChangeNumber .', \''. $this->deltaSet .'\', '. $this->dbmsSyntax->generateTimestamp() .', \'dbdeploy\', \''. $fileName .'\', '. $this->dbmsSyntax->generateTimestamp() .');' . "\n";
                }
            }
        }
        return $sqlToPerformDeploy;
    }
    
    function undoDeploy() {
        $sqlToPerformUndo = '';
        $lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();     
        $files = $this->getDeltasFilesArray();
        if (count($files)) {
            krsort($files);
            foreach(array_keys($files) as $fileChangeNumber){
                if($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply){
                    $sqlToPerformUndo .= 'DELETE FROM ' . DbDeployTask::$TABLE_NAME . ' WHERE change_number = ' . $fileChangeNumber . ' AND delta_set = \'' . $this->deltaSet . '\';' . "\n";
                }
            }
        }
        return $sqlToPerformUndo;
    }
}
