<?php include("db.php");
if ($_GET['password'] != "buns") {
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);
	die("Wrong password.")
}
if($db){
	$sql = "INSERT INTO points (`lat`,`long`, `timestamp_old`) VALUES (:lat, :long, :timestamp)";
$sth = $db->prepare($sql);
$sth->bindParam('lat', $_GET['lat']);
$sth->bindParam('long', $_GET['long']);
$date = new DateTime("now", new DateTimeZone("Asia/Singapore"));
$sth->bindParam('timestamp', $date->format(DATE_RFC850));
$result =$sth->execute();
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
