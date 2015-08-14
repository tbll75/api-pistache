<?php 

namespace App\Controller;

class GetController extends MailController{

	private $idParentReference;

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		$dataToShow = $this->mainTraitment($entity, $data, $integratedDependences);

		// traitement data a montrer
		print_r($dataToShow);
	}



	public function mainTraitment($entity, $condition, $integratedDependences){
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

		// On vérif les condition
		if(count($condition) > 2)
			$condition = $this->checkIfId($entity, $condition, $integratedDependences);

		// on fait la requete
		$rep = $this->select("SELECT * FROM $entity WHERE ".$condition['key']." = '".$condition['value']."'");
		// on réecrit tout ca pour que ce soit au format JSON.
		$str = '[';
		foreach ($rep as $entry) {
			$str .= '{';
			foreach ($entry as $key => $value) {
				// on rentre ou traite une nouvelle select suivant si c'est un tableau ou non.
				if(!is_array($value))
					$str .= '"'.$key.'":"'.$value.'", ';
				elseif($integratedDependences){
					// on modif la condition pour que ca devienne la référence
					$entityParent = substr($entity, 4);
					$condition['key'] = $entityParent."_id".$entityParent;
					$parentKey = "id".substr($entity, 4);
					$condition['value'] = $entry[$parentKey];
					$str .= $this->getEntry($key, $condition, $integratedDependences);
				}
			}
			$str = substr($str, 0, -2).'}, ';
		}
		$str = substr($str, 0, -2).']';

		// on retourne la data sous form jolie.
		return $str;

	}



	public function checkIfId($table, $data, $integratedDependences){
		// on cherche le nom de la colonne avec l'id
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		foreach ($rep as $fields) {
			if($fields['ORDINAL_POSITION'] == 1){
				$idColumn = $fields['COLUMN_NAME'];
				break;
			}
		}
		// si id il y a
		if (!empty($data[$idColumn])){
			// On prépare l'id de référence pour une potentielle recherche en cascade
			$entityProper = substr($table, 4);
			$keyConstruct = $entityProper."_id".$entityProper;
			$this->idParentReference = array("key" => $keyConstruct, "value" => $data[$idColumn]);
			// On envoit l'id de get spécifique.
			return array("key" => $idColumn, "value" => $data[$idColumn]);
		// si pas d'id on envoit l'id de référence.
		}else{
			if($integratedDependences)
				return $this->idParentReference;
			else
				return false;
		}
	}




}
