<?php include("db.php");
if($db){
	$sql = 'SELECT `lat`,`long`,`timestamp_old`, `timestamp` FROM points';
$sth = $db->prepare($sql);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row){
	$date = DateTime::createFromFormat("l, d-M-y H:i:s T", $row['timestamp_old']);
	//echo $date->format(DATE_RFC850);
	$row['timestamp'] = $date->format(DATE_RFC850);
}
echo json_encode($result);
} else {
  header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	echo "DB error.";
}
?>
