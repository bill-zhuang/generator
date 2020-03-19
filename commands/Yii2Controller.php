<?php

namespace app\commands;

use app\models\Render;
use app\models\Util;
use \yii\console\Controller;
use \yii\gii\CodeFile;
use \yii\gii\generators\crud\Generator as CrudGenerator;
use \yii\gii\generators\model\Generator as ModelGenerator;
use \yii\gii\generators\module\Generator as ModuleGenerator;
use Yii;

class Yii2Controller extends Controller
{
    public static $tables = [
    ];
    public static $namespace = 'common\models';
    public static $crudModuleName = 'test'; //放在某个module名下
    public static $generateSearchModel = false; //是否生成SearchModel，默认为false
    public static $projectName = 'crms';

    public function actionM()
    {
        $test = (new ModelGenerator());
        foreach (self::$tables as $tableName) {
            $modelName = implode('', array_map('ucfirst', explode('_', $tableName)));
            $test->tableName = $tableName;
            $test->ns = self::$namespace;

            $relations = Util::callProtectMethod('\yii\gii\generators\model\Generator', 'generateRelations');
            $db = Util::getDB();
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
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/yii2/model/model.php', $params);
            file_put_contents(Util::getProjectPath(self::$projectName) . "/common/models/$modelName.php", $codeContent);
        }
    }

    public function actionCrud()
    {
        if (empty(self::$crudModuleName)) {
            echo 'module名不能为空';
            exit;
        }

        Yii::setAlias('@backend', Util::getProjectPath(self::$projectName) . '/backend');

        $projectPath = Util::getProjectPath(self::$projectName);
        $backendModulePath = $projectPath . '/backend/modules/' . self::$crudModuleName;
        if (!file_exists($backendModulePath)) {
            //生成默认module下所需文件夹
            $moduleFolders = [
                $backendModulePath,
                $backendModulePath . '/controllers',
                $backendModulePath . '/views',
                $backendModulePath . '/views/default',
            ];
            foreach ($moduleFolders as $moduleRequireFolder) {
                Util::createDir($moduleRequireFolder);
            }
            //

            $test = (new ModuleGenerator());
            $test->moduleClass = 'backend\modules\\' . self::$crudModuleName . '\Module';
            $test->moduleID = self::$crudModuleName;
            $moduleContent = $test->generate();
            foreach ($moduleContent as $fileCode) {
                if ($fileCode instanceof CodeFile) {
                    file_put_contents($fileCode->path, $fileCode->content);
                }
            }
        }
        foreach (self::$tables as $tableName) {
            $name = implode('', array_map('ucfirst', explode('_', $tableName)));
            //生成view下面的文件夹
            $viewName = str_replace('_', '-', $tableName);
            Util::createDir($backendModulePath . '/views/' . $viewName);
            //
            $test = (new CrudGenerator());
            $test->templates = [
                'default' => realpath(__DIR__) . '/../templates/yii2/crud',
            ];
            //model路径 eg. \common\models\OutsideConfig
            $modelClassPath = Util::getProjectPath(self::$projectName) . '/' . str_replace('\\', '/', self::$namespace) . '/' . $name . '.php';
            include($modelClassPath);
            $test->modelClass = self::$namespace . '\\' . $name;
            if (self::$generateSearchModel) {
                $test->searchModelClass = '\common\models\Search\\' . $name . 'Search';
            }
            $test->controllerClass = 'backend\modules\\' . self::$crudModuleName . '\controllers\\' . $name . 'Controller';
            $test->viewPath = '@backend/modules/' . self::$crudModuleName . '/views/' . $viewName;
            $fileContent = $test->generate();
            foreach ($fileContent as $fileCode) {
                if ($fileCode instanceof CodeFile) {
                    file_put_contents($fileCode->path, $fileCode->content);
                }
            }
        }
    }
}
 