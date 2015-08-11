<?php 

namespace App\Controller;

class MailController{

	public function welcome(){

		$server = 'https://mandrillapp.com/api/1.0';
		$mandrillKey = "FbUINsewlDpp_WAZV-a04w";

		// Requete vers le serveur pour avoir le template
		$api = "/templates/info.json";
		$templateFields = array(
			"key" => $mandrillKey, 
			"name" => "welcome"
			);

		$template = curl_init();
		curl_setopt( $template,CURLOPT_URL, $server.$api );
		curl_setopt( $template,CURLOPT_POST, true );
		curl_setopt( $template,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $template,CURLOPT_POSTFIELDS, json_encode($templateFields) );
		$tmpResult = json_decode(curl_exec($template), true);
		curl_close($template);

		// traitement
		if(!empty($tmpResult['publish_code'])) {
			$html = $tmpResult['publish_code'];
		}elseif(!empty($tmpResult['code'])) {
			$html = $tmpResult['code'];
		}
		if(!empty($tmpResult['publish_text'])) {
			$text = $tmpResult['publish_text'];
		}elseif(!empty($tmpResult['text'])) {
			$text = $tmpResult['text'];
		}

		// prepare
		$api = '/messages/send.json';
		$fields = array(
			"key" => "FbUINsewlDpp_WAZV-a04w",
			"template_name" => "welcome",
		    "template_content" => array(
		        array(
		            "name" => "welcome",
		            "content" => "example content"
		        )
		    ),
		    "message" => array(
		        "html" => $html,
		        "text" => $text,
		        "subject" => "Bienvenue chez Pistache!",
		        "from_email" => "contact@pistache-app.com",
		        "from_name" => "Pistache",
		        "to" => array(
		            array(
		                "email" => "thibault@pistache-app.com",
		                // "name" => "Orazio Locchi",
		                "type" => "to"
		            ),
		            array(
		                "email" => "orazio.locchi@live.fr",
		                // "name" => "Orazio Locchi",
		                "type" => "to"
		            )
		        ),
		        "headers" => array(
		            "Reply-To" => "thibault@pistache-app.com"
		        ),
		         "merge_vars" => array(
		            array(
		                "rcpt" => "thibault@pistache-app.com",
		                "vars" => array(
		                    array(
		                        "name" => "PSWD",
		                        "content" => "easy le paswd"
		                    )
		                )
		            ),
		            array(
		                "rcpt" => "orazio.locchi@live.fr",
		                "vars" => array(
		                    array(
		                        "name" => "PSWD",
		                        "content" => "tmtc"
		                    )
		                )
		            )
		        ),
		        "important" => false,
		        "track_opens" => null,
		        "track_clicks" => null,
		        "auto_text" => null,
		        "auto_html" => null,
		        "inline_css" => null,
		        "url_strip_qs" => null,
		        "preserve_recipients" => null,
		        "view_content_link" => null,
		        // "bcc_address" => "message.bcc_address@example.com",
		        "tracking_domain" => null,
		        "signing_domain" => null,
		        "return_path_domain" => null,/*
		        "google_analytics_domains" => array(
		            "example.com"
		        ),
		        "google_analytics_campaign" => "message.from_email@example.com",
		        "metadata" => array(
		            "website" => "www.example.com"
		        ),
		        "recipient_metadata" => array(
		            array(
		                "rcpt" => "orazio.locchi@live.fr",
		                "values" => array(
		                    "user_id" => 123456
		                )
		            )
		        ),
		        "attachments" => array(
		            array(
		                "type" => "text/plain",
		                "name" => "myfile.txt",
		                "content" => "Contenu du fichier texte!"
		            )
		        ),
		        "images" => array(
		            array(
		                "type" => "image/png",
		                "name" => "IMAGE",
		                "content" => "qsgSDFg"
		            )
		        )*/
		    ),
		    "async" => false
		);
		// envoit
		$mail = curl_init();
		curl_setopt( $mail,CURLOPT_URL, $server.$api );
		curl_setopt( $mail,CURLOPT_POST, true );
		curl_setopt( $mail,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $mail,CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = curl_exec($mail);
		curl_close($mail);

		// retour
		echo 'Send';


	}

	public function weekly(){
		// Envoi un mail aux parents avec les stats de l'enfant 

	}

}

 ?>