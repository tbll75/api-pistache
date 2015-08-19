<?php 

namespace App\Controller;

class GetController extends MailController{

	private $callBack = '';
	private $nbRecursive = 0;


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$table = json_decode($_POST['json'], true)['entity']; 
		$struct = json_decode($_POST['json'], true)['data']; 
		$integrateDependences = json_decode($_POST['json'], true)['integrateDependences']; 

		foreach ($struct as $key => $value) {
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
				$parentField = $parentFieldMail;
				$parentId = $parentValueMail;
				break;
			}
		}

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
				echo 'No condition for futur clause.';
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



	public function switcher($table){
		$switcher = array(
				"FamilyData" => "api_Family",
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

}

?>