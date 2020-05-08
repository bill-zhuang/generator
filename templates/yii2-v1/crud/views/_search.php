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
?>

<div class="panel panel-default">
    <header class="panel-heading">
        搜索
    </header>
    <div class="panel-body">
        <?= "<?php " ?>$form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            "options" => ['class' => 'form-inline'],
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
            <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('搜索') ?>, ['class' => 'btn btn-primary']) ?>
        </div>

        <?= "<?php " ?>ActiveForm::end(); ?>
    </div>
</div>
