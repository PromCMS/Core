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

    foreach ($ref->getRelationshipColumns() as $refRelationColumn) {
      if ($refRelationColumn->otherMetadata['inversedBy'] === $this->name) {
        if ($refRelationColumn->isOneToMany()) {
          return true;
        }

        break;
      }
    }

    return false;
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
}