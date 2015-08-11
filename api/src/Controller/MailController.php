<?php 

namespace App\Controller;

class MailController{

	public function welcome(){

		/*# Include the Autoloader (see "Libraries" for install instructions)
		require 'vendor/autoload.php';
		use Mailgun\Mailgun;

		# Instantiate the client.
		$mgClient = new Mailgun('key-e71c0195d23e0930cae6e89f7078f360');
		$domain = "sandboxffbbdca0be4c417d8af478152afb81d8.mailgun.org";

		# Make the call to the client.
		$result = $mgClient->sendMessage("$domain",
		                  array('from'    => 'Mailgun Sandbox <postmaster@sandboxffbbdca0be4c417d8af478152afb81d8.mailgun.org>',
		                        'to'      => 'Orazio Locchi <orazio.locchi@gmail.com>',
		                        'subject' => 'Hello Orazio Locchi',
		                        'text'    => 'Congratulations Orazio Locchi, you just sent an email with Mailgun!  You are truly awesome!  You can see a record of this email in your logs: https://mailgun.com/cp/log .  You can send up to 300 emails/day from this sandbox server.  Next, you should add your own domain so you can send 10,000 emails/month for free.'));

	}

	public function welcome(){*/

		$mgClient = 'key-e71c0195d23e0930cae6e89f7078f360';
		$domain = "sandboxffbbdca0be4c417d8af478152afb81d8.mailgun.org";
		$from = 'postmaster@'.$domain;

		$to = 'Orazio Locchi <orazio.locchi@gmail.com>';
		$subject = 'Hello Orazio Locchi';
		$message = 'Congratulations Orazio Locchi, you just sent an email with Mailgun!  You are truly awesome!  You can see a record of this email in your logs: https://mailgun.com/cp/log .  You can send up to 300 emails/day from this sandbox server.  Next, you should add your own domain so you can send 10,000 emails/month for free.';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:'.$mgClient);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$plain = preg_replace("/\<br\s*\/?\>/i", "\n", $message);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/'.$domain.'/messages');
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => $from,
			'to' => $to,
			'subject' => $subject,
			'html' => $message,
			'text' => $plain));

		$j = json_decode(curl_exec($ch));

		$info = curl_getinfo($ch);

		if($info['http_code'] != 200)
		error("Fel 313: Vänligen meddela detta via E-post till ".$from);

		curl_close($ch);

		print_r($j);
	}

}

 ?>