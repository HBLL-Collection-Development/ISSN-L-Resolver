<?php
/**
 * Run service as terminal command. Requires PHP v7.1.
 * Commands  N2C,N2Ns,isN,isC,info, list
 * php resolve.php -j --n2n 1234567
 * http://localhost/gits/ISSN-L-resolver/webservice/
 */

include('conf.php');

if ($is_cli) {
  $optind = null;
  $opts = getopt('hjx', $cmdValid, $optind); // exige PHP7.1
  $extras = array_slice($argv, $optind);
  $outFormat = isset($opts['x'])? 'x': 'j';  // x|j|t
  unset($opts['x']);unset($opts['j']);
  $cmd = array_keys($opts);
  if (isset($opts['h']) || !count($extras) || count($cmd)!=1)
    die("\n---- ISSN-L RESOLVER -----".file_get_contents('synopsis.txt')." \n");
  $sval = $extras[0];
  $opname = strtolower(trim($cmd[0]));
  $outType   = 'int';

} else {
 	$opname = isset($_GET['opname'])? strtolower(trim($_GET['opname'])): '';
 	$sval = isset($_GET['sval'])? trim($_GET['sval']): ''; 				// string input
 	$outFormat = isset($_GET['format'])? trim($_GET['format']): 'x'; // h|x|j|t
 	$outType   = 'int';
}

if (!$is_cli) {
	if ($status!=200) http_response_code($status);
	header("Content-Type: $outFormatMime[$outFormat]");
}

$output = issnLresolver($opname,$sval,$outType,$outFormat);
echo json_encode($output);


//////////////////// LIB ////////////////////
function issnLresolver($opname,$sval,$vtype='str',$outFormat,$debug=false) {
	global $PG_CONSTR, $PG_USER, $PG_PW;
	$r = $sql = '';
	if ($opname) {
		switch ($opname) {
		case 'n2ns':
		case 'n2c':
		case 'n2cs':
		case 'isn':
		case 'isc':
    case 'n2n':
    case 'n2ns':
			$val = (!$vtype || $vtype=='str')? "'$sval'": $sval;
			$sqlFCall = "issn.{$outFormat}service($val,'$opname')";  // ex. issn_xservice(8755999,'n2ns');
			break;
		case 'info':
			$sqlFCall = "'... info formated text ...'";
			break;
		default:
			$sqlFCall = "'op-name not knowed'";
		} // switch
		$dbh = new PDO($PG_CONSTR, $PG_USER, $PG_PW);
		$sql = "SELECT $sqlFCall LIMIT 1";
    if ($debug) echo "\n$sql\n";
		$r = $dbh->query($sql)->fetchColumn();
	}
	return $debug? array($r, $sql): $r;
} // func

?>
