<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Security;
use yii\web\IdentityInterface;

class ContactModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%contact}}';
    }
    
    public function attributeLabels()
    {
    }
    
    public static function findIdentity($id) {
        $contact = self::find()
        ->where([
            "id" => $id
        ])
        ->one();
        if (!count($contact)) {
            return null;
        }
        return new static($contact);
    }
    
    public function getId() {
        return $this->id;
    }
    
}