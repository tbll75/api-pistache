<?php 

namespace App\Controller;

class CreateController extends MailController{

	private $ids;
	private $idError = 0;

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$allOrStruct = json_decode($_POST['json'], true)['savedDateTime']; 

		echo $this->mainTraitment($entity, $data);
	}



	public function mainTraitment($entity, $data){
		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit le nom de la table BDD pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"UnlockModules" => "api_UnlockModules",
			"parentData" => "api_ParentData",
			"ParentData" => "api_ParentData",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone", 
			"Settings" => "api_Settings",
			"hero" => "api_Hero",
			"device" => "api_Support",
			// "listeDebloque" => "api_ObjectUnlock"
			);
		// Si le tableau n'existe pas
		if(!isset($switcher[$entity])){
			$this->ids[] =  '"error'.$this->idError++.'":"Entity '.$entity.' unknown"';
			return false;
		}else
			$entity = $switcher[$entity];

		// on vérfie que le mail n'existe pas déjà.
		if(isset($data['mail']) && $this->checkMailExistAllready($data['mail'])) {
			$this->ids[] =  '"error'.$this->idError++.'":"Mail '.$data['mail'].' allready exist"';
			return "{".implode(",", $this->ids)."}";
		}

		// On vérifie les cas particuliers
		if(isset($data['recMomentOfWeek']) || isset($data['recMomentOfWeek']))
			$data = $this->modifyDataForMoment($data);

		/* On hash les mots de passe */ 
		if(isset($data['masterPassword'])){ 
			$this->welcome($data['mail'], $data['masterPassword']);
			$data['masterPassword'] = hash_hmac('sha256', $data['masterPassword'], 'pistache'); 
		}
	

		// Ajouter les champ pour le dateTimeFormat.
		if(isset($data['dueDate'])) { $data['dueDate'] = $this->modifyTimeStampFormat($data['dueDate']); }
		if(isset($data['timeCompleted'])) { $data['timeCompleted'] = $this->modifyTimeStampFormat($data['timeCompleted']); }
		if(isset($data['date'])) { $date['date'] = $this->modifyTimeStampFormat($data['date']); }

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
		$this->ids[] = '"'.implode('":"', $idJson).'"';

		// Si on a des sous data (Hero Settings ..) à insérer
		if($isInsert[1]){ // Si on a d'autre tableau de data a regarder.
			foreach ($data as $key => $value) {
				if(is_array($value)){
					// on injecte l'id du référent
					$entityProper = substr($entity, 4);
					$keyConstruct = $entityProper."_id".$entityProper;
					$value[$keyConstruct] = $idJson[1];
					// on renvoit la fonction
					$this->mainTraitment($key, $value); // Ici $value est l'array contenant la data de l'entity $key.
				}
			}
		}

		return "{".implode(",", $this->ids)."}";
	}



	public function checkMailExistAllready($mail){
		$rep = $this->select("SELECT mail FROM api_Family WHERE mail = '$mail'");
		if(!empty($rep))
			return true;
		else
			return false;
	}



	public function modifyTimeStampFormat($modifiedData){
		// Petite magouille pour choper juste le timestamp.
		preg_match('!\d+!', $modifiedData, $modifiedData);
		$modifiedData =  substr($modifiedData[0], 0, -3);
		// on retourne la nouvelle valeur.
		return $modifiedData;
	}



	public function modifyDataForMoment($data){
		// Modifie la structure de la data pour les momentOf..
		// Jour de la semaine
		$recMomentOfWeek = $data['recMomentOfWeek'];
		$data['lundi'] = $recMomentOfWeek[0];
		$data['mardi'] = $recMomentOfWeek[1];
		$data['mercredi'] = $recMomentOfWeek[2];
		$data['jeudi'] = $recMomentOfWeek[3];
		$data['vendredi'] = $recMomentOfWeek[4];
		$data['samedi'] = $recMomentOfWeek[5];
		$data['dimanche'] = $recMomentOfWeek[6];
		unset($data['recMomentOfWeek']);
		// Moment de la journée
		$recMomentOfDay = $data['recMomentOfDay'];
		$data['matin'] = $recMomentOfDay[0];
		$data['dejeuner'] = $recMomentOfDay[1];
		$data['gouter'] = $recMomentOfDay[2];
		$data['diner'] = $recMomentOfDay[3];
		unset($data['recMomentOfDay']);
		// Ids des enfants liés à la tache.
		$childId = "";
		foreach ($data['childId'] as $child => $id) {
			$childId .= $id.", ";
		}
		$data['childId'] = substr($childId, 0, -2);

		return $data;
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
				
				$values .= "'".htmlentities($value, ENT_QUOTES)."', ";
			}elseif(is_array($value))
				$subValues = true;
		}
		$keys = substr($keys, 0, -2).")";
		$values = substr($values, 0, -2).")";

		$this->insert("INSERT INTO $table $keys VALUES $values");
		return array(true, $subValues);
	}



	public function entityTraitment($entity, $data){
		// On regarde que pour chaque colonne, on a bien une data.
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$entity."'");
		$fields = array();
		foreach ($rep as $columnData) {
			if($columnData['ORDINAL_POSITION'] != 1)
				$fields[] = $columnData['COLUMN_NAME'];
		}
		// Maintenant on connait les champs de la table. On regarde si on les connais tous.
		$missingField = array();
		foreach ($fields as $key => $field) {
		
			// Liste des exceptions
			if ($field == "nbTicksPlay" || $field == "savedDateTime")
			{
				unset($fields[$key]);
				continue;
			}
		
			if(empty($data[$field])/* && $data[$field] != '0'*/){
				//$missingField[] = $field;
				$data[$field] = '0';
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
