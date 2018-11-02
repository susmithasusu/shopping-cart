<?php

use yii\db\Migration;

/**
 * Handles the creation of table `total`.
 */
class m181030_054901_create_total_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('total', [
            'id' => $this->primaryKey(),
            'customer_id'=>$this->integer(),
            'order_id'=>$this->integer(),
            'total'=>$this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('total');
    }
}
