<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class AreadetailsModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%area_detail}}';
	}
	public function getPost() {
		return $this->hasOne(AreaModel::className(), ['id' => 'extra_id']);
	}
	
	
}