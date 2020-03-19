<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

/* @var $this yii\web\View */
/* @var $moduleName string module name */
/* @var $controllerClass string controller class name */
/* @var $modelClass string model class name */
/* @var $modelName string model name */
/* @var $pkID string primary key */

echo "<?php\n";
?>

namespace api\modules\<?= $moduleName ?>\controllers;

use Yii;
use api\controllers\BaseController;
use common\filters\Cor;
use <?= $modelClass ?>;
use common\services\FormatService;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?>Controller extends BaseController
{
    /**
     * 全局过滤等控制方法
     * @return array
     */
    public function behaviors()
    {
        $self = [
            'corsFilter' => [
                'class' => Cor::className(),
            ],
        ];
        return array_merge(parent::behaviors(), $self);
    }

    /**
     * Lists all <?= $modelName ?> models.
     * @return array
     */
    public function actionIndex()
    {
        $params = $this->getAjaxParams();
        list($list, $total) = (new <?= ltrim($modelName, '\\') ?>())->getList($params);
        $result = ['list' => $list, 'total' => $total];
        return FormatService::success($result);
    }

    /**
     * Creates/Updates a <?= $modelName ?> model.
     * @return array
     */
    public function actionSave()
    {
        $model = new <?= $modelName ?>();
        $params = $this->getAjaxParams();
        if (isset($params['<?= $pkID ?>']) && !empty($params['<?= $pkID ?>'])) {
            $model = <?= $modelName ?>::findOne($params['<?= $pkID ?>']);
        }

        if ($model->load($params, '') && $model->save()) {
            return FormatService::success();
        }

        return FormatService::fail();
    }

    /**
     * Deletes an existing <?= $modelName ?> model.
     * @return array
     */
    public function actionDelete()
    {
        $id = $this->getParam('<?= $pkID ?>');
        $item = <?= $modelName; ?>::findOne($id);
        if (isset($item)) {
            if ($item->delete()) {
                return FormatService::success();
            }
        }
        return FormatService::fail();
    }
}
