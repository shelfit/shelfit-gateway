<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    public const VALIDATION_GROUP_REGISTER = 'register';
    public const PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        #[Assert\NotBlank(
            message: "Username cannot be blank",
            normalizer: "trim",
            groups: [self::VALIDATION_GROUP_REGISTER],
        )]
        private ?string $username = null,

        #[Assert\NotBlank(
            message: "Email cannot be blank",
            normalizer: "trim",
            groups: [self::VALIDATION_GROUP_REGISTER],
        )]
        #[Assert\Email(
            message: "Not a valid email address",
            groups: [self::VALIDATION_GROUP_REGISTER])
        ]
        private ?string $email = null,

        #[Assert\NotBlank(
            message: "Password cannot be blank",
            normalizer: "trim",
            groups: [self::VALIDATION_GROUP_REGISTER],
        )]
        #[Assert\Length(
            min: self::PASSWORD_MIN_LENGTH,
            minMessage: "Password must be at least " . self::PASSWORD_MIN_LENGTH . " characters long",
            groups: [self::VALIDATION_GROUP_REGISTER],
        )]
        private ?string $password = null,
        private ?string $bio = null,
    ) {
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }
}