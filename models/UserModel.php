<?php

class UserModel
{
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