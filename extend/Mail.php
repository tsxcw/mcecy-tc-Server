<?php

namespace extend;


use app\model\Settings;
use think\facade\Log;
use Nette\Mail\SmtpMailer;
use Nette\Mail\Message;
class Mail
{
  public static function send($to = "", $text = "")
  {

    $template = <<<Eof
    <h2>创次元令牌</h2>
    <p>
       <span>您的本次获取的令牌为</span>
       <strong style='color:#4b9fff' >$text</strong>
    </p>
    Eof;
    $mail = new Message;
    $mail->setFrom('创次元 <admin@mcecy.com>')
      ->addTo($to)
      ->setSubject('创次元动态令牌')
      ->setHtmlBody($template);
    $config = Settings::find("smtpServer");
    $mailer = new SmtpMailer([
      'host' => $config->value->host,
      'username' => $config->value->username,
      'password' => $config->value->password,
      'secure' => 'ssl',
    ]);
    try {
      $mailer->send($mail);
    } catch (\Throwable $th) {
      //throw $th;
      return false;
    }
    return true;
  }
}
