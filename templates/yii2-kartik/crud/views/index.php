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
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>
<?php if ($enableField != '') { echo 'use ' . $generator->modelClass . ';' . PHP_EOL; echo 'use kartik\switchinput\SwitchInput;' . PHP_EOL; } ?>

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $params array */

$this->title = <?= "'" . \app\models\Util::getTableComment($generator->tableSchema->name) . "管理'" ?>;
$this->params['breadcrumbs'][] = $this->title;

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
<?= $generator->enablePjax ? '<?php Pjax::begin(); ?>' : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
<?= "<?= " ?>GridView::widget([
    'dataProvider' => $dataProvider,
    'responsive' => true,
    'responsiveWrap' => false,
    'panel' => [
        'heading' => '<h3 class="panel-title">' . $this->title . '</h3>',
        'type' => 'default',
        'after' => false,
        'before' =>  Html::a('新建', ['create'], ['class' => 'btn btn-success pull-left']) ,
    ],
    'toolbar' => [
        [
            'content' => $this->render("_search", ['model' => $searchModel, 'params' => $params])
        ],
        '{export}',
        '{toggleData}',
    ],
    <?= !empty($generator->searchModelClass) ? "'columns' => [\n" : "'columns' => [\n"; ?>
        ['class' => 'kartik\grid\SerialColumn', 'header' => '序号',],
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) { ?>
        [
            'header' => 'todo',
            'attribute' => '<?= $name ?>',
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
        ],
<?php }
 } else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (in_array($column->name, [$enableField, $imgFiled])) {
            continue;
        } ?>
        [
            'header' => '<?= !empty($column->comment) ? $column->comment : $column->name ?>',
            'attribute' => '<?= $column->name . ($format === 'text' ? "" : ":" . $format) ?>',
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
        ],
<?php    }
}
?>
<?php if ($imgFiled != '') { ?>
        [
            'label' => '图片',
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
            'format' => 'raw',
            'value'=> function($model) {
                return Html::img($model-><?= $imgFiled ?>, ['width' => '100px']);
            },
        ],
<?php } ?>
<?php if ($enableField != '') { ?>
        [
            'label' => '启用',
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
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
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function($url, $model){
                    return Html::a('编辑', ['update', 'id' => $model->BOOK_SETTING_ID]);
                },
                'delete' => function($url, $model){
                    return Html::a('删除', ['delete', 'id' => $model->id], [
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