<?php

namespace PromCMS\Core\Services;

use PromCMS\Core\Password;

// TODO Remove
/**
 * @deprec User PromCMS\Core\Password instead
 */
class PasswordService
{

  public function __construct()
  {
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
    return Password::hash($password);
  }

  public function validate(string $newPassword, string $hashedPassword)
  {
    return Password::check($newPassword, $hashedPassword);
  }
}
