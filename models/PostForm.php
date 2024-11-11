<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class PostForm extends FormModel{
	
	public $tag;	
	
	public function attributeLabels()
	{
		return [
            'name' => "Title",
            'description' => "Description",
            'content' => "Content",
            'tag' => "Tag",
            'cate_id' => "Category"	,
            'avatar' => "avatar",
            'seo_title' => "Seo title",
            'seo_description' => "Seo description",
            'hot_prod' => "hot product",
		    'catalog' => 'hình ảnh', 
		];
	}
	
	public function rules()
	{
		return [
				[['name','content'], 'trim'],
				[['avatar'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
		];
	}
	
	public function scenarios()
	{
		return [
		    'create' => ['name','cate_id', 'description','content','seo_title','seo_description'],
		    'create_prod' => ['catalog','hot_prod','name','cate_id', 'description','content','seo_title','seo_description'],
		];
	}
	
	public function upload($id,$folder)
	{
		$path = 'upload/'.$folder.'/'.'avatar/'.$id;
		return FormModel::upload_avatar_form($id,$path);
	}
	public function upload_catalog($id)
	{
	    $path ='upload/cataloge/'.$id;
	    return FormModel::upload_form($id,$path);
	}
}