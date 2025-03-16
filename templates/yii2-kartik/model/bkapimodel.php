<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $className string class name */
/* @var $tableSchema yii\db\TableSchema */

echo "<?php\n";
$extendClassName = '\\'.  \app\commands\Yii2Controller::$baseNamespace . '\\' . $className;
?>

namespace <?= $generator->ns ?>;

class <?= $className ?> extends <?= $extendClassName . PHP_EOL ?>
{
<?php
$enableFlag = false;
$stateFlag = false;
foreach ($tableSchema->columns as $column) {
    if (strpos($column->name, 'enable') !== false) {
        $enableFlag = true;
        //break;
    }
    if (strpos($column->name, 'state') !== false || strpos($column->name, 'status') !== false) {
        $stateFlag = true;
        //break;
    }
}
if ($stateFlag) { ?>
    const STATUS_VALID = 1;
    const STATUS_INVALID = 2;

<?php } ?>
<?php if ($enableFlag) {?>
    const ENABLE_YES = 1;
    const ENABLE_NO = 2;

    public static $enableList = [
        self::ENABLE_YES => '启用',
        self::ENABLE_NO => '不启用',
    ];
<?php } ?>
}
