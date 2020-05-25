<?php

require_once 'vendor/autoload.php';

use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;

$config = new PayPal\Rest\ApiContext(
    new PayPal\Auth\OAuthTokenCredential(
        '',//Client ID;
        ''//Client Secret;
    )
);


$config->setConfig(array(
    'mode'=>'sandbox',
));


/*precisa enviar para o Paypal:
Billing agreement ID:
Note:
*/

$stateDescriptor = new AgreementStateDescriptor();
$stateDescriptor->setNote("Suspensão da assinatura");

$agreement = new Agreement();

$agreement->setId("I-56X9PCXTT13W");

try{

    $agreement->suspend($stateDescriptor, $config);

    $agreement = Agreement::get($agreement->getId(), $config);

    echo $agreement->getId() . ' Assinatura suspensa';


}catch (Exception $ex) {
    exit(1);
}
