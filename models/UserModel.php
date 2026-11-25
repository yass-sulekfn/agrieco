<<<<<<< HEAD
=======

>>>>>>> origin/main
<?php

class UserModel
{
<<<<<<< HEAD
    private ?int $id = null;
    private string $username = '';
    private string $email = '';
    private string $password = '';
    private string $role = 'client';
    private bool $canWrite = true;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getCanWrite(): bool
    {
        return $this->canWrite;
    }

    public function setCanWrite(bool $canWrite): self
    {
        $this->canWrite = $canWrite;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public static function fromArray(array $row): self
    {
        $user = new self();

        if (isset($row['id'])) {
            $user->setId((int) $row['id']);
        }
        if (isset($row['username'])) {
            $user->setUsername((string) $row['username']);
        }
        if (isset($row['email'])) {
            $user->setEmail((string) $row['email']);
        }
        if (isset($row['password'])) {
            $user->setPassword((string) $row['password']);
        }
        if (isset($row['role'])) {
            $user->setRole((string) $row['role']);
        }
        if (isset($row['can_write'])) {
            $user->setCanWrite((int) $row['can_write'] === 1);
        }
        if (isset($row['created_at'])) {
            $user->setCreatedAt((string) $row['created_at']);
        }

        return $user;
    }
}
=======
    private $id;
    private $name;
    private $fam_name;
    private $phone_num;
    private $address;
    private $mail;
    private $password;
    private $role;
    private $user_type;

    public function __construct(
        $id = null,
        $name = "",
        $fam_name = "",
        $phone_num = "",
        $address = null,
        $mail = "",
        $password = "",
        $role = "user",
        $user_type = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->fam_name = $fam_name;
        $this->phone_num = $phone_num;
        $this->address = $address;
        $this->mail = $mail;
        $this->password = $password;
        $this->role = $role;
        $this->user_type = $user_type;
    }
    //getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getFamName() { return $this->fam_name; }
    public function getPhoneNum() { return $this->phone_num; }
    public function getAddress() { return $this->address; }
    public function getMail() { return $this->mail; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getUserType() { return $this->user_type; }
    //setters
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setFamName($fam_name) { $this->fam_name = $fam_name; }
    public function setPhoneNum($phone_num) { $this->phone_num = $phone_num; }
    public function setAddress($address) { $this->address = $address; }
    public function setMail($mail) { $this->mail = $mail; }
    public function setPassword($password) { $this->password = $password; }
    public function setRole($role) { $this->role = $role; }
    public function setUserType($user_type) { $this->user_type = $user_type; }

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

        return $user;
    }
}

>>>>>>> origin/main
