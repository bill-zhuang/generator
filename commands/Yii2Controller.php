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
    public static $baseNamespace = 'common\models';
    public static $crudModuleName = ''; //放在某个module名下
    public static $generateSearchModel = true; //是否生成SearchModel，默认为false
    public static $templateFolderName = 'yii2-kartik'; //yii2-v1 table普通 yii2-kartik table用kartik
    public static $projectName = '';
    public static $generateBaseModel = false; //是否生成基类model
    public static $baseModelFolderPath = '/common/models/'; //基类model文件路径
    /**
     * 生成的继承model配置 格式 namespace => extend model folder path
     * @var array
     */
    public static $extendModelList = [
        'backend\models' => '/backend/models/',
        //'api\models' => '/api/models/',
    ];
    public static $crudFolderName = 'backend';
    public static $crudNamespace = 'backend\models';
    public static $crudFolderAlias = '@backend';

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
            $test->ns = self::$baseNamespace;
            $test->generateLabelsFromComments = true;
            $test->useTablePrefix = true;


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
            $templateBaseModelPath = Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/model/model.php';
            $codeContent = Render::phpFile($templateBaseModelPath, $params);
            if (self::$generateBaseModel) {
                $baseModelDirPath = Util::getProjectPath(self::$projectName) . self::$baseModelFolderPath;
                if (!file_exists($baseModelDirPath)) {
                    Util::createDir($baseModelDirPath);
                }
                $baseModelPath = $baseModelDirPath . "$modelName.php";
                file_put_contents($baseModelPath, $codeContent);
            }
            foreach (self::$extendModelList as $extendNs => $extendModelFolderPath) {
                $test->ns = $extendNs;
                $params = ['generator' => $test, 'className' => $modelName, 'tableSchema' => $tableSchema,];
                $templateExtendModelPath = Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/model/bkapimodel.php';
                $codeContent = Render::phpFile($templateExtendModelPath, $params);
                $extendModelPath = Util::getProjectPath(self::$projectName) . $extendModelFolderPath . "$modelName.php";
                file_put_contents($extendModelPath, $codeContent);
            }
        }
    }

    public function actionCrud()
    {
        Yii::setAlias(self::$crudFolderAlias, Util::getProjectPath(self::$projectName) . '/' . self::$crudFolderName);

        $projectPath = Util::getProjectPath(self::$projectName);
        $backendModulePath = $projectPath . '/' . self::$crudFolderName . '/modules/' . self::$crudModuleName;
        if (empty(self::$crudModuleName)) {
            $backendModulePath = $projectPath . '/' . self::$crudFolderName . '/';
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
                $test->moduleClass = self::$crudFolderName . '\modules\\' . self::$crudModuleName . '\Module';
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
            $baseModelPath = Util::getProjectPath(self::$projectName) . '/' . str_replace('\\', '/', self::$baseNamespace) . '/' . $name . '.php';
            include_once($baseModelPath);
            //继承类需要也include
            $extendModelPath = Util::getProjectPath(self::$projectName) . '/' . str_replace('\\', '/', self::$crudNamespace) . '/' . $name . '.php';
            include_once($extendModelPath);
            $test->modelClass = self::$crudNamespace . '\\' . $name;
            if (self::$generateSearchModel) {
                // $test->searchModelClass = '\common\models\Search\\' . $name . 'Search';
                $test->searchModelClass = '\\'. self::$crudFolderName . '\models\Search\\' . $name . 'Search';
                //generate model search file
                $searchParams = [
                    'generator' => $test,
                ];
                $templateSearchModelPath = Yii::$app->basePath . '/templates/' . self::$templateFolderName . '/crud/search.php';
                $codeContent = Render::phpFile($templateSearchModelPath, $searchParams);
                //file_put_contents(Util::getProjectPath(self::$projectName) . "/common/models/Search/{$name}Search.php", $codeContent);
                $searchModelDirPath = Util::getProjectPath(self::$projectName) . '/' . self::$crudFolderName . "/models/Search/";
                if (!file_exists($searchModelDirPath)) {
                    Util::createDir($searchModelDirPath);
                }
                $searchModelPath = $searchModelDirPath . "{$name}Search.php";
                file_put_contents($searchModelPath, $codeContent);
            }
            $test->controllerClass = self::$crudFolderName . '\modules\\' . self::$crudModuleName . '\controllers\\' . $name . 'Controller';
            $test->viewPath = self::$crudFolderAlias . '/modules/' . self::$crudModuleName . '/views/' . $viewName;
            if (empty(self::$crudModuleName)) {
                $test->controllerClass = self::$crudFolderName . '\controllers\\' . $name . 'Controller';
                $test->viewPath = self::$crudFolderAlias . '/views/' . $viewName;
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
 