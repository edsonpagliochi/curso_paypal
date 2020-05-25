<?php

require_once 'vendor/autoload.php';

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Common\PayPalModel;

$config = new PayPal\Rest\ApiContext(
    new PayPal\Auth\OAuthTokenCredential(
        '',//Client ID;
        ''//Client Secret;
    )
);


$config->setConfig(array(
    'mode'=>'sandbox',
));


$plano = $_GET['plano'];
$valor;

if ($plano == "1"){
    $valor = "15.99";
    $nome = "Plano Light";
    $descricao = "Assinatura do plano light";
}else{
    $valor = "35.99";
    $nome = "Plano Completo";
    $descricao = "Assinatura do plano completo";
}

function createPlan($config, $valor, $frequencia, $nome, $descricao){
    $definition = new PaymentDefinition();
    $definition->setName('Regular Payments')
    ->setType('REGULAR')
    ->setFrequency($frequencia)
    ->setFrequencyInterval("1")
    ->setCycles("0")
    ->setAmount(new Currency((array('value'=> $valor, 'currency'=> 'BRL'))));

    $plan = new Plan();
    $plan->setName($nome)
    ->setDescription($descricao)
    ->setType("INFINITE");

    $plan->setPaymentDefinitions(array($definition));

    $preferences = new MerchantPreferences();
    $preferences->setReturnUrl('https://cursopaypal.tk/return.php?success=true')
    ->setCancelUrl('https://cursopaypal.tk/return.php?success=false')
    ->setAutoBillAmount("YES")
    ->setInitialFailAmountAction("CONTINUE")
    ->setMaxFailAttempts("3");

    $plan->setMerchantPreferences($preferences);

    return $plan->create($config);

}

function activePlan($plan, $config){
    $patch = new Patch();

    $value = new PayPalModel('{"state": "ACTIVE"}');

    $patch->setOp('replace')
    ->setPath('/')
    ->setValue($value);
    $patchRequest = new PatchRequest();
    $patchRequest->addPatch($patch);
   
    $plan->update($patchRequest, $config);
    return Plan::get($plan->getId(), $config);
}

function createAgreement($config, $createdPlan, $nome, $descricao){
    $agreement = new Agreement();
    $agreement->setName($nome)
    ->setDescription($descricao);
    $data = date("Y-m-d", mktime(0, 0, 0, date("m"), (date("d") + 1), date("Y"))) . "T00:00:00Z";
    
    $agreement->setStartDate($data);

    $plan = new Plan();
    $plan->setId($createdPlan->getId());

    $agreement->setPlan($plan);

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $agreement->setPayer($payer);

    return $agreement->create($config);  

}

function redirectUser($url){
    header('Location: ' . $url);
}

$userPlan = createPlan($config, $valor, "Month", $nome, $descricao);

$userPlan = activePlan($userPlan, $config);

$userSubscription = createAgreement($config, $userPlan, $nome, $descricao);

redirectUser($userSubscription->getApprovalLink());


//ed.fernando87@gmail.com