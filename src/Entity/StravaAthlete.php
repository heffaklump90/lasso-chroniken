<?php

namespace App\Entity;

use App\Repository\StravaAthleteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StravaAthleteRepository::class)
 */
class StravaAthlete
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $clientId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clientSecret;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authorizationCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $authToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenExpiryTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profileMedium;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $profile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $latestActivityData;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="stravaAthlete", cascade={"persist", "remove"})
     */
    private $user;

    public function __toString(): string
    {
        return $this->name ? $this->name : sprintf("%d", $this->clientId);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(?string $authorizationCode): self
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function getAuthToken(): ?string
    {
        return $this->authToken;
    }

    public function setAuthToken(?string $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getTokenExpiryTime(): ?\DateTimeInterface
    {
        return $this->tokenExpiryTime;
    }

    public function setTokenExpiryTime(?\DateTimeInterface $tokenExpiryTime): self
    {
        $this->tokenExpiryTime = $tokenExpiryTime;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProfileMedium(): ?string
    {
        return $this->profileMedium;
    }

    public function setProfileMedium(?string $profileMedium): self
    {
        $this->profileMedium = $profileMedium;

        return $this;
    }

    public function getProfile(): ?string
    {
        return $this->profile;
    }

    public function setProfile(?string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getLatestActivityData(): ?string
    {
        return $this->latestActivityData;
    }

    public function setLatestActivityData(?string $latestActivityData): self
    {
        $this->latestActivityData = $latestActivityData;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setStravaAthlete(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getStravaAthlete() !== $this) {
            $user->setStravaAthlete($this);
        }

        $this->user = $user;

        return $this;
    }
}
