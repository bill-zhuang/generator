<?php

namespace app\commands;

use app\models\Render;
use app\models\Util;
use \yii\console\Controller;
use \yii\gii\generators\model\Generator as ModelGenerator;
use Yii;

class CurrentController extends Controller
{
    public static $tables = [
    ];
    public static $namespace = 'app\models';
    public static $generateSearchModel = false; //是否生成SearchModel，默认为false
    public static $projectName = 'generator';

    public function actionM()
    {
        $test = (new ModelGenerator());
        foreach (self::$tables as $tableName) {
            $modelName = implode('', array_map('ucfirst', explode('_', $tableName)));
            $test->tableName = $tableName;
            $test->ns = self::$namespace;
            $test->db = 'testDB';

            $relations = Util::callProtectMethod('\yii\gii\generators\model\Generator', 'generateRelations');
            $db = Yii::$app->testDB;
            // model :
            $modelClassName = Util::callProtectMethod('\yii\gii\generators\model\Generator', 'generateClassName');
            $queryClassName = ($test->generateQuery) ? $test->generateQueryClassName($modelClassName) : false;
            $tableSchema = $db->getTableSchema($tableName);
            //new add by bill, get table comment, field comments
            $createTableSql = $db->createCommand('show create table ' . $tableName)->queryAll()[0]['Create Table'];
            preg_match('/ENGINE[^\']+\'([^\']+)\'/', $createTableSql, $commentMatches);
            if (isset($commentMatches[1])) {
                $tableComment = $commentMatches[1];
            } else {
                $tableComment = '';
            }
            preg_match_all('/`([^`]+)`[^`]*?\'([^\']*)\',/', $createTableSql, $fieldComments);
            $params = [
                'generator' => $test,
                'tableName' => $tableName,
                'className' => $modelName,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'properties' => Util::callProtectMethod('\yii\gii\generators\model\Generator', 'generateProperties', $tableSchema),
                'labels' => $test->generateLabels($tableSchema),
                'rules' => $test->generateRules($tableSchema),
                'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
                'tableComment' => $tableComment,
                'fieldComments' => array_combine($fieldComments[1], $fieldComments[2]),
                'fieldPHPFunc' => Util::generateFieldPHPFunc($tableSchema),
                'thirdParty' => false,
            ];
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/default/model/model.php', $params);
            file_put_contents(Util::getProjectPath(self::$projectName) . "/models/$modelName.php", $codeContent);
        }
    }
}
 