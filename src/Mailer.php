<?php
namespace PromCMS\Core;

use GuzzleHttp\Psr7\Uri;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends PHPMailer
{
  private $isSetuped = false;
  private string $passwordResetTemplatePath = 'email/password-reset.twig';
  private string $inviteUserTemplatePath = 'email/password-reset.twig';

  function getPasswordResetTemplatePath()
  {
    return $this->passwordResetTemplatePath;
  }

  function getInviteUserTemplatePath()
  {
    return $this->inviteUserTemplatePath;
  }

  function setPasswordResetTemplatePath(string $path)
  {
    $this->passwordResetTemplatePath = $path;
  }

  function setInviteUserTemplatePath(string $path)
  {
    $this->inviteUserTemplatePath = $path;
  }

  /**
   * Sets up Mailer with specified config and enables email sending
   * 
   * @param Uri $uri An URI instance. Example: new Uri('smtp://user:some-password@some-server:587');
   */
  public function setup(Uri $uri, string|null $sendFrom = null, string|null $sendFromName = null)
  {
    $this->Host = $uri->getHost();

    if (!empty($mailerPort = $uri->getPort())) {
      $this->Port = $mailerPort;
    }

    if (!empty($userInfo = $uri->getUserInfo())) {
      [$username, $password] = explode(':', $userInfo);

      $this->Username = $username;
      $this->Password = $password ?? '';
    }

    if (!empty($fromEmailAddress = $this->Username ?? $sendFrom)) {
      $this->setFrom(
        $fromEmailAddress,
        $sendFromName ?? 'PROM Mailer',
      );
    }

    $this->isSetuped = true;
  }

  public function send(): bool
  {
    if (!$this->isSetuped) {
      return false;
    }

    return parent::send();
  }
}