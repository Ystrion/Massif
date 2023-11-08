<?php

declare(strict_types=1);

namespace Application\Database\Entities;

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[HasLifecycleCallbacks]
#[MappedSuperclass]
abstract class AbstractEntity
{
    #[Id]
    #[GeneratedValue]
    #[Column(name: 'id')]
    protected int $id;

    #[Column(name: 'created_at')]
    protected ?DateTime $createdAt = null;

    #[Column(name: 'updated_at')]
    protected ?DateTime $updatedAt = null;

    #[PrePersist]
    #[PreUpdate]
    public function onPrePersistOrPreUpdate(): void
    {
        $dateTimeNow = new DateTime('now');

        $this->updatedAt = $dateTimeNow;

        if ($this->createdAt === null) {
            $this->createdAt = $dateTimeNow;
        }
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
}
