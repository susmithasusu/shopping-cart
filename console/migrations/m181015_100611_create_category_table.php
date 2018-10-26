<?php

use yii\db\Migration;

/**
 * Handles the creation of table `category`.
 */
class m181015_100611_create_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
         
            'category_name'=>$this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('category');
    }
}
