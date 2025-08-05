<?php
namespace Src\Services;

class StatisticsService
{
    public function getStatisticsData(array $filters)
    {
        $queryParts = [];
        $params = [];

        $dateFrom = $filters['from'] ?? null;
        $dateTo = $filters['to'] ?? null;
        #$os = $filters['os'] ?? null;
        #$architecture = $filters['architecture'] ?? null;

        $params = [':from' => $dateFrom, ':to' => $dateTo];

        if (!empty($dateFrom)) {
            $queryParts[] = 'requested_at >= :from';
            $params[':from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $queryParts[] = 'requested_at <= :to';
            $params[':to'] = $dateTo;
        }

        $totalRequests = Yii::$app->db->createCommand("
        SELECT DATE(requested_at) as date, COUNT(*) as cnt
        FROM log " . $queryParts ? "WHERE " . implode(' AND ', $queryParts) : '' . "
        GROUP BY DATE(requested_at)
        ORDER BY DATE(requested_at)
        ", $params)->queryAll();

        $browserStats = Yii::$app->db->createCommand("
        SELECT DATE(requested_at) as date, COUNT(*) as cnt
        FROM log " . $queryParts ? "WHERE " . implode(' AND ', $queryParts) : '' . "
        GROUP BY DATE(requested_at), browser
        ORDER BY DATE(requested_at)
        ", $params)->queryAll();

        $dates = array_column($totalRequests, 'date');
        $count = array_column($totalRequests, 'total');

        $browserCount = [];

        foreach ($browserStats as $row) {
            $browser = $row['browser'];
            $browserCount[$browser] = ($browserCount[$browser] ?? 0) + $row['total'];
        }

        arsort($browserCount);

        var_export($browserStats);
    }
}