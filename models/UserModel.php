
<?php

class UserModel
{
    private $id;
    private $name;
    private $fam_name;
    private $phone_num;
    private $address;
    private $mail;
    private $password;
    private $role;
    private $user_type;
    private $created_at;

    public function __construct($id = null,$name = "",$fam_name = "",$phone_num = "",$address = null,$mail = "",$password = "",$role = "user",$user_type = null,$created_at = null) {
        $this->id = $id;
        $this->name = $name;
        $this->fam_name = $fam_name;
        $this->phone_num = $phone_num;
        $this->address = $address;
        $this->mail = $mail;
        $this->password = $password;
        $this->role = $role;
        $this->user_type = $user_type;
        $this->created_at = $created_at;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getFamName() { return $this->fam_name; }
    public function getUsername() { return trim($this->name . " " . $this->fam_name); }
    public function getPhoneNum() { return $this->phone_num; }
    public function getAddress() { return $this->address; }
    public function getMail() { return $this->mail; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getUserType() { return $this->user_type; }
    public function getCreatedAt() { return $this->created_at; }
    

    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setFamName($fam_name) { $this->fam_name = $fam_name; }
    public function setPhoneNum($phone_num) { $this->phone_num = $phone_num; }
    public function setAddress($address) { $this->address = $address; }
    public function setMail($mail) { $this->mail = $mail; }
    public function setPassword($password) { $this->password = $password; }
    public function setRole($role) { $this->role = $role; }
    public function setUserType($user_type) { $this->user_type = $user_type; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    public static function fromArray($data)
    {
        $user = new UserModel();

        $user->setId($data['id'] ?? null);
        $user->setName($data['name'] ?? "");
        $user->setFamName($data['fam_name'] ?? "");
        $user->setPhoneNum($data['phone_num'] ?? "");
        $user->setAddress($data['address'] ?? null);
        $user->setMail($data['mail'] ?? "");
        $user->setPassword($data['password'] ?? "");
        $user->setRole($data['role'] ?? "user");
        $user->setUserType($data['user_type'] ?? null);
	$user->setCreatedAt($data['created_at'] ?? null);

        return $user;
    }
}

