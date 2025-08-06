<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;

class SiteController extends Controller
{
    public function actionStatistic()
    {
        $request = Yii::$app->request;

        // Обработка фильтров
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $params = [];
        $where = [];

        if ($dateFrom) {
            $where[] = 'requested_at >= :from';
            $params[':from'] = $dateFrom;
        }

        if ($dateTo) {
            $where[] = 'requested_at <= :to';
            $params[':to'] = $dateTo;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Общее количество запросов по датам
        $totalRequests = Yii::$app->db->createCommand("
            SELECT DATE(requested_at) AS date, COUNT(*) AS total
            FROM log $whereClause
            GROUP BY DATE(requested_at)
            ORDER BY DATE(requested_at)
        ", $params)->queryAll();

        // URL статистика по дням
        $urlStats = Yii::$app->db->createCommand("
            SELECT DATE(requested_at) AS date, url, COUNT(*) AS total
            FROM log $whereClause
            GROUP BY DATE(requested_at), url
            ORDER BY DATE(requested_at), total DESC
        ", $params)->queryAll();

        // Браузеры по дням
        $browserStats = Yii::$app->db->createCommand("
            SELECT DATE(requested_at) AS date, browser, COUNT(*) AS total
            FROM log $whereClause
            GROUP BY DATE(requested_at), browser
            ORDER BY DATE(requested_at), total DESC
        ", $params)->queryAll();

        // Определение самого популярного URL и браузера по дате
        $topUrls = [];
        foreach ($urlStats as $row) {
            $date = $row['date'];
            if (!isset($topUrls[$date]) || $row['total'] > $topUrls[$date]['total']) {
                $topUrls[$date] = ['url' => $row['url'], 'total' => $row['total']];
            }
        }

        $topBrowsers = [];
        foreach ($browserStats as $row) {
            $date = $row['date'];
            if (!isset($topBrowsers[$date]) || $row['total'] > $topBrowsers[$date]['total']) {
                $topBrowsers[$date] = ['browser' => $row['browser'], 'total' => $row['total']];
            }
        }

        // Таблица
        $dailyStats = [];
        foreach ($totalRequests as $row) {
            $date = $row['date'];
            $dailyStats[] = [
                'date' => $date,
                'total_requests' => $row['total'],
                'top_url' => $topUrls[$date]['url'] ?? '-',
                'top_browser' => $topBrowsers[$date]['browser'] ?? '-',
            ];
        }

        // График долей 3х самых популярных браузеров
        $browserData = [];
        foreach ($browserStats as $row) {
            $date = $row['date'];
            $browser = $row['browser'];
            $count = $row['total'];

            $browserData[$date]['total'] = ($browserData[$date]['total'] ?? 0) + $count;
            $browserData[$date]['browsers'][$browser] = $count;
        }

        $browserChart = [];
        foreach ($browserData as $date => $data) {
            arsort($data['browsers']);
            $topBrowsers = array_slice($data['browsers'], 0, 3);
            $total = $data['total'];

            $entry = ['date' => $date];
            foreach ($topBrowsers as $browser => $count) {
                $entry[$browser] = round($count / $total * 100, 2);
            }

            $browserChart[] = $entry;
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $dailyStats,
            'sort' => [
                'attributes' => ['date', 'total_requests', 'top_url', 'top_browser'],
            ],
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('statistics', [
            'dataProvider' => $dataProvider,
            'chartTotalRequests' => $totalRequests,
            'chartBrowserShares' => $browserChart,
            'requestData' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
