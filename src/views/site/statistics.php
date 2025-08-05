<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = 'Статистика логов';
?>
<h1><?= $this->title ?></h1>

<p>
    <?= Html::a('К логам веб-сервера', ['site/index'], ['class' => 'btn btn-primary']) ?>
</p>

<div class="log-filter mb-4">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['site/statistics'],
        'options' => ['class' => 'form-inline mb-3'],
    ]); ?>

    <?= Html::label('Дата с', 'date_from', ['class' => 'mr-2']) ?>
    <?= Html::input('date', 'date_from', $dateFrom ?? '', ['class' => 'form-control mr-3']) ?>

    <?= Html::label('Дата по', 'date_to', ['class' => 'mr-2']) ?>
    <?= Html::input('date', 'date_to', $dateTo ?? '', ['class' => 'form-control mr-3']) ?>

    <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-primary']) ?>

    <?= Html::a('Сбросить', ['site/statistics'], ['class' => 'btn btn-secondary ml-2']) ?>

    <?php ActiveForm::end(); ?>
</div>

<canvas id="requestChart" width="800" height="400"></canvas>
<br>
<canvas id="browserChart" width="800" height="400"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dates = <?= $dates ?>;
    const totals = <?= $totals ?>;
    const percentData = <?= $percentData ?>;
    const browsers = <?= $browsers ?>;

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
            scales: {
                x: { title: { display: true, text: 'Дата' } },
                y: { title: { display: true, text: '% от числа запросов' } }
            }
        }
    });

    const browserDatasets = browsers.map(browser => ({
        label: browser,
        data: percentData[browser],
        fill: false,
        borderWidth: 2
    }));

    new Chart(document.getElementById('browserChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: browserDatasets
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
                    title: {
                        display: true,
                        text: '% от числа запросов'
                    },
                    max: 100,
                    min: 0
                }
            }
        }
    });
</script>