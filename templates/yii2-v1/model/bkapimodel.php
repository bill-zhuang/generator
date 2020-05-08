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

<?php
$safeAttributes = $generator->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $generator->attributes();
}
$enableFlag = false;
if (strpos(implode('|', $safeAttributes), 'enable') !== false) {
    $enableFlag = true;
}
if ($enableFlag) {?>
    const ENABLE_YES = 1;
    const ENABLE_NO = 2;

    public static $enableList = [
        self::ENABLE_YES => '启用',
        self::ENABLE_NO => '不启用',
    ];
<?php } ?>
}
