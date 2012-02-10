// Todo: 
// - Check for password
// - Allow timestamp to be specified (allows recorded updates)
// - possibly use JSON for updates - json_encode/decode is probably trivial, though I'd need to see the array that gets sent... 
<?php include("db.php");
if ($_GET['password'] != "buns") {
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
	die("Wrong password.")
}
if($db){
	$sql = "INSERT INTO points (`lat`,`long`, `timestamp`, `timestamp_old`) VALUES (:lat, :long, :timestamp, :timestamp_old)";
$sth = $db->prepare($sql);
$sth->bindParam('lat', $_GET['lat']);
$sth->bindParam('long', $_GET['long']);
if ($_GET['timestamp'] == null){
	$date = new DateTime("now", new DateTimeZone("Asia/Singapore"));
	$sth->bindParam('timestamp_old', $date->format(DATE_RFC850));
	$sth->bindValue('timestamp', null, PDO::PARAM_NULL);
} else {
	$date = new DateTime("now", new DateTimeZone("Asia/Singapore"));
	$date->setTimestamp((int)$_GET['timestamp']);
	$sth->bindParam('timestamp_old', $date->format(DATE_RFC850));
	$sth->bindParam('timestamp', $_GET['timestamp']);
}
$result = $sth->execute();
	if ($result) {
		echo "OK";
	} else {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		echo $sth->errorCode() . " - " . print_r($sth->errorInfo());
	}
} else {
	echo "DB error.";
}
?>
