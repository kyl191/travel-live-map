<?php include("db.php");
if($db){
	$sql = "INSERT INTO points (`lat`,`long`) VALUES (:lat, :long)";
$sth = $db->prepare($sql);
$sth->bindParam('lat', $_GET['lat']);
$sth->bindParam('long', $_GET['long']);
$sth->execute();
$result = $sth->fetchAll();
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
