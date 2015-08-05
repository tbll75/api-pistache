<?php 

namespace App\Controller;



class ChoreController extends SQLController{

// getters Rec ***************************************************************
	public function index(){ // /family
		echo json_encode($this->select("SELECT * FROM api_ChoreRec"));
	}

	public function indexfields($champs){ // /family/prenom,nom,maison
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_ChoreRec"));
	}

	public function show($id){ // /family/4
		echo json_encode($this->select("SELECT * FROM api_ChoreRec WHERE idChoreRec = ".$id));
	}

	public function showfields($id,$champs){ // /family/4/prenom,nom,jardin
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_ChoreRec WHERE idChoreRec = ".$id));
	}

// getters Done ***************************************************************
	public function indexDone(){ // /family
		echo json_encode($this->select("SELECT * FROM api_ChoreDone"));
	}

	public function indexfieldsDone($champs){ // /family/prenom,nom,maison
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_ChoreDone"));
	}

	public function showDone($id){ // /family/4
		echo json_encode($this->select("SELECT * FROM api_ChoreDone WHERE idChoreDone = ".$id));
	}

	public function showfieldsDone($id,$champs){ // /family/4/prenom,nom,jardin
		$champs = implode(', ', explode(',', $champs));
		echo json_encode($this->select("SELECT ".$champs." FROM api_ChoreDone WHERE idChoreDone = ".$id));
	}

}

?>