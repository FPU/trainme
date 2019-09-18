<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodleTM';
$CFG->dbuser    = 'backup';
$CFG->dbpass    = '$$backup$$';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbsocket' => 0,
);


if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
	$CFG->wwwroot   = 'https://trainme.fresno.edu'; 
}else{
	$CFG->wwwroot   = 'http://trainme.fresno.edu';
}
$CFG->dataroot  = 'D:\\\\moodledata';
$CFG->admin     = 'admin';

// ********************** This is the debug section ************************
// Comment these out when not in use
//$CFG->debug = 6143;  
//$CFG->debugdisplay = 1;

// Using a tool like phpMyAdmin, execute the following SQL commands:  

//UPDATE mdl_config SET VALUE = 2047 WHERE name = 'debug'; 
//UPDATE mdl_config SET VALUE = 1 WHERE name = 'debugdisplay'; 

//To turn it back off, use the admin screens, or the commands:  

//UPDATE mdl_config SET VALUE = 0 WHERE name = 'debug'; 
//UPDATE mdl_config SET VALUE = 0 WHERE name = 'debugdisplay';
// ********************** End of Debug Section *********************************

$CFG->directorypermissions = 0777;

$CFG->passwordsaltmain = '}2JsXL}g9`[FEz)r)da>qBEjm!';

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!