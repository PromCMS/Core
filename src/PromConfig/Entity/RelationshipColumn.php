<?php

namespace PromCMS\Core\PromConfig\Entity;

use PromCMS\Core\PromConfig\Entity;

class RelationshipColumn extends Column
{
  function getDatabaseColumName()
  {
    return parent::getDatabaseColumName() . '_id';
  }

  function getReferencedEntity(): Entity
  {
    return $this->promConfig->getEntity($this->otherMetadata['targetModelTableName']);
  }

  function getReferenceFieldName(): string
  {
    return $this->otherMetadata['foreignKey'];
  }

  function isOneToMany()
  {
    return isset($this->otherMetadata['multiple']) && $this->otherMetadata['multiple'];
  }

  function isManyToOne(): bool
  {
    $ref = $this->getReferencedEntity();

    if ($this->isOneToMany()) {
      return false;
    }

    if (!isset($this->otherMetadata['inversedBy'])) {
      return false;
    }

    $refColumn = $ref->getColumnByName($this->otherMetadata['inversedBy']);

    return $refColumn->isOneToMany();
  }

  function isOneToOne()
  {
    return !$this->isManyToOne() && !$this->isOneToMany();
  }

  function getPhpType()
  {
    if ($this->isOneToMany()) {
      return '\Doctrine\Common\Collections\Collection';
    }

    return "\\" . $this->getReferencedEntity()->className;
  }

  function hasCascadeModes(): bool
  {
    return count($this->getCascadeModes() ?? []) > 0;
  }

  function getCascadeModes(): ?array
  {
    return $this->otherMetadata['cascade'] ?? null;
  }

  function hasOnDeleteMode()
  {
    return !!$this->getOnDeleteMode();
  }

  function getOnDeleteMode(): ?string
  {
    return match ($this->otherMetadata['onDelete'] ?? '') {
      'cascade' => 'CASCADE',
      'set-null' => 'SET NULL',
      default => null,
    };
  }
}