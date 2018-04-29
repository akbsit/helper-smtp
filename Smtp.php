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
    private $arSettings = [];

    /**
     * @var array
     */
    public $arError = [
        "status" => false
    ];

    /**
     * Smtp constructor.
     * @param $arSettings
     */
    public function __construct($arSettings)
    {
        try {
            if (isset($arSettings["maillogin"],
                    $arSettings["mailpass"],
                    $arSettings["from"],
                    $arSettings["host"])
                && !empty($arSettings["maillogin"])
                && !empty($arSettings["mailpass"])
                && !empty($arSettings["from"])
                && !empty($arSettings["host"])) {
                $this->arSettings = $arSettings;
            } else {
                throw new Exception("Init error");
            }

            if (!isset($arSettings["port"]) || empty($arSettings["port"])) {
                $this->arSettings["port"] = 25;
            }

            if (!isset($arSettings["charset"]) || empty($arSettings["charset"])) {
                $this->arSettings["charset"] = "utf-8";
            }

            if (!isset($arSettings["rpvalid"]["mail"]) || empty($arSettings["rpvalid"]["mail"])) {
                $this->arSettings["rpvalid"]["mail"] = "/^[a-z0-9_][a-z0-9\._-]*@([a-z0-9]+([a-z0-9-]*[a-z0-9]+)*\.)+[a-z]+$/i";
            }
        } catch (Exception $e) {
            $this->arError["status"] = true;
            $this->arError["message"] = $e->getMessage();
            $this->arError["file"] = $e->getFile();
            $this->arError["line"] = $e->getLine();
        }
    }

    /**
     * @param $sMail
     * @param string $sSubject
     * @param string $sMessage
     * @return bool
     */
    public function send($sMail, $sSubject = "", $sMessage = "")
    {
        if (!$this->arError["status"]) {
            try {
                if (!preg_match($this->arSettings["rpvalid"]["mail"], $sMail)) {
                    throw new Exception("Mail error: " . $sMail . " not valid");
                }

                $sendContent = "Date: " . date("D, d M Y H:i:s") . " UT\r\n";
                $sendContent .= "Subject: =?" . $this->arSettings["charset"] . "?B?" . base64_encode($sSubject) . "=?=\r\n";
                $sendContent .= "MIME-Version: 1.0\r\n";
                $sendContent .= "Content-type: text/html; charset=" . $this->arSettings["charset"] . "\r\n";
                $sendContent .= "From: =?" . $this->arSettings["charset"] . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode($this->arSettings["from"]))) . "?= <" . $this->arSettings["maillogin"] . ">\r\n";
                $sendContent .= "To: <" . $sMail . ">\r\n";
                $sendContent .= "\r\n";
                $sendContent .= $sMessage . "\r\n";

                $rConnection = @fsockopen($this->arSettings["host"], $this->arSettings["port"], $errno, $errstr, 30);

                if (!$rConnection) {
                    throw new Exception("Connection error level 1");
                }

                if (!$this->getData($rConnection, "220")) {
                    throw new Exception("Connection error level 2");
                }

                fputs($rConnection, "HELO " . $_SERVER["SERVER_NAME"] . "\r\n");
                if (!$this->getData($rConnection, "250")) {
                    throw new Exception("Error command: HELO");
                }

                fputs($rConnection, "AUTH LOGIN\r\n");
                if (!$this->getData($rConnection, "334")) {
                    throw new Exception("Autorization error command: AUTH LOGIN");
                }

                fputs($rConnection, base64_encode($this->arSettings["maillogin"]) . "\r\n");
                if (!$this->getData($rConnection, "334")) {
                    throw new Exception("Autorization error: login");
                }

                fputs($rConnection, base64_encode($this->arSettings["mailpass"]) . "\r\n");
                if (!$this->getData($rConnection, "235")) {
                    throw new Exception("Autorization error: password");
                }

                fputs($rConnection, "MAIL FROM: <" . $this->arSettings["maillogin"] . ">\r\n");
                if (!$this->getData($rConnection, "250")) {
                    throw new Exception("Error command: MAIL FROM");
                }

                fputs($rConnection, "RCPT TO: <" . $sMail . ">\r\n");
                if (!$this->getData($rConnection, "250")) {
                    throw new Exception("Error command: RCPT TO");
                }

                fputs($rConnection, "DATA\r\n");
                if (!$this->getData($rConnection, "354")) {
                    throw new Exception("Error command: DATA");
                }

                fputs($rConnection, $sendContent . "\r\n.\r\n");
                if (!$this->getData($rConnection, "250")) {
                    throw new Exception("Error: mail didn't sent");
                }

                fputs($rConnection, "QUIT\r\n");

                fclose($rConnection);

                return true;
            } catch (Exception $e) {
                $this->arError["status"] = true;
                $this->arError["message"] = $e->getMessage();
                $this->arError["file"] = $e->getFile();
                $this->arError["line"] = $e->getLine();
            }
        }

        return false;
    }

    /**
     * @param $rConnection
     * @param $sResponse
     * @return bool
     */
    private function getData($rConnection, $sResponse)
    {
        while (@substr($sResponseServer, 3, 1) != " ") {
            if (!($sResponseServer = fgets($rConnection, 256))) {
                return false;
            }
        }

        if (!(substr($sResponseServer, 0, 3) == $sResponse)) {
            return false;
        }

        return true;
    }
}