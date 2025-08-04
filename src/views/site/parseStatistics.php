<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


$this->title = 'Статистика логов';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
?>

<h1><?= Html::encode($this->title) ?></h1>

<!-- ФИЛЬТРЫ -->
<div class="log-filters">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['site/parse-statistics'],
    ]); ?>

    <?= $form->field($filterModel, 'date_from')->input('date') ?>
    <?= $form->field($filterModel, 'date_to')->input('date') ?>
    <?= $form->field($filterModel, 'os')->textInput() ?>
    <?= $form->field($filterModel, 'architecture')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- ГРАФИК 1 -->
<h3>График: Число запросов по датам</h3>
<canvas id="requestChart"></canvas>

<!-- ГРАФИК 2 -->
<h3>График: Доля популярных браузеров</h3>
<canvas id="browserChart"></canvas>

<!-- ТАБЛИЦА -->
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['attribute' => 'date', 'label' => 'Дата'],
        ['attribute' => 'total', 'label' => 'Число запросов'],
        ['attribute' => 'top_url', 'label' => 'Самый популярный URL'],
        ['attribute' => 'top_browser', 'label' => 'Самый популярный браузер'],
    ],
]); ?>

<?php
$dates = json_encode(array_column($chartData, 'date'));
$totals = json_encode(array_column($chartData, 'total'));

// Подготовка данных для 3 браузеров
$browsers = array_keys($browserChartData);
$browserDates = json_encode(array_column($chartData, 'date'));
$browserSeries = [];

foreach ($browsers as $browser) {
    $series = [];
    foreach ($chartData as $row) {
        $series[] = $browserChartData[$browser][$row['date']] ?? 0;
    }
    $browserSeries[] = [
        'label' => $browser,
        'data' => $series,
        'fill' => false,
        'borderColor' => '#' . substr(md5($browser), 0, 6),
    ];
}
$browserSeriesJson = json_encode($browserSeries);
?>

<script>
    const ctx1 = document.getElementById('requestChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= $dates ?>,
            datasets: [{
                label: 'Число запросов',
                data: <?= $totals ?>,
                borderColor: 'blue',
                fill: false
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ось X – дата'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Ось Y - число запросов'
                    }
                }
            }
        }
    });

    const ctx2 = document.getElementById('browserChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?= $browserDates ?>,
            datasets: <?= $browserSeriesJson ?>
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ось X – дата'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Ось Y - % число запросов'
                    },
                    min: 0,
                    max: 100
                }
            }
        }
    });
</script>