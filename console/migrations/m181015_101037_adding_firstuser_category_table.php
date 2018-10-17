<?php

use yii\db\Migration;

/**
 * Class m181015_101037_adding_firstuser_category_table
 */
class m181015_101037_adding_firstuser_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $sql="INSERT INTO `category` (`id`, `category_id`, `category_name`) VALUES
        (1, '1', 'mobile');
        ";
                Yii::$app->db->createCommand($sql)->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $sql="DELETE from category where category_name='mobile'";
        Yii::$app->db->createCommand($sql)->execute();

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181015_101037_adding_firstuser_category_table cannot be reverted.\n";

        return false;
    }
    */
}
