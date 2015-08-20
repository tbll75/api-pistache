<?php 

namespace App\Controller;

class GetController extends MailController{

	private $callBack = '';
	private $nbRecursive = 0;


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$table = json_decode($_POST['json'], true)['entity']; 
		$jsonStruct = json_decode($_POST['json'], true)['data']; 
		$integrateDependences = json_decode($_POST['json'], true)['integrateDependences']; 

		// GET CONDITION
		foreach ($jsonStruct as $key => $value) {
			if(preg_match('/^id[a-zA-Z]+/', $key)) {
				$parentFieldId = $key;
				$parentValueId = $value;
			}
			if($key == 'mail'){
				$parentFieldMail = $key;
				$parentValueMail = $value;
			}

			if(!empty($parentValueId) && $parentValueId != -1){
				// on a un id valable
				$parentField = $parentFieldId;
				$parentId = $parentValueId;
				break;
			}

			if(!empty($parentValueMail) && !empty($parentValueId) && $parentValueId == -1){
				// on a un mail valable et pas d'id valable
				// on check le mdp
				$idFamily = $this->checkPassForConnection($parentValueMail, $jsonStruct['masterPassword']);
				// on traite le resultat
				if($idFamily > 0){
					// si on a un id
					$parentField = 'idFamily';
					$parentId = $idFamily;
					break;
				}else{
					// sinon error.
					echo '{"error":"Wrong password for '.$parentValueMail.'"}';
					die();
				}
			}
		}

		// GET STRUCTURE
		if($integrateDependences){
			$allStruct = json_decode($this->getAllStructForEntity($table), true);
			if(!$allStruct)
				echo 'No data structure for '.$table;
			else
				$struct = $allStruct;
		}else
			$struct = $jsonStruct;


		// Check if conditions field.
		if(!empty($parentField) && !empty($parentId)){
			$this->mainTraitment($table, $parentField, $parentId, $struct);
		}else
			echo 'No traitment to do.';

