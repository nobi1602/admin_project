<?php
namespace app\models;
use yii\base\Model;
use yii\web\IdentityInterface;

class MenuForm extends Model
{	
    public $parent_id;
    public $avatar;
	public $name;
	public $slug;
	public $url;
	public $tree;
	public $cate_relate;
	
	
	public function attributeLabels()
	{
		return [
			'parent_id' => 'parent id',	
			'name'=> 'Name',			
			'slug'=> 'Slug',
			'url' => 'Link',
		    'tree' => 'Cây liên kết',
		    'cate_relate' => ' Danh mục liên kết',
		];
	}
	public function rules()
	{
		return [
				[['name', 'slug', 'url'], 'trim'],
				[['name'], 'required', 'message'=>'Còn thiếu'],				
		];
	}
	public function scenarios()
	{
		return [
				'create' => ['name','tree','cate_relate' ],
		];
	}
	
}