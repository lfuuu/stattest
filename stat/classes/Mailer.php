<?php

include_once INCLUDE_PATH."class.phpmailer.php";
include_once INCLUDE_PATH."class.smtp.php";

class Mailer
{
    private $doer = null;

    public function __construct($fromName = "МСН Телеком", $fromEmail = "info@mcn.ru")
    {
        $this->doer = new PHPMailer();
        $this->doer->SetLanguage("en",INCLUDE_PATH);
        $this->doer->CharSet = "utf-8";
        $this->doer->From = $fromEmail;
        $this->doer->FromName = $fromName;
        $this->doer->Mailer='smtp';
        $this->doer->Host=SMTP_SERVER;
    }

    public function send($to, $subject, $text)
    {
        $this->doer->ContentType='text/html';
        $this->doer->Subject = $subject;
        $this->doer->IsHTML(false);
        $this->doer->Body = $text;

        $this->doer->AddAddress($to);
        $error = "";
        if(!(@$this->doer->Send()))
        {
            $error = $this->doer->ErrorInfo;
        }
        $this->doer->ClearAddresses();

        if ($error)
            throw new Exception($error);

        return true;
    }
}

