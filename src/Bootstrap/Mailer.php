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

    // Server info
    $mailer->Host = $_ENV['MAIL_HOST'];
    $mailer->Port = $_ENV['MAIL_PORT'];

    // We only talk authorized
    $mailer->SMTPAuth = true;
    $mailer->SMTPSecure = 'ssl';

    // UTF-8 only
    $mailer->CharSet = 'UTF-8';

    // Set login info
    $mailer->Username = $_ENV['MAIL_USER'];
    $mailer->Password = $_ENV['MAIL_PASS'];

    if (!empty($fromEmailAddress = isset($_ENV['MAIL_ADDRESS']) ? $_ENV['MAIL_ADDRESS'] : $_ENV['MAIL_USER'])) {
      // Set from to header
      $mailer->setFrom(
        $fromEmailAddress,
        $_ENV['APP_NAME'] ? $_ENV['APP_NAME'] : 'PROM Mailer',
      );
    }

    $container->set(MailerClass::class, $mailer);
  }
}
