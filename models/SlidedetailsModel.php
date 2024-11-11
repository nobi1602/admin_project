<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Security;
use yii\web\IdentityInterface;

class SlidedetailsModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%slide_detail}}';
	}
	public function getSlide() {
		return $this->hasOne(Slide::className(), ['id' => 'cate_id']);
	}
	
	
}