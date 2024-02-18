<?php

namespace PromCMS\Core\PromConfig\Entity;

use PromCMS\Core\PromConfig\Entity;

class FileColumn extends RelationshipColumn
{
  function getReferencedEntity(): Entity
  {
    return $this->promConfig->getEntity('prom__files');
  }

  function getReferenceFieldName(): string
  {
    return 'id';
  }

  function getPhpType()
  {
    if ($this->isOneToMany()) {
      return '\Doctrine\Common\Collections\Collection';
    }

    return "\\" . $this->getReferencedEntity()->className;
  }
}