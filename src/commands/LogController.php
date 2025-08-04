<?php
namespace app\commands;

use yii\console\Controller;
use app\models\Log;
use donatj\UserAgent\UserAgentParser;
class LogController extends Controller
{
    private function parseLogFile(string $filePath): \Generator
    {
        $pattern = '/(?<ip>[0-9.]+)\s+-\s+-\s+\[(?<date>[^\]]+)]\s+"(?<request>[^"]+)"\s+(?<code>\d+)\s+(?<size>\d+)\s+"(?<referer>[^"]*)"\s+"(?<useragent>[^"]*)"/m';

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            throw new \RuntimeException("Cannot open file: $filePath");
        }

        while (($line = fgets($handle)) !== false) {
            if (preg_match($pattern, $line, $match)) {
                yield $match;
            }
        }

        fclose($handle);
    }

    public function actionParseLog($filePath)
    {
        $userAgentParser = new UserAgentParser();
        $lineNum = 0;
        $parsed = 0;

        foreach ($this->parseLogFile($filePath) as $match) {
            $lineNum++;

            $userAgent = $userAgentParser->parse($match['useragent']);
            $browser = $userAgent->browser() ?? 'Unknown';
            $os = $userAgent->platform() ?? 'Unknown';

            $arch = 'Unknown';
            $userAgentLower = strtolower($match['useragent']);
            if (strpos($userAgentLower, 'x86_64') !== false || strpos($userAgentLower, 'wow64') !== false) {
                $arch = 'x64';
            } elseif (strpos($userAgentLower, 'i686') !== false || strpos($userAgentLower, 'x86') !== false) {
                $arch = 'x86';
            } elseif (strpos($userAgentLower, 'arm') !== false) {
                $arch = 'arm';
            }

            $requestParts = explode(' ', $match['request']);
            $url = $requestParts[1] ?? '';

            $log = new Log();
            $log->ip = $match['ip'];
            $log->requested_at = date('Y-m-d H:i:s', strtotime($match['date']));
            $log->url = $url;
            $log->user_agent = $match['useragent'];
            $log->os = $os;
            $log->architecture = $arch;
            $log->browser = $browser;

            if (!$log->save()) {
                echo "Ошибка сохранения записи в строке $lineNum\n";
                print_r($log->getErrors());
            } else {
                $parsed++;
            }
        }

        echo "Всего строк: $lineNum, успешно разобрано: $parsed\n";
    }

}
