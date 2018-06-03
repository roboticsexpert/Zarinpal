<?php
/**
 * Created by PhpStorm.
 * User: roboticsexpert
 * Date: 5/14/17
 * Time: 12:26 AM
 */

namespace Sibapp\Domains\PlanDomain\Services\PaymentGateways;


class ZarinpalSandbox implements IGateway
{

    const MerchantId = '91243184-2ac3-11e6-91df-000c295eb8fc';
    //const SoapUrl='https://ir.zarinpal.com/pg/services/WebGate/wsdl';
    const SoapUrl = 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl';
    const PayUrl = 'https://sandbox.zarinpal.com/pg/StartPay/';

    /**
     * @param $callbackUrl
     * @param int $price
     * @param $transactionKey
     * @param $description
     * @param null $email
     * @param null $mobile
     * @return bool|GatewayRequestAnswer
     */
    public function request($callbackUrl, int $price, $transactionKey, $description, $email = null, $mobile = null)
    {
        $options = array(
            'MerchantID' => self::MerchantId,
            'Amount' => $price,
            'Description' => $description,
            'CallbackURL' => $callbackUrl
        );

        if (!empty($email))
            $options['Email'] = $email;

        // URL also Can be https://ir.zarinpal.com/pg/services/WebGate/wsdl
        $client = new \SoapClient(self::SoapUrl, array('encoding' => 'UTF-8'));

        $result = $client->PaymentRequest($options);

        //Redirect to URL You can do it also by creating a form
        if ($result->Status == 100) {
            return new GatewayRequestAnswer($result->Authority, new GatewayRequestUrl(self::PayUrl . $result->Authority));
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
    public function verify(int $price, $authority, $transactionKey, $requestParams = [])
    {

        $price = $this->normalizePrice($price);
        $client = new \SoapClient(self::SoapUrl, array('encoding' => 'UTF-8'));

        $result = $client->PaymentVerification(
            array(
                'MerchantID' => self::MerchantId,
                'Authority' => $authority,
                'Amount' => $price
            )
        );

        return new GatewayVerifyAnswer(in_array($result->Status, [100, 101]), $this->deNormalizePrice($price), $result->RefID);
    }

    /**
     * @param $price
     * @return mixed
     */
    private function normalizePrice($price): int
    {
        return $price * 10;
    }

    private function deNormalizePrice($price): int
    {
        return $price / 10;
    }
}

