<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Log;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class SiteController extends Controller
{
    public function actionParseStatistics()
    {
        $query = (new \yii\db\Query())
            ->select([
                'DATE(requested_at) as date',
                'COUNT(*) as total',
                '(SELECT url FROM log l2 WHERE DATE(l2.requested_at) = DATE(l.requested_at) GROUP BY url ORDER BY COUNT(*) DESC LIMIT 1) as top_url',
                '(SELECT browser FROM log l3 WHERE DATE(l3.requested_at) = DATE(l.requested_at) GROUP BY browser ORDER BY COUNT(*) DESC LIMIT 1) as top_browser',
            ])
            ->from('log l')
            ->groupBy(['DATE(requested_at)'])
            ->orderBy(['DATE(requested_at)' => SORT_DESC]);

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination' => false,
            'sort' => [
                'attributes' => ['date', 'total', 'top_url', 'top_browser'],
            ],
        ]);

        return $this->render('parseStatistics', [
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionIndex()
    {
        $request = Yii::$app->request;

        // Получаем фильтры из GET
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Log::find();

        if ($dateFrom) {
            $query->andWhere(['>=', 'requested_at', $dateFrom . ' 00:00:00']);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'requested_at', $dateTo . ' 23:59:59']);
        }

        $query->orderBy(['requested_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