		echo $this->callBack;
		// echo "<pre>";
		// print_r(json_decode($this->callBack, true));
		// echo '</pre>';
	}



	public function mainTraitment($table, $parentField, $parentId, $struct){

		$mainTableau = $table;

		// on switch le nom de la table avec celui qui correspond en bdd
		$table = $this->switcher($table);
		if($table == false){
			echo 'No equivalent table for '.$mainTableau.' in switcher';
			return false;
		}			

		// Requete
		$rep = $this->select("SELECT * FROM $table WHERE $parentField = '$parentId'");
		$idKey = '';
		$idValue = '';

		if(count($rep) > 0 && $this->nbRecursive > 0){
			// name le tableau si necessaire
			$this->callBack .= '"'.$mainTableau.'":';
		}
		// prépare l'affichage du nom dans le json.
		$this->nbRecursive++;

		// traitement
		if(count($rep) > 1)
			$this->callBack .= '[';

		foreach ($rep as $result) {

			$this->callBack .= '{';
			
			$idKey = '';
			$idValue = '';

			foreach ($result as $key => $value) {

				// Si le champ est demandé
				if(array_key_exists($key, $struct)){
					$this->callBack .= '"'.$key.'":"'.$value.'",';
				}
				// Si id il y a on le chope pour construire les conditions des enfants
				if(empty($idKey) && empty($idValue) && preg_match('/^id[a-zA-Z]+/', $key)){
					$idKey = substr($table, 4)."_".$key;
					$idValue = $value;
				}
			}

			if(empty($idKey) && empty($idValue)){
				//echo 'No condition for futur clause.';
			// si il a les futur conditions, on véirifie si des tableau sont demandés.
			}else{
				$tableaux = '';
				foreach ($struct as $key => $value) {
					if(is_array($value) && !empty($value)){
						$tableaux[] = $key;
					}
				}
				if(!empty($tableaux)){
					foreach ($tableaux as $tableau) {
						// on récursive pour les tableaux voulu.
						if(!empty($tableau)){
							$this->mainTraitment($tableau, $idKey, $idValue, $struct[$tableau]);
							$this->callBack .= ",";
						}
					}

				}
			}

			$this->callBack = substr($this->callBack, 0, -1).'},';
		
		}
		$this->callBack = substr($this->callBack, 0, -1);

		if(count($rep) > 1)
			$this->callBack .= ']';


	}




	public function checkPassForConnection($mail, $password){
		// find line en db.
		$rep = $this->select("SELECT idFamily, masterPassword FROM api_Family WHERE mail = '$mail'");
		// hash le mot de passe
		$tryPass = hash_hmac('sha256', $password, 'pistache');
		// compare
		foreach ($rep as $result) {
			if($tryPass == $result['masterPassword']){
				return $result['idFamily'];
			}else
				return false;
		}

	}




	public function getAllStructForEntity($table){
		$switcher = array(
				"FamilyData" => '{"idFamily":null,"mail":null,"masterPassword":null,"name":null,"activateTuto":null,"Chore":{"idChoreRec":null,"Family_idFamily":null,"childId":null,"xpToWin":null,"name":null,"text":null,"state":null,"imageName":null,"date":null,"energy":null,"lundi":null,"mardi":null,"mercredi":null,"jeudi":null,"vendredi":null,"samedi":null,"dimanche":null,"matin":null,"dejeuner":null,"gouter":null,"diner":null,"isRecurrent":null,"isActive":null},"device":{"idSupport":null,"Family_idFamily":null,"os":null,"deviceToken":null},"MemberList":{"idChildren":null,"Family_idFamily":null,"name":null,"birthday":null,"sex":null,"photo":null,"level":null,"xp":null,"energy":null,"nbBanana":null,"activateTuto":null,"Settings":{"idSettings":null,"Children_idChildren":null,"validation":null,"MaxPlayTime":null},"choreChildren":{"idChoreDone":null,"ChoreRec_idChoreRec":null,"Children_idChildren":null,"momentOfDay":null,"momentOfWeek":null,"isValidated":null,"dueDate":null,"isCompleted":null,"timeCompleted":null},"hero":{"idHero":null,"Children_idChildren":null,"yeux":null,"chapeau":null,"pantalon":null,"collier":null,"chaussureDroite":null,"chaussureGauche":null,"gantDroit":null,"gantGauche":null,"colorR":null,"colorB":null,"colorG":null,"colorA":null},"listeDebloque":{"Children_idChildren":null,"ObjectList_idObjectList":null}}}',
				"FamilyMember" => "api_Children",
				"Chore" => "api_ChoreRec",
				"ChoreChild" => "api_ChoreDone", 
				"Settings" => "api_Settings",
				"hero" => "api_Hero",
				"MemberList" => "api_Children",
				"device" => "api_Support",
				"settings" => "api_Settings",
				"chore" => "api_ChoreDone",
				// "listeDebloque" => "api_ObjectUnlock"
			);
		if(is_string($switcher[$table])){
			return $switcher[$table];
		}else
			return false;
	}



	public function switcher($table){
		$switcher = array(
				"FamilyData" => "api_Family",
				"RecurentChoreList" => "api_ChoreRec",
				"Chore" => "api_ChoreRec",
				"FamilyMember" => "api_Children",
				"MemberList" => "api_Children",
				"choreChildren" => "api_ChoreDone", 
				"ChoreChild" => "api_ChoreDone", 
				"Settings" => "api_Settings",
				"Setting" => "api_Settings",
				"hero" => "api_Hero",
				"Hero" => "api_Hero",
				"device" => "api_Support",
				"Device" => "api_Support",
				"listeDebloque" => "api_ObjectUnlock"
			);
		if(is_string($switcher[$table])){
			return $switcher[$table];
		}else
			return false;
	}

}

?>