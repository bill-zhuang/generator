<?php

use yii\db\Migration;

/**
 * Class m210115_145337_add_table_ty
 */
class m210115_145337_add_table_ty extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<EOF
CREATE TABLE `ty` (
  `ty_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ty_floor` int(11) unsigned NOT NULL COMMENT 'floor',
  `ty_type` tinyint(3) unsigned DEFAULT '1' COMMENT '0-not lz 1-lz',
  `ty_reply` text COMMENT 'reply',
  `ty_reply_floor` text COMMENT 'reply floor',
  PRIMARY KEY (`ty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='ty article';
EOF;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210115_145337_add_table_ty cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210115_145337_add_table_ty cannot be reverted.\n";

        return false;
    }
    */
}
