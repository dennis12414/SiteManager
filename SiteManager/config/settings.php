<?php

return [
    /**
     * ---------------------------------
     * Dummy details for testing purposes
     * ---------------------------------
     */
    'dummyPhoneNumber' => env('DUMMY_PHONE_NUMBER', '0712345678'),
    'dummyEmail' => env('DUMMY_EMAIL', 'testemail@gmail.com'),
    'dummyOTP' => env('DUMMY_OTP', '123456'),

    /**
     *  -----------------------------
     * Configuration for sending sms
     * ------------------------------
     */
    'notificationCode' => env('SMS_NOTIFICATION_CODE', 'PMANAGER-SMS'),
    'smsUrl' => env('SMS_URL','http://172.105.90.112:8080/notification-api/v1/notification/create'),
    'subject' => env('SUBJECT','SMS Test'),


    /**
     *  -----------------------------
     * Configuration for authentication
     * ------------------------------
     */
    'authUrl' => env('AUTH_URL','http://172.105.90.112:8080/paymentexpress/v1/client/users/authenticate'),
    'username' => env('AUTHUSERNAME','ikoaqua-mpesa-user'),
    'password' => env('PASSWORD','F5Hm5CNDg0kG'),

    /**
     * -----------------------------
     * Configuration B2C
     * ------------------------------
     */
    'b2cUrl' => env('B2C_URL','http://172.105.90.112:8080/paymentexpress/v1/payment/create'),
    'payerNarration' => env('PAYER_NARRATION','Payment Completed Successfully'),
    'paymentType' => env('PAYMENT_TYPE','BusinessPayment'),
    'serviceCode' => env('SERVICE_CODE','MPESAB2C'),
    'currencyCode' => env('CURRENCY','KES'),
    'callbackUrl' => env('CALLBACK_URL','https://16ab-197-136-108-65.ngrok-free.app'),



    /**
     * -----------------------------
     * Configuration C2B
     * -----------------------------
     */

    'c2bUrl' => env('C2B_URL','http://172.105.90.112:8080/paymentexpress/v1/paymentrequest/initiate'),
    'paymentCode' => env('PAYMENT_CODE','174379'),
    'paymentOption' => env('PAYMENT_OPTION','MPESA'),
    'C2BserviceCode' => env('SERVICE_CODE','SITEMANAGER-COLLECTIONS'),
    'accountNumber' => env('ACCOUNT_NUMBER','TestAccount'),
    //'partnerCallbackUrl' => env('PARTNER_CALLBACK_URL','http://172.105.90.112/site-manager-backend/SiteManager'),
    'partnerCallbackUrl' => env('PARTNER_CALLBACK_URL','https://16ab-197-136-108-65.ngrok-free.app'),
    'narration' => env('NARRATION','Making Test Payment'),

];

