<?php include("db.php");
if($db){
	$sql = 'SELECT lat,long,timestamp FROM points';
$sth = $db->prepare($sql);
$sth->execute();
$result = $sth->fetchAll();
} else {
	echo "DB error.";
}
?>
