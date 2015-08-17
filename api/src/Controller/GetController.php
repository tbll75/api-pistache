<?php 

namespace App\Controller;

class GetController extends MailController{


	public function dispatch(){
		// On récupère la data sous forme de tableaux.
		$table = json_decode($_POST['json'], true)['entity']; 
		$struct = json_decode($_POST['json'], true)['data']; 
		$integratedDependences = json_decode($_POST['json'], true)['integratedDependences']; 

		$this->mainTraitment($table, $parentField, $parentId, $struct);
	}



	public function mainTraitment($table, $parentField, $parentId){
		// on switch le nom de la table avec celui qui correspond en bdd
		$table = $this->switcher($table);

		// Requete
		$rep = $this->select("SELECT * FROM $table WHERE $parentField = '$parentId'");

		// traitement
		foreach ($rep as $result) {
			foreach ($result as $key => $value) {
				echo '<br/>'.$key." : ".$value;
				if(preg_match('/^id[a-zA-Z]+/', $key)){
					$idKey = $key;
					$idValue = $value;
				}
				if(!empty($idKey) && !empty($idValue))
					echo "<br/> Futur clause : ".$idKey."->".$idValue;
				else
					echo 'No condition for futur clause.';
			}
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
				// "listeDebloque" => "api_ObjectUnlock"
			);

		return $switcher[$table];
	}

}

?>