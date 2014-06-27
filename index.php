<?php
header('Content-type: text/txt');

list($group, $site, $major, $minor, $arch) = split(',', $_GET["build"]);
$config = loadConfig("fragments");

#
# Load the configuration file full of fragments
# store in array of lines, remove comments
# 
function loadConfig($path) {
  $fret = Array();
  foreach( split("\n", file_get_contents($path)) as $line) {
	if (preg_match('/^#.*/', $line) or trim($line) == "") { continue;}
	array_push($fret, trim($line));
  }	
  return $fret;
}

#
# find the best key for the variables given
# 
function getKey($config, $keyFind, $pathMatch) {
  $starCount=-1;
  $currentData="# no config found for $keyFind";

  foreach($config as $item) {
	list($key, $match, $data) =  split('\|', $item);
	$key = trim($key);
	$match = trim($match);
	$data = trim($data);

	if ($key == $keyFind) {
	  $t1 = split('/', $pathMatch);
	  $t2 = split(',', $match);

	  if( ! ($t1[0] == $t2[0] or $t2[0] == "*")) { continue;}
	  if( ! ($t1[1] == $t2[1] or $t2[1] == "*")) { continue;}
	  if( ! ($t1[2] == $t2[2] or $t2[2] == "*")) { continue;}
	  if( ! ($t1[3] == $t2[3] or $t2[3] == "*")) { continue;}
	  if( ! ($t1[4] == $t2[4] or $t2[4] == "*")) { continue;}

	  # If the data points to a string (first char is a @ then load the file as the data
	  if (preg_match('/^@.*/',  $data)) { $data = trim(file_get_contents( substr($data,1))); }

	  if ( (substr_count($match,'*') < $starCount) or ($starCount == -1) ) {
		$starCount   = substr_count($match, '*');
		$currentData = $data;
	  }

	}
  }
  return $currentData;
}

#
# used for regex matching to fill in correct vars
#
function findKey($match) {
  global $config,$group,$site,$major,$minor,$arch;

  switch($match[1]) {
	case "MAJOR":
	  return $major;
	  break;
	case "MINOR":
	  return $minor;
	  break;	
	case "ARCH":
	  return $arch;
	  break;
	case "UNDERARCH":
	  if ($arch == "i686") { return "i686";}
	  if ($arch == "i386") { return "i386";}
	  if ($arch == "x8664") { return "x86_64";}
	  if ($arch == "x86_64") { return "x86_64";}
	  return "NOUNDERARCH FOR $arch";
	}

  return getKey($config, $match[1], "$group/$site/$major/$minor/$arch");

}

# load the correct template in
$templateName = getKey($config, "KSTEMPLATE", "$group/$site/$major/$minor/$arch");
$templateData = file_get_contents($templateName);

foreach(split("\n", $templateData) as $line) {
  print preg_replace_callback('/##(.[A-Z]*)##/', "findKey", $line) . "\n" ;
}
?>
