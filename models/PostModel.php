<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class PostModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%post}}';
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
	
	public function getPost_detail()
	{
		return $this->hasOne(PostdetailsModel::className(), ['post_id' => 'id']);
	}

	public function getPost_detail_update()
	{
		return $this->hasMany(PostdetailsModel::className(), ['post_id' => 'id']);
	}
	
	public function getUser_created()
	{
		return $this->hasOne(UserModel::className(), ['id' => 'created_by']);
	}
	
}