<?php

require_once 'vendor/autoload.php';

use PayPal\Api\Agreement;

$config = new PayPal\Rest\ApiContext(
  new PayPal\Auth\OAuthTokenCredential(
      '',//Client ID;
      ''//Client Secret;
  )
);


$config->setConfig(array(
  'mode'=>'sandbox',
));

if (isset($_GET['success']) && $_GET['success'] == 'true') {
    var_dump('Usuário efetuou o pagamento com sucesso.');
    $token = $_GET['token'];
    $agreement = new Agreement();
  
    try {
        $agreement->execute($token, $config);
    } catch (Exception $ex) {
        exit(1);
    }
  
    $agreement = Agreement::get($agreement->getId(), $config);
  
    echo "ID da assinatura: " . $agreement->getId();
  } else {
    var_dump('Usuário cancelou pagamento/não conseguiu pagar.');
  }
///https://developer.paypal.com/docs/api/payments.billing-agreements/v1/#billing-agreements
