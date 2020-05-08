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

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

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
    if (in_array($attribute, $safeAttributes) && (
            strpos($attribute, 'create_at') === false &&
            strpos($attribute, 'created_at') === false &&
            strpos($attribute, 'update_at') === false &&
            strpos($attribute, 'updated_at') === false
        )) {
        echo "    <?= " . $generator->generateActiveField($attribute) . " ?>\n\n";
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
<script>
    <?= '<?php $this->beginBlock(\'additionJs\') ?>' . PHP_EOL ?>
    $(document).ready(function () {
        $(".ckMark").click(function() {
            if ($(this).is(':checked')) {
                $(".ckMark").prop("checked", false);
                $(this).prop("checked", true);
            }
        });
    });
    <?= '<?php $this->endBlock() ?>' . PHP_EOL; ?>
    <?= '<?php $this->registerJs($this->blocks[\'additionJs\']) ?>' . PHP_EOL; ?>
</script>