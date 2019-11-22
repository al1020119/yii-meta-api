<?php
namespace api\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property integer $user_level
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $source_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer remarks
 * @property integer last_access
 * @property string $password write-only password
 */
class MetaUser extends ActiveRecord implements IdentityInterface
{

    /** 用户级别 */
    const
        ADMIN_ROOT = 0,
        ADMIN_ADMIN = 1,
        ADMIN_NORMAL = 2;

    /** 用户状态 */
    const
        STATUS_DELETED = 0,
        STATUS_ACTIVE = 1;

    const
        LOGIN_USERNAME = "用户名不存在，请联系管理员",
        LOGIN_PASSWORD = "密码错误，请修正后再试",
        LOGIN_STATUS = "账号已被禁用，请联系管理员",
        LOGIN_FAILED = "登录失败，请修正后再试",
        LOGIN_REQUEST = "请使用正确的请求方式";

    const
        LOGIN_SUCCESS = "退出登录成功",
        LOGOUT_SUCCESS = "退出登录成功";

    public $rememberMe = true;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',// 自己根据数据库字段修改
                'updatedAtAttribute' => 'updated_at', // 自己根据数据库字段修改, // 自己根据数据库字段修改
                'value' => function(){return date('Y-m-d H:i:s',time()+8*60*60);},
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['username','email','password_hash'],'required'],
            [['username','email'],'trim'],

            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'message' => '该用户名已经存在.'],

            ['email','email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'message' => '该邮箱已经存在.'],

            ['password_hash', 'string', 'min' => 6],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['rememberMe', 'boolean'],

        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public static function validateUser($authKey) {
        $user = self::findOne(['auth_key' => $authKey]);
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * 管理员登录
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if (!$admin = static::findByUsername($this->username)) {
            return self::LOGIN_USERNAME;
        }
        if (0 == $admin->status) {
            return self::LOGIN_STATUS;
        }
        if($admin->validatePassword($this->password_hash)){
            $user = Yii::$app->user;
            $login = $user->login(static::findByUsername($this->username), $this->rememberMe ? 3600 * 24 * 30 : 0);
            if ($login) {
                return $admin;
            }
            return self::LOGIN_FAILED;
        }
        return self::LOGIN_PASSWORD;
    }

}
