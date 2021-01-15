<?php

use yii\db\Migration;

/**
 * Class m210115_145410_add_table_zhihu_hot_collection
 */
class m210115_145410_add_table_zhihu_hot_collection extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<EOF
CREATE TABLE `zhihu_hot_collection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(256) NOT NULL DEFAULT '' COMMENT '标题',
  `abbr_answer` varchar(1024) NOT NULL DEFAULT '' COMMENT 'abbr answer',
  `answer_url` varchar(128) NOT NULL DEFAULT '' COMMENT '回答url',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1-有效；2-无效',
  `create_time` datetime DEFAULT NULL,
  `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_answer_url` (`answer_url`)
) ENGINE=InnoDB AUTO_INCREMENT=815 DEFAULT CHARSET=utf8 COMMENT='知乎热门收藏表';
EOF;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210115_145410_add_table_zhihu_hot_collection cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210115_145410_add_table_zhihu_hot_collection cannot be reverted.\n";

        return false;
    }
    */
}
