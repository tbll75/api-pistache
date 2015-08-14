<?php 

namespace App\Controller;

class UpdateController extends MailController{

	private $ids;
	private $idError = 0;

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$savedDateTime = json_decode($_POST['json'], true)['savedDateTime']; 

		echo $this->mainTraitment($entity, $data);
	}



	public function mainTraitment($entity, $data){

		// Ici on vérifie les données nécessaires pour chaque entité, et on retourne soit une erreur soit le nom de la table BDD pour le traitement de l'entité.
		$switcher = array(
			"FamilyData" => "api_Family",
			"FamilyMember" => "api_Children",
			"Chore" => "api_ChoreRec",
			"ChoreChild" => "api_ChoreDone", 
			"Settings" => "api_Settings",
			"hero" => "api_Hero",
			"listeDebloque" => "api_ObjectUnlock"
			);
		// Si le tableau n'existe pas
		if(!isset($switcher[$entity])){
			$this->ids[] =  '"error'.$this->idError++.'":"Entity '.$entity.' unknown"';
			return false;
		}else
			$entity = $switcher[$entity];

		echo " - InDa : ".$entity;


		// On vérifie les cas particuliers
		if(isset($data['recMomentOfWeek']) || isset($data['recMomentOfWeek']))
			$data = $this->modifyDataForMoment($data);

		// Ajouter les champ pour le dateTimeFormat.
		if(isset($data['dueDate'])) { $data['dueDate'] = $this->modifyTimeStampFormat($data['dueDate']); }
		if(isset($data['timeCompleted'])) { $data['timeCompleted'] = $this->modifyTimeStampFormat($data['timeCompleted']); }
		if(isset($data['date'])) { $data['date'] = $this->modifyTimeStampFormat($data['date']); }

		// CAS PARTICULIER 
		if($entity == 'api_ObjectUnlock' && !empty($data[0])){ 
			// on refait la structure de la data pour ne garder que les nouveaux
			$data = $this->modifyDataObject($entity, $data); 
			// on ajoute les nouvelles entrées, si nouvelles entrées il y a.
			if(!empty($data))
				$this->ids = $this->createNewDataObject($entity, $data);
			// et puis on retourne quelque chose what.
			return "{".implode(",", $this->ids)."}";
		}elseif($entity == 'api_ObjectUnlock' && empty($data[0])){
			$this->ids[] = '"Object":"No data"';
			return "{".implode(",", $this->ids)."}";
		}

		// On vérifie que tous les champs sont saisies
		$tableData = $this->entityTraitment($entity, $data);

		// On envoit le traitement vers la fonction d'insert
		$isUpdate = $this->dataUpdateTraitment($tableData[0], $tableData[1], $tableData[2], $data);

		// Si l'insertion est bonne, on demande le dernier id de la table.
		if(!$isUpdate[0]){ // S'il y a eu une erreur
			echo '{"error":"Update problem for '.$entity.'"}';
			die();
		}
		$idJson = $this->respondBDD($tableData[0], $tableData[2]);

		// On renvoit la reponse (l'id) nouvellement généré.
		$this->ids[] = '"'.implode('":"', $idJson).'"';

		// Si on a des sous data (Hero Settings ..) à insérer
		if($isUpdate[1]){ // Si on a d'autre tableau de data a regarder.
			foreach ($data as $key => $value) {
				if(is_array($value)){
					// on injecte l'id du référent
					$entityProper = substr($entity, 4);
					$keyConstruct = $entityProper."_id".$entityProper;
					$value[$keyConstruct] = $idJson[1];
					echo " - GoTo : ".$keyConstruct;
					// on renvoit la fonction
					$this->mainTraitment($key, $value);
				}
			}
		}

		return "{".implode(",", $this->ids)."}";


	}



	public function createNewDataObject($table, $data){
		// aller hop hop on create tout ca.
		$keys = '(Children_idChildren, ObjectList_idObjectList)';
		$values = '';
		foreach ($data as $newObject) {
			$values .= '('.$newObject['Children_idChildren'].', '.$newObject['ObjectList_idObjectList'].'), ';
		}
		$values = substr($values, 0, -2);

		// requete sql
		$rep = $this->insert("INSERT INTO $table $keys VALUES $values");
		// on retourne un jolie truc pour dire que tout s'est bien passé
		return '"Object":"All saved"';
	}



	public function modifyDataObject($table, $data){
		// Pour le moment on a un array avec des ids, faut mettre la clé.
		// array(Children_idChildren => x, 1,4,7) => array( array( Children_idChildren => x, ObjectList_idObjectList => y ), array( ... ) )
		foreach ($data as $key => $value) {
			if(is_numeric($key)) // en gros on veut tous les id et que les id.
				$data[$key] = array( "Children_idChildren" => $data['Children_idChildren'], "ObjectList_idObjectList" => $value );
		}
		
		// Maintenant on regarde ce qui a déjà été rentré en BDD (les objets dejà débloqué par l'enfant)
		$rep = $this->select("SELECT * FROM $table WHERE Children_idChildren = '".$data['Children_idChildren']."'");
		// on parcours chaque objet dejà débloqué (forme : array( Children_idChildren => x, ObjectList_idObjectList => y ))
		foreach ($data as $key => $newObjectUnlocked) {
			if(is_numeric($newObjectUnlocked)){
				$isIn = 0;	
				// on parcours chacun de la liste des nouveaux arrivant
				foreach ($rep as $objectUnlocked) {
					if($newObjectUnlocked == $objectUnlocked){
						$isIn = 1;
						break;
					}
				}
				if(!$isIn)
					$newData[] = $newObjectUnlocked;
			}
		}

		// on retoune la nouvelle data	
		return $newData;
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



	public function respondBDD($table, $id){
		// on recupère la derniere ligne de la table $table avec l'id donné
		$rep = $this->select("SELECT ".$id['key']." FROM $table ORDER BY ".$id['key']." DESC LIMIT 1");
		// et on renvoit
		foreach ($rep[0] as $key => $value) {
			$json = array($key, $value);
		}
		return $json;

	}



	public function dataUpdateTraitment($table, $columns, $id, $data){
		// on unpdate la data vers la bdd
		$str = '';
		$subValues = false;
		foreach ($data as $key => $value) {
			if(!is_array($value) && in_array($key, $columns)){
				$str .= $key." = '".$value."', ";
			}else
				$subValues = true;
		}
		$str = substr($str, 0, -2);

		$this->insert("UPDATE $table SET $str WHERE ".$id['key']." = '".$id['value']."'");
		return array(true, $subValues);
	}



	public function entityTraitment($entity, $data){
		// On regarde que pour chaque colonne, on a bien une data.
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$entity."'");
		$fields = array();
		foreach ($rep as $columnData) {
			$fields[] = $columnData['COLUMN_NAME'];
			// on met de coté l'id.
			if($columnData['ORDINAL_POSITION'] == 1)
				$id['key'] = $columnData['COLUMN_NAME'];
		}
		// Maintenant on connait les champs de la table. On regarde si on les connais tous.
		$missingField = array();
		foreach ($fields as $field) {
			if(empty($data[$field]) && $data[$field] != '0'){
				$missingField[] = $field;
			}
			// on met de coté la valeur de l'id.
			if($field == $id['key'] && !empty($data[$field]))
				$id['value'] = $data[$field];
		}
		if(!empty($missingField)){
			echo '{"error":"Missing data : '.implode(', ', $missingField).'"}';
			die();
		}else{
			return array($entity, $fields, $id);
		}

	}




}