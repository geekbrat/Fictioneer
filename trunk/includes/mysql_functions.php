<?php

// Some DB functions 

if(!function_exists("dbconnect")) { // just in case.  Some people seemed to be having an issue and this was the easiest fix.

function dbconnect($dbhost, $dbuser, $dbpass, $dbname ) {
	$mysql_access = mysql_connect($dbhost, $dbuser, $dbpass);
	if(!$mysql_access) {
		include(_BASEDIR."languages/en.php");
		die(_FATALERROR." "._NOTCONNECTED);
	}
	mysql_select_db($dbname, $mysql_access);
	return $mysql_access;
}


function dbquery($query) {
	global $debug, $headerSent, $dbconnect;
	if($debug  && $headerSent) echo "<!-- $query -->\n";
	$result = mysql_query($query, $dbconnect) or accessDenied( _FATALERROR.(isADMIN ? "Query: ".$query."<br />Error: (".mysql_errno( ).") ".mysql_error( ) : ""));
	return $result;
}

function dbnumrows($query) {
	global $debug, $dbconnect;
	if ($query === false && mysql_errno( ) > 0 && $debug) {
		echo "<!-- dbnumrows ".mysql_error( )." -->\n";
	}
	$query = mysql_num_rows($query);
	return $query;
}

function dbassoc($query) {
	global $debug, $dbconnect;
	if ($query === false && mysql_errno( ) > 0 && $debug) {
		echo "<!-- dbassoc ".mysql_error( )." -->\n";
	}
	$query = mysql_fetch_assoc($query);
	return $query;
}

function dbinsertid($tablename = 0) {
	return mysql_insert_id( );
}

function dbrow($query) {
	global $debug, $dbconnect;
	if ($query === false && mysql_errno( ) > 0 && $debug) {
		if($error) echo "<!-- dbrow ".mysql_error( )." -->\n";
	}
	$query = mysql_fetch_row($query);
	return $query;
}

// Used to escape text being put into the database.
function escapestring($str) {
   if(version_compare(phpversion(),"4.3.0")=="-1") {
     $str = mysql_escape_string($str);
   } else {
     $str = mysql_real_escape_string($str);
   }
   return $str;
}

function dbclose( ) {
	global $dbconnect;
	mysql_close($dbconnect);

}
// End DB functions
}
?>