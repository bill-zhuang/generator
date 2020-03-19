<?php
/**
 * This is the template for generating a module class file.
 */

/* @var $moduleName string module name */

echo "<?php\n";
?>

namespace api\modules\<?= $moduleName ?>;

/**
 * <?= $moduleName ?> module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\<?= $moduleName ?>\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
