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
            'email'=>$this->string(),
            'delivery_address'=>$this->string(),
            'total'=>$this->integer(),
            'total_quantity'=>$this->integer(),
            'flag'=>$this->boolean(),
            'created_at'=> $this->dateTime(),
            'updated_at'=> $this->dateTime(),
             'delivery_at'=> $this->date()
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
