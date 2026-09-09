<?php

namespace PromCMS\Core;

class Password
{
  private static function removeSpaces(string $text)
  {
    return preg_replace('/\s+/', '', $text);
  }

  /**
   * Validates string input by password schema
   */
  public static function validateNew(mixed $input): bool
  {
    return is_string($input) && strlen(static::removeSpaces($input)) >= 6;
  }

  public static function hash(string $password)
  {
    $spacelessPassword = static::removeSpaces($password);

    return password_hash($spacelessPassword, PASSWORD_DEFAULT, [
      'cost' => 12
    ]);
  }

  public static function check(string $newPassword, string $hashedPassword)
  {
    $spacelessPassword = static::removeSpaces($newPassword);

    return password_verify($spacelessPassword, $hashedPassword);
  }
}