<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class MenudetailsModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%menu_detail}}';
	}
	public function getMenu() {
		return $this->hasOne(Menu::className(), ['id' => 'cate_id']);
	}
	
	
}