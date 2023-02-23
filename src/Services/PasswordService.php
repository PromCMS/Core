<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Exception;
use Rakit\Validation\Validator;

class PasswordService
{
  private Validator $validator;

  public function __construct()
  {
    $this->validator = new Validator();
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
    $validationResult = $this->validator->validate(["password" => $input], [
      'password' => 'required|min:6',
    ]);

    if ($validationResult->fails()) {
      return false;
    }

    return $validationResult->getValidatedData()["password"];
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
