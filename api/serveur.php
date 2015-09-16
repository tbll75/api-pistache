<?php 

$dbhost = $_SERVER['RDS_HOSTNAME'];
$dbport = $_SERVER['RDS_PORT'];
$dbname = $_SERVER['RDS_DB_NAME'];

$dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname}";
$username = $_SERVER['RDS_USERNAME'];
$password = $_SERVER['RDS_PASSWORD'];

try { 

	// $bdd = new PDO('mysql:host=bobappschqthib.mysql.db;dbname=bobappschqthib;charset=utf8', 'bobappschqthib', 'Bob110891', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)); 
	// $bdd = new PDO('mysql:host=localhost;dbname=bddpistache;charset=utf8', 'root', 'root', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)); 
	$bdd = new PDO($dsn, $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	$bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}catch (Exception $e){
	die('Erreur : ' . $e->getMessage());
}

?>