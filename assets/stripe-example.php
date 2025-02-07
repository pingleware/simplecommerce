<?php
require 'vendor/autoload.php';
\Stripe\Stripe::setApiKey('your_stripe_secret_key');

// Process a basic charge:
$charge = \Stripe\Charge::create([
  'amount' => 5000,
  'currency' => 'usd',
  'source' => 'tok_visa',
  'description' => 'Example charge'
]);
?>