<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = 'Логи веб-сервера';
?>

<div class="log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('К статистике логов', ['site/statistics'], ['class' => 'btn btn-primary']) ?>
    </p>

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
                //'contentOptions' => ['style' => 'max-width: 300px; max-height: 200px; word-wrap: break-word;'],
                'value' => function ($model) {
                $parsed = parse_url($model->url);
                return Html::encode($parsed['path'] ?? $model->url);
            }
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
        'pager' => [
            'class' => \yii\widgets\LinkPager::class,
            'maxButtonCount' => 7,
            'prevPageLabel' => '‹',
            'nextPageLabel' => '›',
            'firstPageLabel' => '«',
            'lastPageLabel' => '»',
            'options' => ['class' => 'pagination justify-content-center'],
            'linkOptions' => ['class' => 'page-link'],
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'firstPageCssClass' => 'page-item',
            'lastPageCssClass' => 'page-item',
            'pageCssClass' => 'page-item',
        ],
    ]); ?>
</div>