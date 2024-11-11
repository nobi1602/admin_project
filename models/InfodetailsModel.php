<?php
namespace app\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class InfodetailsModel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%info_detail}}';
    }
    public function getPost() {
        return $this->hasMany(InfoModel::className(), ['id' => 'id_info']);
    }
    
    
}