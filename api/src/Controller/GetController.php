<?php 

namespace App\Controller;

class GetController extends MailController{

	private $idParentReference;
	private $idError = 0;

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		$dataToShow = $this->mainTraitment($entity, $data, $integratedDependences);

		// traitement data a montrer
		echo "<br/>---------------------------------------------------------------------------------------------------------------------------------<br/>";
		print_r($dataToShow);
	}



	public function mainTraitment($entity, $data, $integratedDependences){
		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit le nom de la table BDD pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone", 
			"Settings" => "api_Settings",
			"hero" => "api_Hero",
			// "listeDebloque" => "api_ObjectUnlock"
			);
		// Si le tableau n'existe pas
		if(!isset($switcher[$entity])){
			$this->ids[] =  '"error'.$this->idError++.'":"Entity '.$entity.' unknown"';
			return false;
		}else
			$entity = $switcher[$entity];


		// On récupère la structure de la table ciblée.
		$tableStruct = $this->getTableStruct($entity);

		echo '<pre>';
		print_r($tableStruct);
		echo '</pre>';

		die();


	}



	public function getTableStruct($table){
		// petite requete sql
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		// on filtre les infos intéressante
		$colonne = array();
		foreach ($rep as $col) {
			$colonne[] = $col['COLUMN_NAME'];
		}
		// on renvoit la réponse
		return $colonne;
	}




}
