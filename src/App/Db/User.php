<?php
namespace App\Db;

use Tk\Auth;
use Tk\Auth\Exception;
use App\Auth\Access;


/**
 * Class User
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class User extends \Tk\Db\Map\Model
{
    static $HASH_FUNCTION = 'md5';

    
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $uid = '';

    /**
     * @var string
     */
    public $username = '';

    /**
     * @var string
     */
    public $password = '';

    /**
     * @var string
     */
    public $role = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var string
     */
    public $hash = '';

    /**
     * @var string
     */
    public $notes = '';

    /**
     * @var \DateTime
     */
    public $lastLogin = null;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var \App\Auth\Access
     */
    private $access = null;


    /**
     *
     */
    public function __construct()
    {
        $this->modified = \Tk\Date::create();
        $this->created = \Tk\Date::create();
    }

    public function save()
    {
        $this->getHash();
        if (!$this->uid) {
            $this->uid = hash(self::$HASH_FUNCTION, $this->username . $this->created->format(\Tk\Date::ISO_DATE));
        }
        parent::save();
    }

    /**
     * @return Access
     */
    public function getAccess()
    {
        if (!$this->access) {
            $this->access = new Access($this);
        }
        return $this->access;
    }

    /**
     * Create a random password
     *
     * @param int $length
     * @return string
     */
    public static function createPassword($length = 8)
    {
        $chars = '234567890abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $i = 0;
        $password = '';
        while ($i <= $length) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
            $i++;
        }
        return $password;
    }

    /**
     * Get the user hash or generate one if needed
     *
     * @return string
     * @throws \Tk\Exception
     */
    public function getHash()
    {
        if (!$this->username)
            throw new \Tk\Exception('The username must be set before calling getHash()');

        if (!$this->hash) {
            $this->hash = $this->generateHash();
        }
        return $this->hash;
    }

    /**
     * Helper method to generate user hash
     * 
     * @return string
     */
    public function generateHash() 
    {
        return hash(self::$HASH_FUNCTION, sprintf('%s', $this->username));
    }

    /**
     * Set the password from a plain string
     *
     * @param $str
     * @throws Exception
     */
    public function setPassword($str = '')
    {
        if (!$str) {
            $str = self::createPassword(10);
        }
        $this->password = hash(self::$HASH_FUNCTION, $str . $this->getHash());
    }

    /**
     * Return the users home|dashboard relative url
     *
     * @return string
     * @throws \Exception
     */
    public function getHomeUrl()
    {
        if ($this->getAccess()->isAdmin())
            return '/admin/index.html';
        if ($this->getAccess()->isClient())
            return '/client/index.html';
        if ($this->getAccess()->isStaff())
            return '/staff/index.html';
        if ($this->getAccess()->isStudent())
            return '/student/index.html';
        return '/index.html';   // Should not get here unless their is no roles
    }

}


class UserValidator extends \Tk\Db\Map\Validator
{

    /**
     * Implement the validating rules to apply.
     *
     */
    protected function validate()
    {
        /** @var User $obj */
        $obj = $this->getObject();

        if (!$obj->name) {
            $this->addError('name', 'Invalid field name value.');
        }
        if (!$obj->role) {
            $this->addError('role', 'Invalid field role value.');
        }
        if (!$obj->username) {
            $this->addError('username', 'Invalid field username value.');
        } else {
            $dup = User::getMapper()->findByUsername($obj->username);
            if ($dup && $dup->getId() != $obj->getId()) {
                $this->addError('username', 'This username is already in use.');
            }
        }

        if (!filter_var($obj->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please enter a valid email address');
        } else {
            $dup = User::getMapper()->findByEmail($obj->email);
            if ($dup && $dup->getId() != $obj->getId()) {
                $this->addError('email', 'This email is already in use.');
            }
        }

    }
}
