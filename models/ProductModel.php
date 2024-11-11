<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class ProductModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%product}}';
	}
	
	public function attributeLabels()
	{
		return [
				'cate_id'=>'Danh mục cha',				
		];
	}
	
	public static function findIdentity($id) {
		$post = self::find()
		->where([
				"id" => $id
		])
		->one();
		if (!count($post)) {
			return null;
		}
		return new static($post);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getCategory_detail()
	{
		return $this->hasMany(CategorydetailsModel::className(), ['cate_id' => 'cate_id'])
		->viaTable('{{%category_detail}}', ['cate_id'=>'cate_id']);
	}
	
	public function getProduct_detail()
	{
		return $this->hasOne(ProductdetailsModel::className(), ['prod_id' => 'id']);
	}

	public function getProduct_detail_update()
	{
		return $this->hasMany(ProductdetailsModel::className(), ['prod_id' => 'id']);
	}
	
	public function getUser_created()
	{
		return $this->hasOne(UserModel::className(), ['id' => 'created_by']);
	}
	
}