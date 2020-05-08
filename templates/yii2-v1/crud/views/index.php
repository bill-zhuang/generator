<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= "'" . \app\models\Util::getTableComment($generator->tableSchema->name) . "管理'" ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<p>
    <?= "<?= " ?>Html::a(<?= '\'创建' . \app\models\Util::getTableComment($generator->tableSchema->name) . '\'' ?>, ['create'], ['class' => 'btn btn-success']) ?>
</p>
<?php if (!empty($generator->searchModelClass)) { ?>
<?= "<?= " . ($generator->indexWidgetType === 'grid' ? "" : "") ?>$this->render('_search', ['model' => $searchModel]); ?>
<?php } ?>
<section class="scrollable padder">
    <div class="row bg-light m-b">
        <div class="col-md-12">
            <section class="panel panel-default">
                <header class="panel-heading font-bold"><?php echo \app\models\Util::getTableComment($generator->tableSchema->name); ?>列表</header>
                <div class="panel-body">
                    <div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
                        <?= "<?= " ?>GridView::widget([
                            'dataProvider' => $dataProvider,
                            <?= !empty($generator->searchModelClass) ? "'columns' => [\n" : "'columns' => [\n"; ?>
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
            echo str_pad(' ', 4 * 8) . "'" . $name . "',\n";
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
            echo str_pad(' ', 4 * 8) . "'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => '操作',
                                    'template' => '{view} {update} {delete}',
                                    'buttons' => [
                                        'delete' => function($url, $model){
                                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->id], [
                                                'data' => [
                                                    'confirm' => '确认删除?',
                                                    'method' => 'post',
                                                ],
                                            ]);
                                        }
                                    ]
                                ],
                            ],
                            'layout' => '{items}{pager}',
                            'summary' => '', //Total xxxx items.
                            'pager' => [
                                'options'=>['class'=>'pagination'],
                                'prevPageLabel' => '上一页',
                                'firstPageLabel'=> '首页',
                                'nextPageLabel' => '下一页',
                                'lastPageLabel' => '末页',
                                'maxButtonCount'=>'10',
                            ]
                        ]); ?>
                    <?php else: ?>
                        <?= "<?= " ?>ListView::widget([
                            'dataProvider' => $dataProvider,
                            'itemOptions' => ['class' => 'item'],
                            'itemView' => function ($model, $key, $index, $widget) {
                                return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
                            },
                        ]) ?>
                    <?php endif; ?>
<?= $generator->enablePjax ? '<?php Pjax::end(); ?>' : '' ?>
</div>
                </div>
            </section>
        </div>
    </div>
</section>