<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $className string class name */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

class <?= $className ?> extends <?= '\common\models\\' . $className . PHP_EOL ?>
{
    const STATUS_VALID = 1;
    const STATUS_INVALID = 2;
}
