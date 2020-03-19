<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $tableComment string table comment */
/* @var $fieldComments array table field comments */
/* @var $fieldPHPFunc array table field php func */
/* @var $thirdParty boolean whether is third party table */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

<?php if ($thirdParty) {echo 'use common\consts\Error;' . PHP_EOL;} ?>
use Yii;
<?php if ($thirdParty) {echo 'use yii\base\Exception;' . PHP_EOL;} ?>
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . (isset($fieldComments[$name]) ? ("'" . $fieldComments[$name] . "'") : $generator->generateString($label)) . ",\n" ?>
<?php endforeach; ?>
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
<?php foreach ($labels as $name => $label){if (strpos($name, 'create') !== false){ ?>
                'createdAtAttribute' => '<?php echo $name; ?>',
<?php }} ?>
<?php foreach ($labels as $name => $label){if (strpos($name, 'update') !== false){ ?>
                'updatedAtAttribute' => '<?php echo $name; ?>',
<?php }} ?>
                'value' => function ($event) {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\db\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
<?php if ($thirdParty){ ?>

    public function addOne($record, $riskQuestId)
    {
<?php $tableStart = strlen($generator->generateTableName($tableName)) + 1; ?>
<?php $tableFieldPrefix = $generator->generateTableName($tableName); ?>
<?php foreach ($labels as $name => $label): ?>
<?php if (($name == $tableFieldPrefix . '_id') || (strpos($name, 'create') !== false) || (strpos($name, 'update') !== false)){ continue;} ?>
<?php if ($tableFieldPrefix . '_risk_request_id' == $name){ ?>
        <?php echo '$this->' . $generator->generateTableName($tableName) .  '_risk_request_id = $riskQuestId;' . PHP_EOL; ?>
<?php echo PHP_EOL; }else{ ?>
<?php $filedPostFix = substr($name, $tableStart); ?>
        <?php echo '$this->' . $name . ' = ' . ($fieldPHPFunc[$name] != '' ? $fieldPHPFunc[$name] . '(' : '')
                . 'strval($this->getValueFromRecord($record, \'' . $name . '\', \'' . $filedPostFix . '\')' . ($fieldPHPFunc[$name] != '' ? ')' : '') . ');' . PHP_EOL; ?>
<?php } ?>
<?php endforeach; ?>
        if ($this->save() === false) {
            $item = current($this->getErrors());
            $msg = is_array($item) ? $item[0] : '<?php echo $tableComment; ?>信息保存失败';
            throw new Exception($msg, Error::ERROR_SAVE);
        }
    }

    private function getValueFromRecord($record, $tableKey, $recordKey, $defaultValue = '')
    {
        if (isset($record[$tableKey])) {
            return $record[$tableKey];
        } else {
            if (isset($record[$recordKey])) {
                return $record[$recordKey];
            } else {
                return $defaultValue;
            }
        }
    }
<?php } ?>
}
