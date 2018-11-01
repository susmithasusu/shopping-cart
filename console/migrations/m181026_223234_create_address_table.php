<?php

use yii\db\Migration;

/**
 * Handles the creation of table `address`.
 */
class m181026_223234_create_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('address', [
            'id' => $this->primaryKey(),
            'customer_id'=>$this->integer(),
            'order_id'=>$this->integer(),
            'address'=>$this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('address');
    }
}
