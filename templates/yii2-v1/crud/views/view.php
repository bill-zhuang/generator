<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$enableField = '';
$imgFiled = '';
foreach ($generator->getTableSchema()->columns as $column) {
    if (($enableField == '') && strpos($column->name, 'enable') !== false) {
        $enableField = $column->name;
    }
    if (($imgFiled == '') && (
            (strpos($column->name, 'img') !== false)
            || (strpos($column->name, 'image') !== false)
            || (strpos($column->name, 'pic') !== false)
            || (strpos($column->name, 'picture') !== false)
        )
    ) {
        $imgFiled = $column->name;
    }
}
$modelNames = explode('\\', $generator->modelClass);
$modelName = $modelNames[count($modelNames) - 1];
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
<?php if ($enableField != '') { echo 'use ' . $generator->modelClass . ';' . PHP_EOL; } ?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= "'" . \app\models\Util::getTableComment($generator->tableSchema->name) . "管理'" ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">
    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('修改') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('删除') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('您确定要删除嘛?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <section class="scrollable padder">
        <div class="row bg-light m-b">
            <div class="col-md-12">
                <section class="panel panel-default">
                    <header class="panel-heading font-bold">详细</header>
                    <div class="panel-body">
                    <?= "<?= " ?>DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                <?php
                if (($tableSchema = $generator->getTableSchema()) === false) {
                    foreach ($generator->getColumnNames() as $name) {
                        echo "                            '" . $name . "',\n";
                    }
                } else {
                    foreach ($generator->getTableSchema()->columns as $column) {
                        if ($imgFiled == $column->name) { ?>
                            [
                                'label' => '图片',
                                'format' => 'raw',
                                'value'=> function($model) {
                                    return Html::img($model-><?= $imgFiled ?>, ['width' => '100px']);
                                },
                            ],
                        <?php } elseif ($enableField == $column->name) { ?>
                            [
                                'label' => '启用状态',
                                'value'=> function($model) {
                                    return $model-><?= $enableField ?> == <?= $modelName ?>::ENABLE_YES ? '启用' : '未启用';
                                },
                            ],
                        <?php } else {
                            $format = $generator->generateColumnFormat($column);
                            echo str_repeat(' ', 4 * 7) . "'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
                        }
                    }
                }
                ?>
                        ],
                    ]) ?>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>
