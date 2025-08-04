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

        $batchSize = 500;
        $batchData = [];

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

            $batchData[] = [
                $match['ip'],
                date('Y-m-d H:i:s', strtotime($match['date'])),
                $url,
                $match['useragent'],
                $os,
                $arch,
                $browser,
            ];

            if (count($batchData) >= $batchSize) {
                $this->saveBatch($batchData);
                $batchData = [];
            }
            $parsed++;
        }

        if (!empty($batchData)) {
            $this->saveBatch($batchData);
        }

        echo "Всего строк: $lineNum, успешно разобрано: $parsed\n";
    }

    private function saveBatch(array $batchData)
    {
        $columns = ['ip', 'requested_at', 'url', 'user_agent', 'os', 'architecture', 'browser'];

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            \Yii::$app->db->createCommand()->batchInsert(
                Log::tableName(),
                $columns,
                $batchData
            )->execute();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo "Ошибка при вставке: " . $e->getMessage() . "\n";
            throw $e;
        }
        gc_collect_cycles();
    }
}
