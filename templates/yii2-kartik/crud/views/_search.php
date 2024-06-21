<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->searchModelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
/* @var $params array */
?>

<?= "<?php " ?>$form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    "options" => ['class' => 'form-inline', 'autocomplete' => 'off'],
    'fieldConfig' => [
        'template' => "{input}",
        'labelOptions' => [
            'class' => 'control-label'
        ]
    ]
]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $keyIndex => $attribute) {
if (!($keyIndex > 0 &&
    strpos($attribute, 'create_at') === false &&
    strpos($attribute, 'created_at') === false &&
    strpos($attribute, 'update_at') === false &&
    strpos($attribute, 'updated_at') === false)) {
    continue;
}
if (++$count < 6) {
    echo "    <?= " . $generator->generateActiveSearchField($attribute) . "->textInput(['placeholder'=> '名称']) ?>\n\n";
} else {
    echo "    <?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
}
}
?>
<div class="form-group">
    <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('查询') ?>, ['class' => 'btn btn-primary']) ?>
</div>
<div class="form-group">
    <?= "<?= " ?>Html::a('重置', ['index'], ['data-pjax'=>0, 'class'=>'btn btn-default form-group'])?>
</div>

<?= "<?php " ?>ActiveForm::end(); ?>
