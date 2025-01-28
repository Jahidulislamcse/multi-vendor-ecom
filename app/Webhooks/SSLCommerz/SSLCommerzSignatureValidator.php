<?php

namespace App\Webhooks\SSLCommerz;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class SSLCommerzSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (! $request->has('verify_key') || ! $request->has('verify_sign')) {
            return false;
        }

        $verifyKey = $request->input('verify_key');
        $verifySign = $request->input('verify_sign');

        $preDefineKey = explode(',', $verifyKey);
        $newData = [];

        foreach ($preDefineKey as $value) {
            if ($request->has($value)) {
                $newData[$value] = $request->input($value);
            }
        }

        $newData['store_passwd'] = md5($config->signingSecret);
        ksort($newData);
        $hashString = '';
        foreach ($newData as $key => $value) {
            $hashString .= $key.'='.($value).'&';
        }

        $hashString = rtrim($hashString, '&');

        return md5($hashString) === $verifySign;
    }
}
