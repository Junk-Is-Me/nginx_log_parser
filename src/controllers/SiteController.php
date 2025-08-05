<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Log;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

use Src\Services\StatisticsService;

class SiteController extends Controller
{
    public function actionStatistics()
    {
        $request = Yii::$app->request;

        if (!empty($request->get('date_from'))) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $request->get('date_from'));
            $dateFrom = $dateTime->format('Y-m-d');
        }

        if (!empty($request->get('date_to'))) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $request->get('date_to'));
            $dateFrom = $dateTime->format('Y-m-d');
        }

        $queryParts = [];
        $params = [];

        if (!empty($dateFrom)) {
            $queryParts[] = 'requested_at >= :from';
            $params[':from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $queryParts[] = 'requested_at <= :to';
            $params[':to'] = $dateTo;
        }

        $whereClause = '';
        if (!empty($queryParts)) {
            $whereClause = 'WHERE ' . implode(' AND ', $queryParts);
        }

        $totalRequests = Yii::$app->db->createCommand(
            "SELECT DATE(requested_at) as date, COUNT(*) as total FROM log $whereClause GROUP BY DATE(requested_at) ORDER BY DATE(requested_at)",
            $params
        )->queryAll();

        $browserStats = Yii::$app->db->createCommand(
            "SELECT DATE(requested_at) as date, COUNT(*) as total, browser FROM log $whereClause GROUP BY DATE(requested_at), browser ORDER BY DATE(requested_at)",
            $params
        )->queryAll();

        $dates = array_column($totalRequests, 'date');
        $totals = array_column($totalRequests, 'total', 'date');

        $browserData = [];
        foreach ($browserStats as $row) {
            $browser = $row['browser'];
            $date = $row['date'];
            $total = $row['total'];

            if (!isset($browserData[$browser])) {
                $browserData[$browser] = array_fill_keys($dates, 0);
            }

            $browserData[$browser][$date] = $total;
        }

        $browserTotals = [];
        foreach ($browserData as $browser => $requestByDate) {
            $browserTotals[$browser] = array_sum($requestByDate);
        }

        arsort($browserTotals);
        $topBrowsers = array_slice(array_keys($browserTotals), 0, 5);

        $percentData = [];
        foreach ($topBrowsers as $browser) {
            foreach ($dates as $date) {
                $count = $browserData[$browser][$date] ?? 0;
                $totalForDate = $totals[$date] ?? 0;
                $percentData[$browser][] = $totalForDate > 0 ? round($count / $totalForDate * 100, 2) : 0;
            }
        }

        return $this->render('statistics', [
            'dates' => json_encode($dates),
            'totals' => json_encode(array_values($totals)),
            'percentData' => json_encode($percentData),
            'browsers' => json_encode($topBrowsers),
        ]);
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Log::find();

        if ($dateFrom) {
            $query->andWhere(['>=', 'requested_at', $dateFrom . ' 00:00:00']);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'requested_at', $dateTo . ' 23:59:59']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
