<?php 

namespace App\Controller;

class GetController extends MailController{

	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$entity = json_decode($_POST['json'], true)['entity']; 
		$data = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		echo $this->mainTraitment($entity, $data, $integratedDependences);
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

		// On vérifie les cas particuliers
		if(isset($data['recMomentOfWeek']) || isset($data['recMomentOfWeek']))
			$data = $this->modifyDataForMoment($data);

		/* On hash les mots de passe */ /** 
		if(isset($data['masterPassword'])){ }
		*/

		// Ajouter les champ pour le dateTimeFormat.
		if(isset($data['dueDate'])) { $data['dueDate'] = $this->modifyTimeStampFormat($data['dueDate']); }
		if(isset($data['timeCompleted'])) { $data['timeCompleted'] = $this->modifyTimeStampFormat($data['timeCompleted']); }
		if(isset($data['date'])) { $date['date'] = $this->modifyTimeStampFormat($data['date']); }

		$data = $this->dataGetTraitment($entity, $data);

	}



	public function dataGetTraitment($table, $data){
		// on chope la ligne demandée
		$rep = $this->select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table."'");
		foreach ($rep as $fields) {
			if($fields['ORDINAL_POSITION'] == 1){
				$idColumn = $fields['COLUMN_NAME'];
				break;
			}
		}
		$rep = $this->select("SELECT * FROM $table WHERE $idColumn = '".$data[$idColumn]."'");
	}


}
