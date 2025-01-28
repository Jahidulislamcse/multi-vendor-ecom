<?php

namespace App\Services;

use Http;
use Str;

class ImageproxyService
{
    private ?int $signatureSize = null;

    public function __construct()
    {
        if (config('services.imgproxy.signature_size')) {
            $signatureSize = config('services.imgproxy.signature_size');

            if (is_numeric($signatureSize)) {
                $this->signatureSize = (int) $signatureSize;
            }
        }
    }

    public function makeSignedUrl(string $processingOpts, string $sourceUrl): string
    {
        $processingOpts = Str::of($processingOpts)->startsWith('/') ? $processingOpts : "/{$processingOpts}";
        $path = $this->getPath($processingOpts, $sourceUrl);
        $signature = hash_hmac(
            'sha256',
            $this->getBinarySalt().$path,
            $this->getBinaryKey(),
            true
        );

        if ($this->signatureSize) {
            $signature = pack('A'.$this->signatureSize, $signature);
        }

        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return sprintf('/%s%s', $signature, $path);
    }

    public function getKey(): string
    {
        $key = config('services.imgproxy.key');

        return $key;
    }

    public function getSalt(): string
    {
        $salt = config('services.imgproxy.salt');

        return $salt;
    }

    public function getBinaryKey(): string
    {
        $keyBin = pack('H*', $this->getKey());

        return $keyBin;
    }

    public function getBinarySalt(): string
    {
        $saltBin = pack('H*', $this->getSalt());

        return $saltBin;
    }

    public function getEncodedUrl(string $sourceUrl): string
    {
        return rtrim(strtr(base64_encode($sourceUrl), '+/', '-_'), '=');
    }

    /**
     * converts source url to base64 and merges with processing options
     *
     * @param  string  $processingOpts https://docs.imgproxy.net/usage/processing
     * @param  string  $sourceUrl url of image/video which will be modified
     */
    public function getPath(string $processingOpts, string $sourceUrl): string
    {
        $extension = pathinfo($sourceUrl, PATHINFO_EXTENSION);
        $encodedSourceUrl = $this->getEncodedURL($sourceUrl);
        $path = "{$processingOpts}/{$encodedSourceUrl}.{$extension}";

        return $path;
    }

    public function proxy(string $processingOpts, string $sourceUrl)
    {
        $signedUrl = $this->makeSignedUrl($processingOpts, $sourceUrl);

        $response = Http::withToken(config('services.imgproxy.secret'))
            ->baseUrl(config('services.imgproxy.base_url'))
            ->get($signedUrl);

        $headers = $response->headers();

        return response($response->body())->withHeaders($headers);
    }
}
