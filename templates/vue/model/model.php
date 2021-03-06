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
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $tableComment string table comment */
/* @var $fieldComments array table field comments */
/* @var $fieldPHPFunc array table field php func */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
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

    public static function getList(array $params)
    {
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $page = isset($params['current_page']) ? $params['current_page'] : 1;
        $start = ($page - 1) * $limit;

        $query = self::find();

        $list = $query
            ->offset($start)
            ->limit($limit)
            ->asArray()
            ->all();

        $count = (int)$query->count();

        return [$list, $count];
    }
}
