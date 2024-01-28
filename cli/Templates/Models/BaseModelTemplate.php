<?php

namespace PromCMS\Cli\Templates\Models;

use PhpParser\Modifiers;
use PromCMS\Cli\Templates\Models\Abstract\ModelTemplate;
use PromCMS\Cli\Templates\Models\ModelTemplateMode;
use PromCMS\Core\PromConfig\Entity;
use Symfony\Component\Filesystem\Path;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class BaseModelTemplate extends ModelTemplate
{
  public function __construct(string $root, Entity $entity, protected ModelTemplateMode $mode)
  {
    parent::__construct($root, $entity, $mode);

    $targetFilename = basename($this->getTarget());
    $targetDirname = dirname($this->getTarget());

    // Attach \Base suffix
    $this->setTarget(Path::join($targetDirname, 'Base', $targetFilename));
    $this->namespace .= '\\Base';

    $phpName = $this->mode === ModelTemplateMode::LOCALIZED ? $entity->getTranslationPhpName() : $entity->phpName;
    $this->header
      ->addLine('This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync.')
      ->addLine('Updates should be made to ../' . $phpName . '.php as that is not overriden.');
  }

  protected function getClass(): Stmt\Class_
  {
    $class = parent::getClass();

    $class->stmts = [
      ...$class->stmts,
      ...$this->getTraits($this->entity),
      ...$this->getProperties($this->entity),
      $this->getContructorStmt(),
      ...$this->getMethods($this->entity),
    ];

    $class->extends = new Node\Name('Entity');
    $class->attrGroups = [
      ...$class->attrGroups,
      new Node\AttributeGroup([
        new Node\Attribute(new Node\Name('ORM\MappedSuperclass'))
      ])
    ];

    return $class;
  }

  protected function getUseStatements(Entity $entity)
  {
    $uses = parent::getUseStatements($entity);

    if ($entity->localized || !empty(array_filter($entity->getRelationshipColumns(), fn($column) => $column->isManyToOne()))) {
      $uses[] = new Stmt\Use_([new Stmt\UseUse(new Node\Name('Doctrine\Common\Collections\ArrayCollection'))]);
    }

    return [...$uses, new Stmt\Use_([new Stmt\UseUse(new Node\Name('PromCMS\Core\Database\Models\Abstract\Entity'))])];
  }

  protected function getProperties(Entity $entity)
  {
    $properties = [];

    if ($this->mode === ModelTemplateMode::LOCALIZED) {
      $properties[] = new Stmt\Property(
        type: new Node\Identifier('string'),
        flags: Modifiers::PROTECTED ,
        props: [
          new Stmt\PropertyProperty(
            name: new Node\VarLikeIdentifier('locale'),
          )
        ],
        attrGroups: [
          new Node\AttributeGroup([
            new Node\Attribute(
              name: new Node\Name('ORM\Column'),
              args: [
                new Node\Arg(
                  name: new Node\Identifier('type'),
                  value: new Node\Scalar\String_('string'),
                ),
                new Node\Arg(
                  name: new Node\Identifier('name'),
                  value: new Node\Scalar\String_('locale'),
                ),
                new Node\Arg(
                  name: new Node\Identifier('nullable'),
                  value: new Node\Expr\ConstFetch(new Node\Name('false'))
                )
              ]
            )
          ])
        ],
      );

      $properties[] = new Stmt\Property(
        type: new Node\Name\FullyQualified($this->entity->getTranslationClassName()),
        flags: Modifiers::PROTECTED ,
        props: [
          new Stmt\PropertyProperty(
            name: new Node\VarLikeIdentifier('object'),
          )
        ],
        attrGroups: [
          new Node\AttributeGroup([
            new Node\Attribute(
              name: new Node\Name('ORM\ManyToOne'),
              args: [
                new Node\Arg(
                  name: new Node\Identifier('targetEntity'),
                  value: new Node\Expr\ClassConstFetch(
                    name: new Node\Identifier('class'),
                    class: new Node\Name\FullyQualified($this->entity->getTranslationClassName())
                  ),
                ),
                new Node\Arg(
                  name: new Node\Identifier('inversedBy'),
                  value: new Node\Scalar\String_('translations'),
                ),
              ]
            ),
            new Node\Attribute(
              name: new Node\Name('ORM\JoinColumn'),
              args: [
                new Node\Arg(
                  name: new Node\Identifier('type'),
                  value: new Node\Scalar\String_('string'),
                ),
                new Node\Arg(
                  name: new Node\Identifier('name'),
                  value: new Node\Scalar\String_('object_id'),
                ),
                new Node\Arg(
                  name: new Node\Identifier('nullable'),
                  value: new Node\Expr\ConstFetch(new Node\Name('false'))
                )
              ]
            )
          ])
        ],
      );
    }

    $originalProperties = parent::getProperties($entity);
    $originalProperties = array_map(function ($property) {
      if (($property->type instanceof Node\NullableType) === false) {
        $property->type = new Node\NullableType($property->type);
      }

      return $property;
    }, $originalProperties);

    $properties = [
      ...$properties,
      ...$originalProperties,
    ];

    return $properties;
  }
}