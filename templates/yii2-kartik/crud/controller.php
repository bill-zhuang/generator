<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

$enableField = '';
$imgFiled = '';
foreach ($generator->getTableSchema()->columns as $column) {
    if (($enableField == '') && (strpos($column->name, 'enable') !== false)) {
        $enableField = $column->name;
    }
    if (($imgFiled == '') && (
            (strpos($column->name, 'img') !== false)
            || (strpos($column->name, 'image') !== false)
            || (strpos($column->name, 'pic') !== false)
            || (strpos($column->name, 'picture') !== false)
        )
    ) {
        $imgFiled = $column->name;
    }
}

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
<?php if ($imgFiled != '') { echo 'use yii\web\UploadedFile;'; } ?>

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember();
<?php if (!empty($generator->searchModelClass)): ?>
        $params = Yii::$app->request->queryParams;
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
        $dataProvider = $searchModel->search($params);
        $dataProvider->sort = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $params,
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);
        $dataProvider->sort = false;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
<?php endif; ?>
    }

    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(<?= $actionParams ?>)
    {
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }

    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new <?= $modelClass ?>();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
<?php if ($imgFiled != '') { ?>
                $imgUrl = $this->uploadFile();
                if (!empty($imgUrl)) {
                    $model-><?= $imgFiled ?> = $imgUrl;
                    $model->save();
                }
<?php } ?>
                $this->saveDuplicateAction($model->id);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(<?= $actionParams ?>)
    {
        $model = $this->findModel(<?= $actionParams ?>);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
<?php if ($imgFiled != '') { ?>
                $imgUrl = $this->uploadFile();
                if (!empty($imgUrl)) {
                    $model-><?= $imgFiled ?> = $imgUrl;
                    $model->save();
                }
<?php } ?>
                $this->saveDuplicateAction($model->id);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete(<?= $actionParams ?>)
    {
        //$this->findModel(<?= $actionParams ?>)->delete();

        if (($model = $this->findModel($id)) !== null) {
            $model->status = <?= $modelClass ?>::STATUS_INVALID;
            $model->save();
        }

        if (Yii::$app->request->referrer && (strpos(Yii::$app->request->referrer, '/view') === false)) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->redirect(['index']);
        }
    }
<?php if ($enableField != '') { echo PHP_EOL; ?>
    public function actionUpdateEnable()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->post("id");
        $isEnable = Yii::$app->request->post("value");
        $flag = false;
        if (($model = $this->findModel($id)) !== null) {
            $model-><?= $enableField ?> = ($isEnable ? <?= $modelClass ?>::ENABLE_YES : <?= $modelClass ?>::ENABLE_NO);
            $flag = $model->save();
        }

        if ($flag) {
            return [
                'code' => 0,
                'msg' => '修改成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '修改失败',
            ];
        }
    }
<?php } ?>

    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
     * @return <?=                   $modelClass ?> the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>)
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function saveDuplicateAction($id)
    {
        return $this->goBack();
        $ckOption = Yii::$app->request->post('ckOption');
        if ($ckOption == 'view') {
            return $this->redirect(['view', 'id' => $id]);
        } elseif ($ckOption == 'create') {
            return $this->redirect(['create']);
        } elseif ($ckOption == 'update') {
            return $this->redirect(['update', 'id' => $id]);
        } else {
            return $this->redirect(['view', 'id' => $id]);
        }
    }
<?php if ($imgFiled != '') { echo PHP_EOL; ?>
    protected function uploadFile()
    {
        $ret = '';
        $imgObj = UploadedFile::getInstanceByName("<?= $modelClass ?>[<?= $imgFiled ?>]");
        if(empty($imgObj)) {
            return $ret;
        }

        $ossUpload = new UploadOss();
        $ossUpload->fileobj = $imgObj;
        $ret = $ossUpload->uploadOss();

        return $ret;
    }
<?php } ?>
}
