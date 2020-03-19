<?php

namespace app\commands;

use app\models\Render;
use app\models\Util;
use yii\console\Controller;
use \yii\gii\generators\model\Generator as ModelGenerator;
use Yii;

class VueController extends Controller
{
    public $tables = [
    ];
    public $moduleName = 'admin';
    public $backendProjectName = 'model';
    public $frontendProjectName = 'model-vue';

    public function actionM()
    {
        $test = (new ModelGenerator());
        foreach ($this->tables as $tableName) {
            $modelName = implode('', array_map('ucfirst', explode('_', $tableName)));
            $test->tableName = $tableName;
            $test->ns = 'common\models';

            $relations = Util::callProtectMethod('\yii\gii\generators\model\Generator', 'generateRelations');
            $db = Yii::$app->db;
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
            ];
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/vue/model/model.php', $params);
            file_put_contents(Util::getProjectPath($this->backendProjectName) . "/common/models/$modelName.php", $codeContent);
        }
    }

    public function actionC()
    {
        if (empty($this->moduleName)) {
            echo 'module名不能为空';
            exit;
        }
        $moduleFolderPath = Util::getProjectPath($this->backendProjectName) . '/api/modules/' . $this->moduleName;
        if (!file_exists($moduleFolderPath)) {
            Util::createDir($moduleFolderPath);
            Util::createDir($moduleFolderPath . '/' . 'controllers');
            //module类
            $params = [
                'moduleName' => $this->moduleName,
            ];
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/vue/module/module.php', $params);
            file_put_contents(Util::getProjectPath($this->backendProjectName) . "/api/modules/{$this->moduleName}/Module.php", $codeContent);
        }
        $db = Util::getDB();
        foreach ($this->tables as $tableName) {
            $name = implode('', array_map('ucfirst', explode('_', $tableName)));
            $pkID = '';
            $tableSchema = $db->getTableSchema($tableName);
            foreach ($tableSchema->columns as $columnName => $columnObj) {
                if ($columnObj->isPrimaryKey) {
                    $pkID = $columnName;
                    break;
                }
            }
            //
            $params = [
                'moduleName' => $this->moduleName,
                'controllerClass' => $name,
                'modelName' => $name,
                'modelClass' => 'common\models\\' . $name,
                'pkID' => $pkID,
            ];

            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/vue/crud/controller.php', $params);
            file_put_contents(Util::getProjectPath($this->backendProjectName) . "/api/modules/{$this->moduleName}/controllers/{$name}Controller.php", $codeContent);
        }
    }

    public function actionV()
    {
        if (empty($this->frontendProjectName)) {
            echo 'vue module名不能为空';
            exit;
        }
        $db = Util::getDB();
        $ignorePostfixs = ['_id', '_status', '_state', '_created_at', '_create_at', '_updated_at', '_update_at'];
        foreach ($this->tables as $tableName) {
            $tableSchema = $db->getTableSchema($tableName);
            $createTableSql = $db->createCommand('show create table ' . $tableName)->queryAll()[0]['Create Table'];
            preg_match_all('/`([^`]+)`[^`]*?\'([^\']*)\',/', $createTableSql, $fieldComments);
            $comments = array_combine($fieldComments[1], $fieldComments[2]);
            $availableFields = [];
            foreach ($tableSchema->columns as $columnName => $columnObj) {
                if (in_array(substr($columnName, strlen($tableName)), $ignorePostfixs)) {
                    continue;
                }
                $availableFields[] = $columnName;
            }

            $params = [
                'module' => $this->moduleName,
                'tableName' => $tableName,
                'tableFields' => $availableFields,
                'tableComments' => $comments
            ];
            $viewFolderName = str_replace('_', '-', $tableName);
            Util::createDir(Util::getProjectPath($this->frontendProjectName) . "/src/views/{$viewFolderName}");
            $codeContent = Render::phpFile(Yii::$app->basePath . '/templates/vue/crud/views/index.php', $params);
            file_put_contents(Util::getProjectPath($this->frontendProjectName) . "/src/views/{$viewFolderName}/index.vue", $codeContent);
        }
    }
}
 