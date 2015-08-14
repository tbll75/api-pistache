<?php 

namespace App\Controller;

class GetController extends MailController{

	private $idParentReference;
	private $idError = 0;
	private $majorEntity = array();

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
		}else{
			$entity = $switcher[$entity];
			$this->$majorEntity['table'] = $entity;
		}

		// On récupère la structure de la table ciblée.
		$tableStruct = $this->getTableStruct($entity);

		// On compare avec la data pour filtrer les champs des 'dataEnfants'.
		$sortedData = $this->filterDataForThisTable($entity, $data, $tableStruct);

		// On cherche les champs souhaité
		// $output = $this->selectTableElements($entity, $sortedData[0], $condition); // [0] pour les champs de la bdd

		echo '<pre>';
		print_r($this->majorEntity);
		echo '</pre>';

		die();


	}



	public function selectTableElements($table, $fields, $condition){
		// on préprare
		$fields = implode(', ', $fields);
		// on select
		$rep = $this->select("SELECT $fields FROM $table ");
	}



	public function filterDataForThisTable($table, $data, $tableStruct){
		// on compare si oui on garde sinon ou met de coté pour plus tard
		$fields = '';
		$otherTable = '';
		foreach ($data as $key => $value) {
			// c'est dans la structure
			if(in_array($key, $tableStruct))
				$fields[] = $key;
			elseif (is_array($value)) 
				$otherTable[] = $key;

			// par aillerus on stock l'id du patron ci nécéssaire
			if($key == $this->majorEntity['key'])
				$this->majorEntity['value'] = $value;
		}

		return array($fields, $otherTable);

	}



	public function getTableStruct($table){
		// petite requete sql
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		// on filtre les infos intéressante
		$struct = array();
		foreach ($rep as $col) {
			$struct[] = $col['COLUMN_NAME'];
			// si c'est le patron de la requete, on sauvegarde son identificateur.
			if($col['ORDINAL_POSITION'] == 1 && $this->majorEntity['table'] == $table)
				$this->majorEntity['key'] = $col['COLUMN_NAME'];
		}
		// on renvoit la réponse
		return $struct;
	}




}
