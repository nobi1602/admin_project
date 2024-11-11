<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Security;
use yii\web\IdentityInterface;

class LangModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%lang}}';
	}

	public function rules()
	{
		return [
				[['lang_name','lang_value'], 'trim'],
		];
	}

	public static function findIdentity($id) {
		$type = self::find()
		->where([
				"id" => $id
		])
		->one();
		if (!count($type)) {
			return null;
		}
		return new static($type);
	}
	
	public function getId() {
		return $this->id;
	}
}