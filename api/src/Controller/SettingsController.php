<?php 

namespace App\Controller;



class SettingsController extends SQLController{

// getters ***************************************************************
	public function index(){ // /family
		echo json_encode($this->select("SELECT * FROM api_Settings"));
	}

	public function indexfields($champs){ // /family/prenom,nom,maison
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_Settings"));
	}

	public function show($id){ // /family/4
		echo json_encode($this->select("SELECT * FROM api_Settings WHERE idSettings = ".$id));
	}

	public function showfields($id,$champs){ // /family/4/prenom,nom,jardin
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_Settings WHERE idSettings = ".$id));
	}

}

?>