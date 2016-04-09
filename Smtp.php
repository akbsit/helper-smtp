<?php
/*========================================== 
	Appointment: Класс для отправки почты
				 через SMTP протокол
	File: Smtp.php
	Author: Anton Kuliashou
	Site: http://falbar.ru/
===========================================*/

class Smtp{

	private $settings = array();

	public $error = array(
		"status" => false
	);

	public function __construct($settings){

		try{

			if(isset($settings["maillogin"],
					 $settings["mailpass"],
					 $settings["from"],
					 $settings["host"])
			   && !empty($settings["maillogin"])
			   && !empty($settings["mailpass"])
			   && !empty($settings["from"])
			   && !empty($settings["host"])){

				$this->settings = $settings;
			}else{

				throw new Exception("Init error");
			}

			if(!isset($settings["port"]) || empty($settings["port"])){

				$this->settings["port"] = 25;
			}

			if(!isset($settings["charset"]) || empty($settings["charset"])){

				$this->settings["charset"] = "utf-8";
			}

			if(!isset($settings["rpvalid"]["mail"]) || empty($settings["rpvalid"]["mail"])){

				$this->settings["rpvalid"]["mail"] = "/^[a-z0-9_][a-z0-9\._-]*@([a-z0-9]+([a-z0-9-]*[a-z0-9]+)*\.)+[a-z]+$/i";
			}
		}catch(Exception $e){

			$this->error["status"]  = true;
			$this->error["message"] = $e->getMessage();
			$this->error["file"]    = $e->getFile();
			$this->error["line"]    = $e->getLine();
		}
	}

	public function send($mail, $subject = "", $message = ""){

		if(!$this->error["status"]){

			try{

				if(!preg_match($this->settings["rpvalid"]["mail"], $mail)){

					throw new Exception("Mail error: ".$mail." not valid");
				}

				$send_content  = "Date: ".date("D, d M Y H:i:s")." UT\r\n";
				$send_content .= "Subject: =?".$this->settings["charset"]."?B?".base64_encode($subject)."=?=\r\n";
				$send_content .= "MIME-Version: 1.0\r\n";
				$send_content .= "Content-type: text/html; charset=".$this->settings["charset"]."\r\n";
				$send_content .= "From: =?".$this->settings["charset"]."?Q?".str_replace("+", "_", str_replace("%", "=", urlencode($this->settings["from"])))."?= <".$this->settings["maillogin"].">\r\n";
				$send_content .= "To: <".$mail.">\r\n";
				$send_content .= "\r\n";
				$send_content .= $message."\r\n";

				$connection = @fsockopen($this->settings["host"], $this->settings["port"], $errno, $errstr, 30);

				if(!$connection){

					throw new Exception("Connection error level 1");
				}

				if(!$this->get_data($connection, "220")){

					throw new Exception("Connection error level 2");
				}

				fputs($connection, "HELO ".$_SERVER["SERVER_NAME"]."\r\n");
				if(!$this->get_data($connection, "250")){

					throw new Exception("Error command: HELO");
				}

				fputs($connection, "AUTH LOGIN\r\n");
				if(!$this->get_data($connection, "334")){

					throw new Exception("Autorization error command: AUTH LOGIN");
				}

				fputs($connection, base64_encode($this->settings["maillogin"])."\r\n");
				if(!$this->get_data($connection, "334")){

					throw new Exception("Autorization error: login");
				}

				fputs($connection, base64_encode($this->settings["mailpass"])."\r\n");
				if(!$this->get_data($connection, "235")){

					throw new Exception("Autorization error: password");
				}

				fputs($connection, "MAIL FROM: <".$this->settings["maillogin"].">\r\n");
				if(!$this->get_data($connection, "250")){

					throw new Exception("Error command: MAIL FROM");
				}

				fputs($connection, "RCPT TO: <".$mail.">\r\n");
				if(!$this->get_data($connection, "250")){

					throw new Exception("Error command: RCPT TO");
				}

				fputs($connection, "DATA\r\n");
				if(!$this->get_data($connection, "354")){

					throw new Exception("Error command: DATA");
				}

				fputs($connection, $send_content."\r\n.\r\n");
				if(!$this->get_data($connection, "250")){

					throw new Exception("Error: mail didn't sent");
				}

				fputs($connection, "QUIT\r\n");

				fclose($connection);

				return true;
			}catch(Exception $e){

				$this->error["status"]  = true;
				$this->error["message"] = $e->getMessage();
				$this->error["file"]    = $e->getFile();
				$this->error["line"]    = $e->getLine();
			}
		}

		return false;
	}

	private function get_data($connection, $response){

		while(@substr($responseServer, 3, 1) != " ") {

			if(!($responseServer = fgets($connection, 256))){

				return false;
			}
		}

		if(!(substr($responseServer, 0, 3) == $response)){

			return false;
		}

		return true;
	}
}
?>