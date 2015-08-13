<?php 

namespace App\Controller;

class CreateController extends MailController{

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json']['entity'], true); 
		$data = json_decode($_POST['json']['data'], true); 
		// $ladata = json_decode($_POST['json']['date'], true); 

		$dispatchTo = $this->entityTraitment($entity, $data);
	}

	public function entityTraitment($entity, $data){
		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit une fonction pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone"
			);
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$switcher[$entity]."'");
		echo "<pre>";
		print_r($rep);
		echo "</pre>";

	}

}
