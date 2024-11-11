<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class AreaModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%area}}';
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
	
	public function getArea_detail()
	{
		return $this->hasOne(AreadetailsModel::className(), ['extra_id' => 'id']);
	}

	public function getArea_detail_update()
	{
		return $this->hasMany(AreadetailsModel::className(), ['extra_id' => 'id']);
	}
	
	public function getUser_created()
	{
		return $this->hasOne(UserModel::className(), ['id' => 'created_by']);
	}
	
}