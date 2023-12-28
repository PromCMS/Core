<?php

namespace PromCMS\Core\Bootstrap;

use PHPMailer\PHPMailer\PHPMailer;
use PromCMS\Core\Mailer as MailerClass;

class Mailer implements AppModuleInterface
{
  public function run($app, $container)
  {
    // Create mailer instance
    $mailer = new MailerClass(true);

    // We only talk in SMTP
    $mailer->isSMTP();

    // We only talk authorized
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = 'ssl';

    // UTF-8 only
    $mailer->CharSet = 'UTF-8';

    $container->set(MailerClass::class, $mailer);
  }
}
