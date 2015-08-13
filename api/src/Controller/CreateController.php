<?php 

namespace App\Controller;

class CreateController extends MailController{

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$savedDateTime = json_decode($_POST['json'], true)['savedDateTime']; 

		// On vérifie que tous les champs sont saisies
		$tableBdd = $this->entityTraitment($entity, $data);
		echo "table : ".$tableBdd[0];

		// On envoit le traitement vers la fonction d'insert
		$isInsert = $this->dataInsertTraitment($tableBdd, $tableBdd[1], $data);
		echo "insertion valide.";

		// Si l'insertion est bonne, on demande le dernier id de la table.
		if(!$isInsert){
			echo '{"error":"Insert problem"}';
			die();
		}
		$idJson = $this->respondBDD($tableBdd);
		echo "idJson : ".$idJson;

		// On renvoit la reponse (l'id) nouvellement généré.
		echo $idJson;
	}



	public function respondBDD($table){
		// on recupère la derniere ligne de la table $table
		// Pour ca faut déjà connaitre le nom de la colone qui porte l'id.
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$switcher[$entity]."'");
		foreach ($rep as $fields) {
			if($fields['ORDINAL_POSITION'] == 1){
				$idColumn = $fields['COLUMN_NAME'];
				break;
			}
		}
		echo $idColumn;
		// puis on demande
		$rep = $this->select("SELECT $idColumn FROM $table ORDER BY $idColumn DESC LIMIT 1");
		// et on renvoit
		foreach ($rep as $key => $value) {
			$json = '{"'.$key.'":"'.$value.'"}';
		}
		echo $key;
		echo $value;
		return $json;

	}



	public function dataInsertTraitment($table, $columns, $data){
		// on insert la data vers la bdd
		$keys = '(';
		$values = '(';
		foreach ($data as $key => $value) {
			if(!is_array($value) && in_array($key, $columns)){
				$keys .= $key.", ";
				$values .= "'".$value."', ";
			}
		}
		$keys = substr($keys, 0, -2).")";
		$values = substr($values, 0, -2).")";

		$this->insert("INSERT INTO $table $keys VALUES $values");
		return true;
	}



	public function entityTraitment($entity, $data){
		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit le nom de la table BDD pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone"
			);
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$switcher[$entity]."'");
		$fields = array();
		foreach ($rep as $columnData) {
			$fields[] = $columnData['COLUMN_NAME'];
		}
		// Maintenant on connait les champs de la table. On regarde si on les connais tous.
		$missingField = array();
		foreach ($fields as $field) {
			if(empty($data[$field])){
				$missingField[] = $field;
			}
		}
		if(!empty($missingField)){
			echo '{"error":"Missing data : '.implode(', ', $missingField).'"}';
			die();
		}else{
			return array($switcher[$entity], $fields);
		}

	}

}
