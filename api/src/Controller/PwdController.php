<?php 

namespace App\Controller;



class PwdController extends MailController{


	public function askPass(){ 
	
		//echo "HI";
	
		$hashmail = $_POST['hash'];
		$pass = $_POST['pass'];

		// on récupere les mails
		$req = $this->select("SELECT mail, idFamily FROM api_Family");

		$mails = "";
		foreach ($req as $famille) {
			$mails[$famille['idFamily']] = hash_hmac('sha256', $famille['mail'], 'pistache', false);
		}

		// on compare dans la liste de la bdd
		if(in_array($hashmail, $mails)){
			$idFamily = array_search($hashmail, $mails);
			// On peut générer le mdp et tout ca.
			//echo $idFamily
			//echo $this->update("SELECT mail, idFamily FROM api_Family")
			
			//echo "</br>ID ".$idFamily;
			
			$passCrypt = hash_hmac('sha256', $pass, 'pistache', false); 
			
			$this->insert("INSERT INTO api_TempMdp (Family_idFamily, tempMdp) VALUES (".$idFamily.", '".$pass."')");
			
			$this->update("UPDATE api_Family SET masterPassword='".$passCrypt."' WHERE idFamily = '".$idFamily."'");
			$rep = $this->select("SELECT mail FROM api_Family WHERE idFamily = '".$idFamily."'");
			
			/*
			foreach ($rep as $mail) {
				echo "</br>Nouveau mot de passe envoyé à ".$mail['mail']." !";
				$this->newPass($mail['mail'], $pass);
			}
			*/
			
		}else{
			echo '{"error":"Hash invalide"}';
		}
	}

	public function getnewmdp(){ 
	
		//echo "HI";
	
		$idFamily = json_decode($_POST['json'], true)['idFamily']; 
		
		$rep = $this->select("SELECT * FROM api_TempMdp WHERE Family_idFamily = '$idFamily' order by idTempMdp DESC LIMIT 1");
		
		if(count($rep) > 0)
		{
			echo '{"tempMdp":"'.$rep[0]['tempMdp'].'"}';
			$this->delete("DELETE FROM api_TempMdp WHERE idTempMdp = ".$rep[0]['idTempMdp']);
		}
		else
			echo '{"tempMdp":"-1"}';
	}
}

 ?>