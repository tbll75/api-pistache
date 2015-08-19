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
		echo '<br/>';
		print_r($struct);
		echo '<br/>';

		// on switch le nom de la table avec celui qui correspond en bdd
		$table = $this->switcher($table);
		if($table == false)
			return false;

		echo '<br/><b><font color="red">mainTraitment :</font></b> <br>table : '.$table.'<br/>parentField : '.$parentField.'<br/>parentId : '.$parentId.'<br/>struct :';
		print_r($struct);
		echo '<br/><br/>';

		// Requete
		$rep = $this->select("SELECT * FROM $table WHERE $parentField = '$parentId'");
		$idKey = '';
		$idValue = '';


		// traitement
		foreach ($rep as $result) {
			$idKey = '';
			$idValue = '';

			foreach ($result as $key => $value) {
				// Si le champ est demandé
				if(array_key_exists($key, $struct))
					echo '!'.$key;
				// Si id il y a on le chope pour construire les conditions des enfants
				if(empty($idKey) && empty($idValue) && preg_match('/^id[a-zA-Z]+/', $key)){
					$idKey = substr($table, 4)."_".$key;
					$idValue = $value;
				}
				echo '&nbsp;&nbsp;&nbsp; ->"'.$key.'":"'.$value.'"<br/>';
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
				if(!empty($tableaux))
					foreach ($tableaux as $tableau) {
						// on récursive pour les tableaux voulu.
						$this->mainTraitment($tableau, $idKey, $idValue, $struct[$tableau]);
					}
			}

			echo '<br/>';
		}


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
			return $switcher[$table];
		}else
			return false;
	}

}

?>