<?php

namespace app\models;

use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property integer $position
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $tree
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    public function behaviors() {
    	return [
    			\yii\behaviors\TimestampBehavior::className(),
    			'tree' => [
    					'class' => NestedSetsBehavior::className(),
    					'treeAttribute' => 'tree',
//     					'leftAttribute' => 'lft',
//     					'rightAttribute' => 'rgt',
    					// 'depthAttribute' => 'depth',
    			],
    	];
    }
    
    public function transactions()
    {
    	return [
    			self::SCENARIO_DEFAULT => self::OP_ALL,
    	];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        	
        	[['position'],'default','value'=>0],
        	[['tree', 'lft', 'rgt', 'depth', 'position', 'created_by', 'updated_by'], 'integer'],
     	
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),            
            'lft' => Yii::t('app', 'Lft'),
            'rgt' => Yii::t('app', 'Rgt'),
            'depth' => Yii::t('app', 'Depth'),
            'position' => Yii::t('app', 'Position'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'tree' => Yii::t('app', 'Tree'),
        ];
    }

    /**
     * @inheritdoc
     * @return CategoryQuery the active query used by this AR class.
     */

    public function getParentId(){
    	$parentId = $this->parent;
    	return $parentId ? $parentId->id : null ;
    }
    
    public function getParent(){
    	return $this->parents(1)->one();
    }
    
    public static function getTree($node_id = 0){
    	$children = [];
    	if(!empty($node_id))
    		$children= array_merge(
    				self::findOne($node_id)->children()->column(),
    				[$node_id]
    				);
    		
    	$row = self::find()->select('id,depth')
                    ->where(['NOT IN','id',$children])
                    ->andWhere('tree = 1')
			    	->with ( [
				    			'category_detail' => function ($query) {
					    			$query->where ( [
					    					'lang_name' => Yii::$app->language,
					    			]);
				    			}			    			
			    			] )
    				->orderBy('tree,lft')->all();
    	$return = [];

    	foreach ($row as $r)    		
    		$return[$r->id]= str_repeat('--', $r->depth). ' ' . $r['category_detail']['name'];
    		return $return;
    }
    
    public function getCategory_detail()
    {    	
        return $this->hasOne(CategorydetailsModel::className(), ['cate_id' => 'id']);
    }

    public function getCategory_detail_update()
    {    	
        return $this->hasMany(CategorydetailsModel::className(), ['cate_id' => 'id']);
    }
    
    public function getUser_created()
    {
    	return $this->hasOne(UserModel::className(), ['id' => 'created_by']);
    }
}
