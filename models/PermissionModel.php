<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Security;
use yii\web\IdentityInterface;

class PermissionModel extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%permission}}';
	}
	
	public function attributeLabels()
	{
		return [
            'permission_name'=>'Ten',						
            'permission_value'=>'Quyen',
            'status'=>'Trang thai',		
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

}