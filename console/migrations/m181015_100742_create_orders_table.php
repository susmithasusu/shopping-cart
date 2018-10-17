<?php

use yii\db\Migration;

/**
 * Handles the creation of table `orders`.
 */
class m181015_100742_create_orders_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('orders', [
            'id' => $this->primaryKey(),
            'customer_id'=>$this->integer(),
            'product_id'=>$this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('orders');
    }
}
