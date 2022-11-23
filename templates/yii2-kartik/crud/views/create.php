<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = <?= $generator->generateString('创建' . \app\models\Util::getTableComment($generator->tableSchema->name, $generator->modelClass)) ?>;
$this->params['breadcrumbs'][] = ['label' => <?= ("'" . \app\models\Util::getTableComment($generator->tableSchema->name, $generator->modelClass) . '管理' . "'") ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<section class="scrollable padder">
    <div class="row bg-light m-b">
        <div class="col-md-12">
            <section class="panel panel-default">
                <header class="panel-heading font-bold"><?= "<?= " ?>Html::encode($this->title) ?></header>
                <div class="panel-body">
                    <?= "<?= " ?>$this->render('_form', [
                        'model' => $model,
                    ]) ?>
                </div>
            </section>
        </div>
    </div>
</section>
