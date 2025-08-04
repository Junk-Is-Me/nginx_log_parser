<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = 'Логи веб-сервера';
?>

<div class="log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="log-filter">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['site/index'],
            'options' => ['class' => 'form-inline mb-3'],
        ]); ?>

        <?= Html::label('Дата с', 'date_from', ['class' => 'mr-2']) ?>
        <?= Html::input('date', 'date_from', $dateFrom, ['class' => 'form-control mr-3']) ?>

        <?= Html::label('Дата по', 'date_to', ['class' => 'mr-2']) ?>
        <?= Html::input('date', 'date_to', $dateTo, ['class' => 'form-control mr-3']) ?>

        <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-primary']) ?>

        <?php ActiveForm::end(); ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'ip',
                'label' => 'IP адрес',
            ],
            [
                'attribute' => 'requested_at',
                'format' => ['datetime', 'php:d.m.Y H:i:s'],
                'label' => 'Дата и время',
            ],
            [
                'attribute' => 'url',
                'format' => 'ntext',
                'label' => 'URL',
            ],
            [
                'attribute' => 'os',
                'label' => 'ОC',
            ],
            [
                'attribute' => 'architecture',
                'label' => 'Архитектура',
            ],
            [
                'attribute' => 'browser',
                'label' => 'Браузер',
            ],
        ],
    ]); ?>

</div>