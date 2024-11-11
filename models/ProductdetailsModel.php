<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class ProductdetailsModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%product_detail}}';
	}
	public function getPost() {
		return $this->hasOne(ProductModel::className(), ['id' => 'prod_id']);
	}
	
	
}