<?php 

namespace App\Controller;

class SQLController{

	public function select($sql){// grosse machinerie vers la bdd

		require './serveur.php'; // on importe la connexion bdd.
		// Une fois la requete SQL bien écrite on envoie le tout vers la bdd.
		$req = $bdd->prepare($sql);
		if (!$req) {
			   echo "\nPDO::errorInfo():\n";
			   print_r($req->errorInfo());
			   return false;
			}
		// echo '<pre>';
		$req->execute();
		// echo '</pre>';
		// On récupère la data 
		$rep = array();
		while($data = $req->fetch()){
			$rep[] = $data;
		}
		// on envoie
		return $rep;
	}

	public function insert($sql){ // grosse machinerie vers la bdd

		require './serveur.php'; // on importe la connexion bdd.
		// Une fois la requete SQL bien écrite on envoie le tout vers la bdd.
		$req = $bdd->prepare($sql);
		if (!$req) {
			   echo "\nPDO::errorInfo():\n";
			   print_r($req->errorInfo());
			   return false;
			}
		// echo '<pre>';
		$req->execute();
		// echo '</pre>';
		// on envoie que tout va bibien.
		return true;
	}

	public function update($sql){ // grosse machinerie vers la bdd

		require './serveur.php'; // on importe la connexion bdd.
		// Une fois la requete SQL bien écrite on envoie le tout vers la bdd.
		$req = $bdd->prepare($sql);
		if (!$req) {
			   echo "\nPDO::errorInfo():\n";
			   print_r($req->errorInfo());
			   return false;
			}
		// echo '<pre>';
		$req->execute();
		// echo '</pre>';
		// on envoie que tout va bibien.
		return true;
	}

	public function delete($sql){ // grosse machinerie vers la bdd

		require './serveur.php'; // on importe la connexion bdd.
		// Une fois la requete SQL bien écrite on envoie le tout vers la bdd.
		$req = $bdd->prepare($sql);
		if (!$req) {
			   echo "\nPDO::errorInfo():\n";
			   print_r($req->errorInfo());
			   return false;
			}
		// echo '<pre>';
		$req->execute();
		// echo '</pre>';
		// on envoie que tout va bibien.
		return true;
	}

}


?>