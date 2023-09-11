<?php

namespace PromCMS\Core\Services;

class PasswordService
{

  public function __construct()
  {
  }

  private function removeSpaces(string $text)
  {
    return preg_replace('/\s+/', '', $text);
  }

  /**
   * Validates string input by password schema
   */
  public function validateInput(string $input)
  {
    if (is_string(!$input) || strlen($input) < 6) {
      return false;
    }

    return $input;
  }

  public function generate(string $password)
  {
    $spacelessPassword = $this->removeSpaces($password);

    return password_hash($spacelessPassword, PASSWORD_DEFAULT, [
      'cost' => 12
    ]);
  }

  public function validate(string $newPassword, string $hashedPassword)
  {
    $spacelessPassword = $this->removeSpaces($newPassword);

    return password_verify($spacelessPassword, $hashedPassword);
  }
}
