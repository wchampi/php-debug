<?php
namespace Ch\Debug;

class Util
{
    public static function curlExec($ch, $nroAttempts = 3)
    {
        $retryErrorCodes = [
            CURLE_HTTP_NOT_FOUND,
            CURLE_READ_ERROR,
            CURLE_OPERATION_TIMEOUTED,
            CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_CONNECT,
            CURLE_SSL_CONNECT_ERROR,
            CURLE_HTTP_POST_ERROR
        ];

        while ($nroAttempts > 0) {
            if (curl_exec($ch) === false) {
                $errorCode = curl_errno($ch);
                if (in_array($errorCode, $retryErrorCodes, true) === true) {
                    continue;
                }

                $error = curl_error($ch);

                curl_close($ch);

                throw new \RuntimeException(sprintf('Curl error: [%s] %s', $errorCode, $error));
            }
            break;
        }
        curl_close($ch);

        return true;
    }
}
