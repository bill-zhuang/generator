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
         '',
    ];
    public static $namespace = 'common\models';
    public static $crudModuleName = ''; //放在某个module名下
    public static $generateSearchModel = true; //是否生成SearchModel，默认为false
    public static $templateFolderName = 'yii2-kartik';
    public static $projectName = '';

    public function actionM()
    {
        $test = (new ModelGenerator());
        foreach (self::$tables as $tableName) {
            $processName = $tableName;
            $setTablePrefix = Yii::$app->db->tablePrefix;
            if ($setTablePrefix != '') {
                $processName = preg_replace("/^{$setTablePrefix}/", '', $processName);
            }
            $modelName = implode('', array_map('ucfirst', explode('_', $processName)));
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
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/model/model.php', $params);
            //file_put_contents(Util::getProjectPath(self::$projectName) . "/common/models/$modelName.php", $codeContent);
            if (self::$templateFolderName == 'yii2-v1' || self::$templateFolderName == 'yii2-kartik') {
                //backend
                $test->ns = 'backend\models';
                $params = ['generator' => $test, 'className' => $modelName, 'tableSchema' => $tableSchema,];
                $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/model/bkapimodel.php', $params);
                file_put_contents(Util::getProjectPath(self::$projectName) . "/backend/models/$modelName.php", $codeContent);
                //api
                $test->ns = 'api\models';
                $params = ['generator' => $test, 'className' => $modelName, 'tableSchema' => $tableSchema,];
                $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/model/bkapimodel.php', $params);
                file_put_contents(Util::getProjectPath(self::$projectName) . "/api/models/$modelName.php", $codeContent);
            }
        }
    }

    public function actionCrud()
    {
        Yii::setAlias('@backend', Util::getProjectPath(self::$projectName) . '/backend');

        $projectPath = Util::getProjectPath(self::$projectName);
        $backendModulePath = $projectPath . '/backend/modules/' . self::$crudModuleName;
        if (empty(self::$crudModuleName)) {
            $backendModulePath = $projectPath . '/backend/';
        }
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
            if (!empty(self::$crudModuleName)) {
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
        }
        foreach (self::$tables as $tableName) {
            $processName = $tableName;
            $setTablePrefix = Yii::$app->db->tablePrefix;
            if ($setTablePrefix != '') {
                $processName = preg_replace("/^{$setTablePrefix}/", '', $processName);
            }
            $name = implode('', array_map('ucfirst', explode('_', $processName)));
            //生成view下面的文件夹
            $viewName = str_replace('_', '-', $processName);
            Util::createDir($backendModulePath . '/views/' . $viewName);
            //
            $test = (new CrudGenerator());
            $test->templates = [
                'default' => realpath(__DIR__) . '/../templates/' . self::$templateFolderName . '/crud',
            ];
            //model路径 eg. \common\models\User
            if (self::$templateFolderName == 'yii2-v1' || self::$templateFolderName == 'yii2-kartik') {
                //继承类需要也include
                $modelClassPath = Util::getProjectPath(self::$projectName) . '/' . str_replace('\\', '/', self::$namespace) . '/' . $name . '.php';
                include($modelClassPath);
                self::$namespace = 'backend\models';
            }
            $modelClassPath = Util::getProjectPath(self::$projectName) . '/' . str_replace('\\', '/', self::$namespace) . '/' . $name . '.php';
            include($modelClassPath);
            $test->modelClass = self::$namespace . '\\' . $name;
            if (self::$generateSearchModel) {
                $test->searchModelClass = '\common\models\Search\\' . $name . 'Search';
                if (self::$templateFolderName == 'yii2-v1' || self::$templateFolderName == 'yii2-kartik') {
                    $test->searchModelClass = '\backend\models\Search\\' . $name . 'Search';
                }
                //generate model search file
                $searchParams = [
                    'generator' => $test,
                ];
                $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/crud/search.php', $searchParams);
                if (self::$templateFolderName == 'yii2-v1' || self::$templateFolderName == 'yii2-kartik') {
                    file_put_contents(Util::getProjectPath(self::$projectName) . "/backend/models/Search/{$name}Search.php", $codeContent);
                } else {
                    file_put_contents(Util::getProjectPath(self::$projectName) . "/common/models/Search/{$name}Search.php", $codeContent);
                }
            }
            $test->controllerClass = 'backend\modules\\' . self::$crudModuleName . '\controllers\\' . $name . 'Controller';
            $test->viewPath = '@backend/modules/' . self::$crudModuleName . '/views/' . $viewName;
            if (empty(self::$crudModuleName)) {
                $test->controllerClass = 'backend\controllers\\' . $name . 'Controller';
                $test->viewPath = '@backend/views/' . $viewName;
            }
            $fileContent = $test->generate();
            foreach ($fileContent as $fileCode) {
                if ($fileCode instanceof CodeFile) {
                    //model search path error, ignore, generate of above
                    if (strpos($fileCode->path, "{$name}Search") !== false) {
                        continue;
                    }
                    file_put_contents($fileCode->path, $fileCode->content);
                }
            }
        }
    }
}
 