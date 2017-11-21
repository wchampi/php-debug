<?php
namespace Ch\Debug\Dumper;

use Ch\Debug\Util;

class SlackWebhookDumper extends AbstractDumper
{
    private $webhookUrl;

    public function __construct($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    public function dump($trace)
    {
        $raw = json_encode($this->getDataToSlack($trace));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhookUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $raw);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return Util::curlExec($ch);
    }

    public function getDataToSlack($trace)
    {
        return $data = [
            'pretext' => $this->getFormattedExtraData($trace),
            'color' => '#36a64f',
            'fields' => [
                [
                    'title' => $this->getFormattedVarLocation($trace),
                    'value' => $this->getFormattedVar($trace),
                    'short' => true
                ]
            ]
        ];
    }

    public function getFormattedExtraData($trace)
    {
        $remoteIp = ($trace['remoteIp'] == 'UNKNOW') ? '' : "\nRemote Ip: $trace[remoteIp]";
        return sprintf(
            "Server: %s(%s)%s",
            getHostName(),
            getHostByName(getHostName()),
            $remoteIp
        );
    }
}
