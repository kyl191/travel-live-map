<?php include("db.php");
if($db){
	$sql = 'SELECT lat,long,timestamp FROM points';
$sth = $db->prepare($sql);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result);
} else {
  header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	echo "DB error.";
}
?>
