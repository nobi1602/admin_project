<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class InfoModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%info}}';
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
    public function getInfo_detail()
    {
        return $this->hasMany(InfodetailsModel::className(), ['id_info' => 'id']);
    }
}