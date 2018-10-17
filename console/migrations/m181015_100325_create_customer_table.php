<?php

use yii\db\Migration;

/**
 * Class m181015_100325_customer_table
 */
class m181015_100325_create_customer_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('customer', [
            'id' => $this->primaryKey(),
            'address' => $this->string(),
            'email' => $this->string(),
            'name' => $this->string(),
            'phone' => $this->string(),
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181015_100325_customer_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181015_100325_customer_table cannot be reverted.\n";

        return false;
    }
    */
}
