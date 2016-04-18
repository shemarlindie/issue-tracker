<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 4/11/16
 * Time: 10:56 PM
 *
 * Adopted from Symfony FOSUserBundle
 */

namespace AppBundle\Util;


class TokenGenerator {
  private $useOpenSsl;

  public function __construct() {
    // determine whether to use OpenSSL
    if (defined('PHP_WINDOWS_VERSION_BUILD') && version_compare(PHP_VERSION, '5.3.4', '<')) {
      $this->useOpenSsl = FALSE;
    }
    elseif (!function_exists('openssl_random_pseudo_bytes')) {
      $this->useOpenSsl = FALSE;
    }
    else {
      $this->useOpenSsl = TRUE;
    }
  }

  public function generateToken() {
    return rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');
  }

  private function getRandomNumber() {
    $nbBytes = 32;

    // try OpenSSL
    if ($this->useOpenSsl) {
      $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);

      if (FALSE !== $bytes && TRUE === $strong) {
        return $bytes;
      }
    }

    return hash('sha256', uniqid(mt_rand(), TRUE), TRUE);
  }
}
