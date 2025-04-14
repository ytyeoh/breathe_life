<?php

require_once '../vendor/autoload.php';
require_once '../secrets.php';

\Stripe\Stripe::setApiKey($stripeSecretKey);

header('Content-Type: application/json');

// $YOUR_DOMAIN = 'http://localhost:4242';
$YOUR_DOMAIN = 'https://breathelife.org.my';
// echo "<pre>";print_r($_POST);die();
try {
  $prices = \Stripe\Price::all([
    // retrieve lookup_key from form data POST body
    'lookup_keys' => [$_POST['lookup_key']],
    'expand' => ['data.product']
  ]);
  if ($_POST['newsletter']=='on'){
    $metadata = array('newsletter' => "Yes");
  } else {
    $metadata = array();
  }
  $checkout_session = \Stripe\Checkout\Session::create([
    'line_items' => [[
      'price' => $prices->data[0]->id,
      'quantity' => $_POST['lookup_quantity'],
    ]],
    'subscription_data' => [
        'metadata' => $metadata
    ],
    'mode' => 'subscription',
    'success_url' => $YOUR_DOMAIN . '/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
  ]);

  header("HTTP/1.1 303 See Other");
  header("Location: " . $checkout_session->url);
} catch (Error $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}