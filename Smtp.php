<?php

/**
 * Отправка почты через SMTP протокол
 * Class Smtp
 */
class Smtp
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    public $error = [
        "status" => false
    ];

    /**
     * Smtp constructor.
     * @param $settings
     */
    public function __construct($settings)
    {
        try {
            if (isset($settings["maillogin"],
                    $settings["mailpass"],
                    $settings["from"],
                    $settings["host"])
                && !empty($settings["maillogin"])
                && !empty($settings["mailpass"])
                && !empty($settings["from"])
                && !empty($settings["host"])) {
                $this->settings = $settings;
            } else {
                throw new Exception("Init error");
            }

            if (!isset($settings["port"]) || empty($settings["port"])) {
                $this->settings["port"] = 25;
            }

            if (!isset($settings["charset"]) || empty($settings["charset"])) {
                $this->settings["charset"] = "utf-8";
            }

            if (!isset($settings["rpvalid"]["mail"]) || empty($settings["rpvalid"]["mail"])) {
                $this->settings["rpvalid"]["mail"] = "/^[a-z0-9_][a-z0-9\._-]*@([a-z0-9]+([a-z0-9-]*[a-z0-9]+)*\.)+[a-z]+$/i";
            }
        } catch (Exception $e) {
            $this->error["status"] = true;
            $this->error["message"] = $e->getMessage();
            $this->error["file"] = $e->getFile();
            $this->error["line"] = $e->getLine();
        }
    }

    /**
     * @param $mail
     * @param string $subject
     * @param string $message
     * @return bool
     */
    public function send($mail, $subject = "", $message = "")
    {
        if (!$this->error["status"]) {
            try {
                if (!preg_match($this->settings["rpvalid"]["mail"], $mail)) {
                    throw new Exception("Mail error: " . $mail . " not valid");
                }

                $sendContent = "Date: " . date("D, d M Y H:i:s") . " UT\r\n";
                $sendContent .= "Subject: =?" . $this->settings["charset"] . "?B?" . base64_encode($subject) . "=?=\r\n";
                $sendContent .= "MIME-Version: 1.0\r\n";
                $sendContent .= "Content-type: text/html; charset=" . $this->settings["charset"] . "\r\n";
                $sendContent .= "From: =?" . $this->settings["charset"] . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode($this->settings["from"]))) . "?= <" . $this->settings["maillogin"] . ">\r\n";
                $sendContent .= "To: <" . $mail . ">\r\n";
                $sendContent .= "\r\n";
                $sendContent .= $message . "\r\n";

                $connection = @fsockopen($this->settings["host"], $this->settings["port"], $errno, $errstr, 30);

                if (!$connection) {
                    throw new Exception("Connection error level 1");
                }

                if (!$this->getData($connection, "220")) {
                    throw new Exception("Connection error level 2");
                }

                fputs($connection, "HELO " . $_SERVER["SERVER_NAME"] . "\r\n");
                if (!$this->getData($connection, "250")) {
                    throw new Exception("Error command: HELO");
                }

                fputs($connection, "AUTH LOGIN\r\n");
                if (!$this->getData($connection, "334")) {
                    throw new Exception("Autorization error command: AUTH LOGIN");
                }

                fputs($connection, base64_encode($this->settings["maillogin"]) . "\r\n");
                if (!$this->getData($connection, "334")) {
                    throw new Exception("Autorization error: login");
                }

                fputs($connection, base64_encode($this->settings["mailpass"]) . "\r\n");
                if (!$this->getData($connection, "235")) {
                    throw new Exception("Autorization error: password");
                }

                fputs($connection, "MAIL FROM: <" . $this->settings["maillogin"] . ">\r\n");
                if (!$this->getData($connection, "250")) {
                    throw new Exception("Error command: MAIL FROM");
                }

                fputs($connection, "RCPT TO: <" . $mail . ">\r\n");
                if (!$this->getData($connection, "250")) {
                    throw new Exception("Error command: RCPT TO");
                }

                fputs($connection, "DATA\r\n");
                if (!$this->getData($connection, "354")) {
                    throw new Exception("Error command: DATA");
                }

                fputs($connection, $sendContent . "\r\n.\r\n");
                if (!$this->getData($connection, "250")) {
                    throw new Exception("Error: mail didn't sent");
                }

                fputs($connection, "QUIT\r\n");

                fclose($connection);

                return true;
            } catch (Exception $e) {
                $this->error["status"] = true;
                $this->error["message"] = $e->getMessage();
                $this->error["file"] = $e->getFile();
                $this->error["line"] = $e->getLine();
            }
        }

        return false;
    }

    /**
     * @param $connection
     * @param $response
     * @return bool
     */
    private function getData($connection, $response)
    {
        while (@substr($responseServer, 3, 1) != " ") {
            if (!($responseServer = fgets($connection, 256))) {
                return false;
            }
        }

        if (!(substr($responseServer, 0, 3) == $response)) {
            return false;
        }

        return true;
    }
}