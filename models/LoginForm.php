<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\UserModel;


/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends UserModel
{
    public $username;
    public $password;
    public $rememberMe = true;
    
    
    private $_user = false;
    private $_pass = false;
    private $_role = false;
    
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
    
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function hashPassword($password)
    {
        return md5($password).'n0b1';
    }
    
    public function validatePassword($password)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            $pass = $this->hashPassword($this->password);
            
            if(!$user || !$pass ){
                $this->addError('password', Yii::t('app', 'Incorrect email or password.'));
            }
            
            if ($user->password != $pass ) {
                $this->addError('password', Yii::t('app', 'Incorrect email or password.'));
            }
        }
    }
    
    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
            //return Yii::$app->user->login($this->getUser());
        }
        return false;
    }
    
    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserModel::findByUsername($this->username);
        }
        return $this->_user;
    }
    
    public function getPassword()
    {
        if ($this->_pass === false) {
            $this->_pass = UserModel::findByPassword($this->hashPassword($this->password));
        }
        return $this->_pass;
    }
    
}
