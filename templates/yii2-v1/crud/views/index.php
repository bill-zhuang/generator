<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

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
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>
<?php if ($enableField != '') { echo 'use ' . $generator->modelClass . ';' . PHP_EOL; echo 'use kartik\switchinput\SwitchInput;' . PHP_EOL; } ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = <?= "'" . \app\models\Util::getTableComment($generator->tableSchema->name) . "管理'" ?>;
$this->params['breadcrumbs'][] = $this->title;

$pre = $dataProvider->pagination->getPageCount();
$count = $dataProvider->getCount();
$totalCount = $dataProvider->getTotalCount();
$begin = $dataProvider->pagination->getPage() * $dataProvider->pagination->pageSize + 1;
$end = $begin + $count - 1;
?>
<?php if ($enableField != '') {?>
<script>
    function submitAjax(event, state) {
        var splitStr = event.target.name.split('_');
        $.ajax({
            type:'POST',
            url: '/<?php echo  $generator->getControllerID(); ?>/update-' + splitStr[0],
            data: {
                'id': splitStr[1],
                'value': state ? 1 : 0
            },
            datatype: 'json',
            success:function(data){
                if (data.code == 0) {
                    $.notify({message: data.msg},{type: 'success',delay:1000,allow_dismiss: false,});
                } else {
                    $.notify({message: data.msg},{type: 'danger',delay:1000,allow_dismiss: false,});
                    location.reload();
                }
            },
            error: function(){
                $.notify({message: '修改失败'},{type: 'danger',delay:1000,allow_dismiss: false,});
            }
        });
    }
</script>
<?php } ?>
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
                <header class="panel-heading font-bold">
                    <?php echo \app\models\Util::getTableComment($generator->tableSchema->name); ?>列表
                    <div class="pull-right">
                        <div class="summary">
                            第<b><?= '<?= $begin . ' . '\'-\'' . ' . $end ?>' ?></b>条, 共<b><?= '<?= $dataProvider->totalCount ?>' ?></b>条数据.
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
                        <?= "<?= " ?>GridView::widget([
                            'dataProvider' => $dataProvider,
                            <?= !empty($generator->searchModelClass) ? "'columns' => [\n" : "'columns' => [\n"; ?>
                                [
                                    'label' => '序号',
                                    'value' => function ($model, $key, $index) use ($begin) {
                                        return $begin + $index;
                                    }
                                ],
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo str_pad(' ', 4 * 8) . "'" . $name . "',\n";
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (in_array($column->name, [$enableField, $imgFiled])) {
            continue;
        }
        echo str_pad(' ', 4 * 8) . "'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
<?php if ($imgFiled != '') { ?>
                                [
                                    'label' => '图片',
                                    'format' => 'raw',
                                    'value'=> function($model) {
                                        return Html::img($model-><?= $imgFiled ?>, ['width' => '100px']);
                                    },
                                ],
<?php } ?>
<?php if ($enableField != '') { ?>
                                [
                                    'label' => '启用',
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        return SwitchInput::widget([
                                            'id' => 'enable_' . $model->id,
                                            'name' => 'enable_' . $model->id,
                                            'value' => $model-><?= $enableField ?> == <?= $modelName ?>::ENABLE_YES ? true : false,
                                            'pluginOptions'=>[
                                                'size' => 'mini',
                                                //'handleWidth' => 30,
                                                'onText' => '启用',
                                                'offText' => '关闭',
                                                'onColor' => 'success',
                                                'offColor' => 'danger',
                                            ],
                                            'pluginEvents' => [
                                                "switchChange.bootstrapSwitch" => "function(event, state) {submitAjax(event, state);}"
                                            ],
                                        ]);
                                    }
                                ],
<?php } ?>
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