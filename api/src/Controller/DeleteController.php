<?php 

namespace App\Controller;

class DeleteController extends SQLController{

	public function entity(){

		$json = json_decode($_POST['json'], true);

		foreach ($json as $key => $value) {
			if(preg_match('/^id/', $key))
				$entity = $key;
		}

		// non de l'entité
		$entity = substr($entity, 2);
		// valeur de l'id
		$id = $json['id'.$entity];

		if(!empty($id)){
			$sql = "DELETE FROM api_".$entity." WHERE id".$entity." = ".$id;
			$result = $this->delete($sql);
			if($result){ echo 'ok'; }else{ echo '{"error":"Probleme inconnu"}'; }
		}else{
			$error = '{"error":"Aucun id"}';
			echo $error;
			die();
		}
	}

}