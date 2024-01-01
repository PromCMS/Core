<?php

namespace PromCMS\Core\PromConfig\Entity;

use PromCMS\Core\PromConfig\Entity;

class RelationshipColumn extends Column {
  function getDatabaseColumName() {
    return parent::getDatabaseColumName() . '_id';
  }

  function getReferencedEntity(): Entity   {
    return $this->promConfig->getEntity($this->otherMetadata['targetModelTableName']);
  }

  function getReferenceFieldName(): string {
    return $this->otherMetadata['foreignKey'];
  }

  function isOneToMany() {
    
  }

  function isManyToOne(): bool {
    return isset($this->otherMetadata['multiple']) && $this->otherMetadata['multiple'];
  }

  function isOneToOne() {
    return !$this->isManyToOne();
  }

  function getPhpType() {
    if ($this->isManyToOne()) {
      return '\Doctrine\Common\Collections\Collection';
    }

    return "\\" . $this->getReferencedEntity()->className;
  }
}