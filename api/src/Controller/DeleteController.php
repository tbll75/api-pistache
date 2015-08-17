<?php 

namespace App\Controller;

class DeleteController extends SQLController{

	public function entity(){

		$data = json_decode($_POST['json'], true)['data'];

		$table = $this->switcher(json_decode($_POST['json'], true)['entity'])

		foreach ($data as $key => $value) {
			if(preg_match('/^id/', $key))
				$entity = $key;
		}

		// valeur de l'id
		$id = $data['id'.$entity];

		if(!empty($id)){
			$sql = "DELETE FROM $table WHERE $key = '".$id."'";
			$result = $this->delete($sql);
			if($result){ echo 'ok'; }else{ echo '{"error":"Probleme inconnu"}'; }
		}else{
			$error = '{"error":"Aucun id"}';
			echo $error;
			die();
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
			echo "<br/>New Table : ".$switcher[$table];
			return $switcher[$table];
		}else
			return false;
	}

}