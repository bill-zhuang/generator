<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}
$useModel = false;
if (strpos(implode('|', $safeAttributes), 'enable') !== false) {
    $useModel = true;
}
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
use yii\widgets\ActiveForm;
<?php if ($enableField != '') { echo 'use ' . $generator->modelClass . ';' . PHP_EOL; } ?>
<?php if ($imgFiled != '') { echo 'use kartik\file\FileInput;' . PHP_EOL; } ?>

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal'
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-sm-4\">{input}</div><div class=\"help-block\">{error}</div>",
            'labelOptions' => [
                'class' => 'col-sm-2 control-label'
            ]
        ]
    ]); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {
        $ignoreFields = ['create_at', 'created_at', 'create_time', 'update_at', 'updated_at', 'update_time', 'status'];
        if (strpos(implode('|', $ignoreFields), $attribute) !== false) {
            continue;
        } elseif ($enableField == $attribute) {
            echo "    <?= \$form->field(\$model, '{$attribute}')->radioList({$modelName}::\${$attribute}List, ['style' => 'margin-top:7px;'])->label('是否启用') ?>\n\n";
        } elseif ($imgFiled == $attribute) { ?>
    <div class="form-group field-<?= $generator->controllerID; ?>-<?= $imgFiled; ?>">
        <label class="col-sm-2 control-label" for="reward-imgurl">图片</label>
        <div class="col-sm-4" style="width:550px;">
            <?= '<?=' . PHP_EOL ?>
            FileInput::widget([
                'name' => '<?= $modelName ?>[<?= $imgFiled; ?>]',
                'options' => [
                    'accept' => 'image/*',
                    'multiple' => false
                ],
                'pluginOptions' => [
                    'initialPreview'=>[
                        $model-><?= $imgFiled . PHP_EOL; ?>
                    ],
                    'initialPreviewAsData'=>true,
                    'showUpload' => false,
                    'showRemove' => true,
                ]
            ]);
            <?= '?>' . PHP_EOL ?>
        </div>
    </div>
        <?php } else {
            echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
        }
    }
} ?>
    <div class="col-md-2">
    </div>
    <div class="col-md-4">
        <?= "<?= " ?>Html::checkbox('ckOption', false, ['label' => '查看', 'value' => 'view', 'class' => 'ckMark']) ?>
        <?= "<?= " ?>Html::checkbox('ckOption', false, ['label' => '继续创建', 'value' => 'create', 'class' => 'ckMark']) ?>
        <?= "<?= " ?>Html::checkbox('ckOption', false, ['label' => '继续编辑', 'value' => 'update', 'class' => 'ckMark']) ?>
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('创建') ?> : <?= $generator->generateString('修改') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?= "<?php " ?>ActiveForm::end(); ?>

</div>