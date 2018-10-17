<?php

use yii\db\Migration;

/**
 * Handles the creation of table `product`.
 */
class m181016_035701_create_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('product', [
            'id' => $this->primaryKey(),
            'category' => $this->integer(),
            'name' => $this->string(),
            'image' => $this->string(),
            'description' => $this->string(),
            'price' => $this->integer(),
            'count'=>$this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('product');
    }
}
