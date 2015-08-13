<?php 

namespace App\Controller;

class CreateController extends MailController{

	private $ids;

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$savedDateTime = json_decode($_POST['json'], true)['savedDateTime']; 

		echo $this->mainTraitment($entity, $data, $savedDateTime);
	}



	public function mainTraitment($entity, $data, $savedDateTime){
		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit le nom de la table BDD pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone", 
			"Settings" => "api_Settings",
			"hero" => "api_Hero"
			// "listeDebloque" => "api_ObjectUnlock"
			);
		// Si le tableau n'existe pas
		if(!isset($switcher[$entity]))
			return false;
		else
			$entity = $switcher[$entity];

		// On vérifie que tous les champs sont saisies
		$tableData = $this->entityTraitment($entity, $data);

		// On envoit le traitement vers la fonction d'insert
		$isInsert = $this->dataInsertTraitment($tableData[0], $tableData[1], $data);

		// Si l'insertion est bonne, on demande le dernier id de la table.
		if(!$isInsert[0]){ // S'il y a eu une erreur
			echo '{"error":"Insert problem"}';
			die();
		}
		$idJson = $this->respondBDD($tableData[0]);

		// On renvoit la reponse (l'id) nouvellement généré.
		$this->ids[] = '"'.implode('", "', $idJson).'"';

		// Si on a des sous data (Hero Settings ..) à insérer
		if($isInsert[1]){ // Si on a d'autre tableau de data a regarder.
			foreach ($data as $key => $value) {
				if(is_array($value)){
					// on injecte l'id du référent
					$entityProper = substr($entity, 4);
					$keyConstruct = $entityProper."_id".$entityProper;
					$value[$keyConstruct] = $idJson[1];
					// on renvoit la fonction
					$this->mainTraitment($key, $value, $savedDateTime);
				}
			}
		}

		return "{".implode(",", $this->ids)."}";
	}



	public function respondBDD($table){
		// on recupère la derniere ligne de la table $table
		// Pour ca faut déjà connaitre le nom de la colone qui porte l'id.
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		foreach ($rep as $fields) {
			if($fields['ORDINAL_POSITION'] == 1){
				$idColumn = $fields['COLUMN_NAME'];
				break;
			}
		}
		// puis on demande
		$rep = $this->select("SELECT $idColumn FROM $table ORDER BY $idColumn DESC LIMIT 1");
		// et on renvoit
		foreach ($rep[0] as $key => $value) {
			$json = array($key, $value);
		}
		return $json;

	}



	public function dataInsertTraitment($table, $columns, $data){
		// on insert la data vers la bdd
		$keys = '(';
		$values = '(';
		$subValues = false;
		foreach ($data as $key => $value) {
			if(!is_array($value) && in_array($key, $columns)){
				$keys .= $key.", ";
				$values .= "'".$value."', ";
			}else
				$subValues = true;
		}
		$keys = substr($keys, 0, -2).")";
		$values = substr($values, 0, -2).")";

		$this->insert("INSERT INTO $table $keys VALUES $values");
		return array(true, $subValues);
	}



	public function entityTraitment($entity, $data){

		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$entity."'");
		$fields = array();
		foreach ($rep as $columnData) {
			if($columnData['ORDINAL_POSITION'] != 1)
				$fields[] = $columnData['COLUMN_NAME'];
		}
		// Maintenant on connait les champs de la table. On regarde si on les connais tous.
		$missingField = array();
		foreach ($fields as $field) {
			if(empty($data[$field]) && $data[$field] != '0'){
				$missingField[] = $field;
			}
		}
		if(!empty($missingField)){
			echo '{"error":"Missing data : '.implode(', ', $missingField).'"}';
			die();
		}else{
			return array($entity, $fields);
		}

	}

}
