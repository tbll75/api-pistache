<?php 

namespace App\Controller;

class GetController extends MailController{

	private $callBack = '';


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$table = json_decode($_POST['json'], true)['entity']; 
		$struct = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		foreach ($struct as $key => $value) {
			if(preg_match('/^id[a-zA-Z]+/', $key)){
				$parentField = $key;
				$parentId = $value;
				break;
			}
		}

		if(!empty($parentField) && !empty($parentId))
			$this->mainTraitment($table, $parentField, $parentId, $struct);
		else
			echo 'No traitment to do.';

		echo "<br/></br>".$this->callBack;
	}



	public function mainTraitment($table, $parentField, $parentId, $struct){
		// on switch le nom de la table avec celui qui correspond en bdd
		$table = $this->switcher($table);
		if($table == false)
			return false;

		// Requete
		$rep = $this->select("SELECT * FROM $table WHERE $parentField = '$parentId'");
		$idKey = '';
		$idValue = '';

		if(count($rep) > 1)
			$this->callBack .= '[';


		// traitement
		foreach ($rep as $result) {
			$idKey = '';
			$idValue = '';
			$this->callBack .= '{';
			foreach ($result as $key => $value) {
				// Si le champ est demandé
				if(in_array($key, $struct))
					$this->callBack .= '"'.$key.":".$value.'", ';
				// Si id il y a on le chope pour construire les conditions des enfants
				if(empty($idKey) && empty($idValue) && preg_match('/^id[a-zA-Z]+/', $key)){
					$idKey = substr($table, 4)."_".$key;
					$idValue = $value;
					echo "<br/><b>Futur clause :</b> ".$idKey." -> ".$idValue;
				}
			}
			$this->callBack = substr($this->callBack, 0, -2);
			if(empty($idKey) && empty($idValue)){
				echo 'No condition for futur clause.';
			// si il a les futur conditions, on véirifie si des tableau sont demandés.
			}else{
				$tableaux = '';
				echo "<br/><br/>TABLEAU(X) :";
				foreach ($struct as $key => $value) {
					if(is_array($value) && !empty($value)){
						$tableaux[] = $key; echo '<br/>'.$key;
						$this->callBack .= '"'.$key.'":';
					}
				}
				if(!empty($tableaux))
					foreach ($tableaux as $tableau) {
						// on récursive pour les tableaux voulu.
						echo '<br/><br/>Go Traitment : '.$tableau.' -> '.$idKey.' = '.$idValue.'<br>';
						$this->mainTraitment($tableau, $idKey, $idValue, $struct[$tableau]);
					}
			}
			$this->callBack .= '}';
		}


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
				"deviceList" => "api_Support",
				"settings" => "api_Settings",
				"chore" => "api_ChoreDone",
				// "listeDebloque" => "api_ObjectUnlock"
			);
		if(is_string($switcher[$table])){
			echo "<br/>New Table : ".$switcher[$table];
			return $switcher[$table];
		}else
			return false;
	}

}

?>