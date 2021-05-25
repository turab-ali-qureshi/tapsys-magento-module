<?php
namespace Tapsys\Checkout\Api;

interface EndpointInterface
{
  /**
   *      
   * Returns greeting message to user
   *
   * @api
   * @param string $currency
   * @return string Greeting message with users data.
   */
  public function data(
    $currency
  );
}
