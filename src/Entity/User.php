<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $activated = false;

    /**
     * @var Collection<int, AccountActivationToken>
     */
    #[ORM\OneToMany(targetEntity: AccountActivationToken::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $accountActivationTokens;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePictureKey = null;

    public function __construct()
    {
        $this->accountActivationTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): static
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * @return Collection<int, AccountActivationToken>
     */
    public function getAccountActivationTokens(): Collection
    {
        return $this->accountActivationTokens;
    }

    public function addAccountActivationToken(AccountActivationToken $accountActivationToken): static
    {
        if (!$this->accountActivationTokens->contains($accountActivationToken)) {
            $this->accountActivationTokens->add($accountActivationToken);
            $accountActivationToken->setUserId($this);
        }

        return $this;
    }

    public function removeAccountActivationToken(AccountActivationToken $accountActivationToken): static
    {
        if ($this->accountActivationTokens->removeElement($accountActivationToken)) {
            // set the owning side to null (unless already changed)
            if ($accountActivationToken->getUserId() === $this) {
                $accountActivationToken->setUserId(null);
            }
        }

        return $this;
    }

    public function getProfilePictureKey(): ?string
    {
        return $this->profilePictureKey;
    }

    public function setProfilePictureKey(?string $profilePictureKey): static
    {
        $this->profilePictureKey = $profilePictureKey;

        return $this;
    }
}
