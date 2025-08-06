<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = 'Статистика логов';
?>
<h1><?= Html::encode($this->title) ?></h1>

<!-- Фильтр по датам -->
<div class="log-filter mb-4">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['site/statistics'],
        'options' => ['class' => 'form-inline mb-3'],
    ]); ?>

    <?= Html::label('Дата от', 'date_from', ['class' => 'mr-2']) ?>
    <?= Html::input('date', 'date_from', $requestData['date_from'] ?? '', ['class' => 'form-control mr-3']) ?>

    <?= Html::label('Дата до', 'date_to', ['class' => 'mr-2']) ?>
    <?= Html::input('date', 'date_to', $requestData['date_to'] ?? '', ['class' => 'form-control mr-3']) ?>

    <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Сбросить', ['site/statistics'], ['class' => 'btn btn-secondary ml-2']) ?>

    <?php ActiveForm::end(); ?>
</div>

<!-- Графики -->
<canvas id="requestChart" width="800" height="400" class="mb-5"></canvas>
<canvas id="browserChart" width="800" height="400" class="mb-5"></canvas>

<!-- Таблица -->
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'date',
            'format' => ['date', 'php:Y-m-d'],
            'label' => 'Дата',
        ],
        [
            'attribute' => 'total_requests',
            'label' => 'Число запросов',
        ],
        [
            'attribute' => 'top_url',
            'label' => 'Популярный URL',
        ],
        [
            'attribute' => 'top_browser',
            'label' => 'Популярный браузер',
        ],
    ],
]); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Данные из PHP в JS
    const dates = <?= json_encode(array_column($chartTotalRequests, 'date')) ?>;
    const totals = <?= json_encode(array_column($chartTotalRequests, 'total')) ?>;

    const browserData = <?= json_encode($chartBrowserShares) ?>;
    const browserNames = Object.keys(browserData);
    const browserSeries = browserNames.map(name => ({
        label: name,
        data: browserData[name],
        fill: false,
        borderWidth: 2
    }));

    // График: Число запросов по датам
    new Chart(document.getElementById('requestChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Число запросов',
                data: totals,
                borderColor: 'blue',
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Число запросов по датам'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Дата'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Запросов'
                    }
                }
            }
        }
    });

    // График: Доля браузеров
    new Chart(document.getElementById('browserChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: browserSeries
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Доля браузеров по дате (%)'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Дата'
                    }
                },
                y: {
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: '% от общего числа'
                    }
                }
            }
        }
    });
</script>