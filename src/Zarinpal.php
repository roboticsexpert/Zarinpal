<?php
/**
 * Created by PhpStorm.
 * User: roboticsexpert
 * Date: 5/14/17
 * Time: 12:26 AM
 */

namespace Roboticsexpert\Zarinpal;


use Roboticsexpert\PaymentGateways\GatewayRequestAnswer;
use Roboticsexpert\PaymentGateways\GatewayRequestUrl;
use Roboticsexpert\PaymentGateways\GatewayVerifyAnswer;
use Roboticsexpert\PaymentGateways\IGateway;

class Zarinpal implements ZarinpalInterface
{

    private $merchantId;

    private $soapUrl;
    private $payUrl = 'https://www.zarinpal.com/pg/StartPay/';


    public function __construct($merchantId, $locationSubdomain = 'ir')
    {
        $this->merchantId = $merchantId;
        $this->soapUrl = "https://{$locationSubdomain}.zarinpal.com/pg/services/WebGate/wsdl";

    }

    private function createPayUrl($authority)
    {
        return $this->payUrl . $authority . '/ZarinGate';
    }

    /**
     * @param $callbackUrl
     * @param int $price
     * @param $transactionKey
     * @param $description
     * @param array $userAttributes
     * @return bool|GatewayRequestAnswer
     */
    public function request($callbackUrl, int $price, $transactionKey, $description, $userAttributes = [])
    {
        $options = array(
            'MerchantID' => $this->merchantId,
            'Amount' => $price,
            'Description' => $description,
            'CallbackURL' => $callbackUrl
        );

        if (isset($userAttributes['email']))
            $options['Email'] = $userAttributes['email'];

        // URL also Can be https://ir.zarinpal.com/pg/services/WebGate/wsdl
        $client = new \SoapClient($this->soapUrl, array('encoding' => 'UTF-8'));

        $result = $client->PaymentRequest($options);

        //Redirect to URL You can do it also by creating a form
        if ($result->Status == 100) {
            return new GatewayRequestAnswer($result->Authority, new GatewayRequestUrl($this->createPayUrl($result->Authority)));
        }

        return false;

    }


    /**
     * @param int $price
     * @param $authority
     * @param $transactionKey
     * @param array $requestParams
     * @return GatewayVerifyAnswer
     */
    public
    function verify(int $price, $authority, $transactionKey, $requestParams = [])
    {

        $client = new \SoapClient($this->soapUrl, array('encoding' => 'UTF-8'));

        $result = $client->PaymentVerification(
            array(
                'MerchantID' => $this->merchantId,
                'Authority' => $authority,
                'Amount' => $price
            )
        );
        // 100 and 101 are success payment codes
        return new GatewayVerifyAnswer(in_array($result->Status, [100, 101]), $price, $result->RefID);
    }

    public function getName(): string
    {
        return 'zarinpal';
    }
}
