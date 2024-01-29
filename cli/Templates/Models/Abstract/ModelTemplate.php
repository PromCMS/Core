<?php

namespace PromCMS\Cli\Templates\Models\Abstract;

use PhpParser\Modifiers;
use PromCMS\Cli\Templates\AbstractTemplate;
use PromCMS\Cli\Templates\Models\ModelTemplateMode;
use PromCMS\Core\Database\Models\Trait\Localized;
use PromCMS\Core\PromConfig\Entity;
use PhpParser\Comment;
use PhpParser\Node\Stmt;
use PhpParser\Node;
use PromCMS\Core\PromConfig\Entity\Column;
use Symfony\Component\Filesystem\Path;

abstract class ModelTemplate extends AbstractTemplate
{
  protected string $namespace;
  public static function from(string $root, Entity $entity, ModelTemplateMode $mode)
  {
    return new static($root, $entity, $mode);
  }

  public function __construct(string $root, public readonly Entity $entity, protected ModelTemplateMode $mode)
  {
    parent::__construct(Path::join($root, ($mode === ModelTemplateMode::LOCALIZED ? $entity->getTranslationPhpName() : $entity->phpName) . '.php'));

    $this->namespace = $entity->namespace;
  }

  protected function getNamespace()
  {
    $headerAsExpression = $this->header->toExpression();

    return new Stmt\Namespace_(
      name: new Node\Name($this->namespace),
      stmts: [
        ...$this->getUseStatements($this->entity),
        $this->getClass()
      ],
      attributes: $headerAsExpression ? [
        'comments' => [
          $headerAsExpression
        ]
      ] : []
    );
  }

  public function generateAst()
  {
    $this->ast = [
      $this->getNamespace()
    ];
  }

  function save()
  {
    $this->generateAst();

    return parent::save();
  }

  protected function getClass(): Stmt\Class_
  {
    return new Stmt\Class_(
      ($this->mode === ModelTemplateMode::LOCALIZED ? $this->entity->getTranslationPhpName() : $this->entity->phpName)
    );
  }

  protected function getArrayCollectionColumns(Entity $entity)
  {
    $localizedOutput = $this->mode === ModelTemplateMode::LOCALIZED;

    return array_filter($this->entity->getRelationshipColumns(), function ($column) use ($localizedOutput) {
      if (($localizedOutput && !$column->localized) || (!$localizedOutput && $column->localized)) {
        return false;
      }

      return $column->isManyToOne();
    });
  }

  protected function getContructorStmt(): Stmt\ClassMethod
  {
    $lines = [];
    $manyToOneColumns = $this->getArrayCollectionColumns($this->entity);

    foreach ($manyToOneColumns as $column):
      $lines[] = new Stmt\Expression(
        new Node\Expr\Assign(
          var: new Node\Expr\PropertyFetch(
            var: new Node\Expr\Variable('this'),
            name: new Node\Identifier($column->name)
          ),
          expr: new Node\Expr\New_(new Node\Name('ArrayCollection'))
        )
      );
    endforeach;

    if ($this->entity->localized && $this->mode !== ModelTemplateMode::LOCALIZED) {
      $lines[] = new Stmt\Expression(
        new Node\Expr\Assign(
          var: new Node\Expr\PropertyFetch(
            var: new Node\Expr\Variable('this'),
            name: new Node\Identifier('translations')
          ),
          expr: new Node\Expr\New_(new Node\Name('ArrayCollection'))
        )
      );
    }

    return new Stmt\ClassMethod(new Node\Identifier('__construct'), [
      'stmts' => $lines
    ]);
  }

  protected function getClassTraits()
  {
    return $this->entity->traits;
  }

  protected function createTraitStmts(Entity $entity)
  {
    $uses = [];

    foreach ($this->getClassTraits() as $traitClass) {
      $adaptations = [];

      if ($traitClass === Localized::class) {
        $adaptations[] = new Stmt\TraitUseAdaptation\Alias(
          // We override this trait method and retyping it
          method: new Node\Identifier('getTranslations'),
          newModifier: Modifiers::PROTECTED ,
          newName: new Node\Identifier('getTranslationsOriginal'),
          trait: null
        );
      }

      $uses[] = new Stmt\TraitUse(
        traits: [
          new Node\Name\FullyQualified($traitClass),
        ],
        adaptations: $adaptations
      );
    }

    return $uses;
  }

  protected function getProperties(Entity $entity)
  {
    $properties = [];

    foreach ($entity->getColumns() as $column) {
      if ($this->mode === ModelTemplateMode::LOCALIZED && !$column->localized) {
        continue;
      }

      $typeIndentifier = new Node\Identifier($column->getPhpType());
      if (!$column->required) {
        $typeIndentifier = new Node\NullableType($typeIndentifier);
      }

      $properties[] = new Stmt\Property(
        type: $typeIndentifier,
        flags: Modifiers::PROTECTED ,
        props: [
          new Stmt\PropertyProperty(
            name: new Node\VarLikeIdentifier($column->name),
            // default:... TODO
          )
        ],
        attrGroups: [
          new Node\AttributeGroup([
            ...$this->getDoctrineColumnAttributes($column),
            // This should be last attribute
            $this->getPromColumnAttribute($column)
          ])
        ],
        attributes: [
          'comments' => $column instanceof RelationshipColumn && $column->isManyToOne() ? [
            new Comment\Doc('/**
* @var ArrayCollection<int, ' . $column->getReferencedEntity()->className . '>
*/')
          ] : []
        ]
      );
    }

    // Adds $translations reference property
    if ($this->mode !== ModelTemplateMode::LOCALIZED && $entity->localized) {
      $properties[] = new Stmt\Property(
        // type: new Node\NullableType(new Node\Identifier('ArrayCollection')),
        flags: Modifiers::PROTECTED ,
        props: [
          new Stmt\PropertyProperty(
            name: new Node\VarLikeIdentifier('translations'),
            // default:... TODO
          )
        ],
        attrGroups: [
          new Node\AttributeGroup([
            new Node\Attribute(
              name: new Node\Name('ORM\OneToMany'),
              args: [
                new Node\Arg(
                  name: new Node\Identifier('targetEntity'),
                  value: new Node\Expr\ClassConstFetch(
                    name: new Node\Identifier('class'),
                    class: new Node\Name\FullyQualified($entity->getTranslationClassName())
                  )
                ),
                new Node\Arg(
                  name: new Node\Identifier('mappedBy'),
                  value: new Node\Scalar\String_('object'),
                ),
                new Node\Arg(
                  name: new Node\Identifier('cascade'),
                  value: new Node\Expr\Array_([
                    new Node\Expr\ArrayItem(
                      new Node\Scalar\String_('persist')
                    ),
                    new Node\Expr\ArrayItem(
                      new Node\Scalar\String_('remove')
                    ),
                  ]),
                )
              ]
            )
          ])
        ],
        attributes: [
          'comments' => [
            new Comment\Doc('/**
* @var ArrayCollection<int, \\' . $entity->getTranslationClassName() . '>
*/')
          ]
        ]
      );
    }

    return $properties;
  }

  protected function getDoctrineColumnAttributes(Column $column)
  {
    $attributes = [];
    // These default are predefined and same for both join and normal columns
    $columnAttributeArguments = [
      new Node\Arg(
        name: new Node\Identifier('name'),
        value: new Node\Scalar\String_($column->getDatabaseColumName())
      ),
      new Node\Arg(
        name: new Node\Identifier('nullable'),
        value: new Node\Expr\ConstFetch(new Node\Name(json_encode(!$column->required)))
      ),
      new Node\Arg(
        name: new Node\Identifier('unique'),
        value: new Node\Expr\ConstFetch(new Node\Name(json_encode($column->unique)))
      ),
    ];

    if ($column instanceof RelationshipColumn) {
      // Join collumns have special attributes first
      $attributes[] = new Node\Attribute(
        name: new Node\Name('ORM\\' . ($column->isManyToOne() ? 'ManyToOne' : 'OneToOne')),
        args: [
          new Node\Arg(
            name: new Node\Identifier('targetEntity'),
            value: new Node\Expr\ClassConstFetch(
              name: new Node\Identifier('class'),
              class: new Node\Name\FullyQualified('\\' . $column->getReferencedEntity()->className)
            )
          )
        ]
      );

      $columnAttributeArguments[] = new Node\Arg(
        name: new Node\Identifier('referencedColumnName'),
        value: new Node\Scalar\String_($column->getReferenceFieldName())
      );
    } else {
      $columnAttributeArguments[] = new Node\Arg(
        name: new Node\Identifier('type'),
        value: new Node\Scalar\String_($column->getDoctrineType())
      );

      if ($column->isEnumColumn()) {
        $columnAttributeArguments[] = new Node\Arg(
          name: new Node\Identifier('enumType'),
          value: new Node\Expr\ClassConstFetch(
            name: new Node\Identifier('class'),
            class: new Node\Name($column->getPhpType())
          )
        );
      }
    }

    // In many-to-one relationship there are two sides, owning and reflecting side.
    // If user defineds it, the reflecting side now have collection of its that references current item.
    // Other side must be marked as readonly othervise it will be a database collumn which should not happen
    if (($column instanceof RelationshipColumn) === false && !$column->readonly) {
      $attributes[] = new Node\Attribute(
        name: new Node\Name('ORM\\' . ($column instanceof RelationshipColumn ? 'JoinColumn' : 'Column')), // TODO: manyToOne requires joinColumn?
        args: $columnAttributeArguments
      );
    }

    return $attributes;
  }

  protected function getPromColumnAttribute(Column $column)
  {
    return new Node\Attribute(
      name: new Node\Name('PROM\PromModelColumn'),
      args: [
        new Node\Arg(
          name: new Node\Identifier('title'),
          value: new Node\Scalar\String_($column->title)
        ),
        new Node\Arg(
          name: new Node\Identifier('type'),
          value: new Node\Scalar\String_($column->type)
        ),
        new Node\Arg(
          name: new Node\Identifier('editable'),
          value: new Node\Expr\ConstFetch(new Node\Name(json_encode($column->readonly)))
        ),
        new Node\Arg(
          name: new Node\Identifier('hide'),
          value: new Node\Expr\ConstFetch(new Node\Name(json_encode($column->hide)))
        ),
        new Node\Arg(
          name: new Node\Identifier('localized'),
          value: new Node\Expr\ConstFetch(new Node\Name(json_encode($column->localized)))
        ),
      ]
    );
  }

  protected function getUseStatements(Entity $entity)
  {
    $items = [
      'Doctrine\ORM\Mapping' => new Node\Identifier('ORM'),
      'PromCMS\Core\Database\Models\Mapping' => new Node\Identifier('PROM'),
    ];

    $statements = [];
    foreach ($items as $name => $alias) {
      $statements[] = new Stmt\Use_(
        uses: [
          new Stmt\UseUse(new Node\Name($name), $alias)
        ]
      );
    }

    return $statements;
  }

  protected function getMethods(Entity $entity)
  {
    $methods = [];

    // Add collection initializer for relation properties
    $initCollectionsMethodLines = [];
    $manyToOneColumns = $this->getArrayCollectionColumns($this->entity);

    foreach ($manyToOneColumns as $column):
      $initCollectionsMethodLines[] = new Stmt\Expression(
        new Node\Expr\AssignOp\Coalesce(
          var: new Node\Expr\PropertyFetch(
            var: new Node\Expr\Variable('this'),
            name: new Node\Identifier($column->name)
          ),
          expr: new Node\Expr\New_(new Node\Name('ArrayCollection'))
        )
      );
    endforeach;

    if ($this->entity->localized && $this->mode !== ModelTemplateMode::LOCALIZED) {
      $initCollectionsMethodLines[] = new Stmt\Expression(
        new Node\Expr\AssignOp\Coalesce(
          var: new Node\Expr\PropertyFetch(
            var: new Node\Expr\Variable('this'),
            name: new Node\Identifier('translations')
          ),
          expr: new Node\Expr\New_(new Node\Name('ArrayCollection'))
        )
      );
    }

    $methods[] = new Stmt\ClassMethod(
      name: new Node\Identifier('__prom__initCollections'),
      subNodes: [
        'attrGroups' => [
          new Node\AttributeGroup([
            new Node\Attribute(
              new Node\Name('ORM\PostLoad')
            )
          ])
        ],
        'stmts' => $initCollectionsMethodLines,
      ]
    );

    if ($this->mode !== ModelTemplateMode::LOCALIZED && $entity->localized) {
      $methods[] = new Stmt\ClassMethod(
        name: new Node\Identifier('getTranslations'),
        subNodes: [
          'returnType' => new Node\Name('ArrayCollection'),
          'stmts' => [
            new Stmt\Return_(
              new Node\Expr\MethodCall(
                var: new Node\Expr\Variable('this'),
                name: new Node\Identifier('getTranslationsOriginal')
              )
            )
          ]
        ],
        attributes: [
          'comments' => [
            new Comment\Doc('/**
* @return ArrayCollection<string, \\' . $entity->getTranslationClassName() . '>
*/')
          ]
        ]
      );

      $addTranslationParam = new Node\Expr\Variable('translation');
      $thisVariable = new Node\Expr\Variable('this');
      $methods[] = new Stmt\ClassMethod(
        name: new Node\Identifier('addTranslation'),
        subNodes: [
          'returnType' => new Node\Identifier('static'),
          'params' => [
            new Node\Param(
              var: $addTranslationParam,
              type: new Node\Name('\\' . $entity->getTranslationClassName())
            )
          ],
          'stmts' => [
            new Stmt\If_(
              new Node\Expr\BooleanNot(
                new Node\Expr\MethodCall(
                  var: new Node\Expr\PropertyFetch(
                    var: $thisVariable,
                    name: new Node\Identifier('translations')
                  ),
                  name: new Node\Identifier('contains'),
                  args: [
                    new Node\Arg($addTranslationParam)
                  ]
                )
              ),
              [
                'stmts' => [
                  new Stmt\Expression(
                    new Node\Expr\MethodCall(
                      var: new Node\Expr\Variable('translation'),
                      name: new Node\Identifier('setObject'),
                      args: [
                        new Node\Arg($thisVariable)
                      ]
                    )
                  ),
                  new Stmt\Expression(
                    new Node\Expr\MethodCall(
                      var: new Node\Expr\PropertyFetch(
                        var: $thisVariable,
                        name: new Node\Identifier('translations')
                      ),
                      name: new Node\Identifier('set'),
                      args: [
                        new Node\Arg(
                          new Node\Expr\MethodCall(
                            var: new Node\Expr\Variable('translation'),
                            name: new Node\Identifier('getLocale'),
                          )
                        ),
                        new Node\Arg($addTranslationParam)
                      ]
                    )
                  )
                ]
              ]
            ),
            new Stmt\Return_(
              $thisVariable
            )
          ]
        ],
      );
    }

    // Print out dynamic getters and setters for entity fields
    foreach ($entity->getColumns() as $column) {
      if ($this->mode === ModelTemplateMode::LOCALIZED && !$column->localized) {
        continue;
      }

      // TODO - relationship columns are trickier, many to one will not have setter with 'set' but 'add' etc...
      $typeIndentifier = new Node\Identifier($column->getPhpType());
      if ($column instanceof RelationshipColumn) {
        if ($column->isOneToMany()) {
          $typeIndentifier = new Node\Name\FullyQualified($column->getReferencedEntity()->className);
        }
      }

      if (!$column->required) {
        $typeIndentifier = new Node\NullableType($typeIndentifier);
      }

      $thisPropertyFetch = new Node\Expr\PropertyFetch(
        var: new Node\Expr\Variable('this'),
        name: new Node\Identifier($column->name)
      );

      // GETTER
      $methods[] = new Stmt\ClassMethod(
        name: new Node\Identifier('get' . ucfirst($column->name)),
        subNodes: [
          'returnType' => $typeIndentifier,
          'stmts' => [
            new Stmt\Return_(
              $thisPropertyFetch
            )
          ]
        ]
      );

      $setterParamVariable = new Node\Expr\Variable($column->name);

      // SETTER
      $methods[] = new Stmt\ClassMethod(
        name: new Node\Identifier('set' . ucfirst($column->name)),
        subNodes: [
          'params' => [
            new Node\Param(
              var: $setterParamVariable,
              type: $typeIndentifier
            )
          ],
          'stmts' => [
            new Stmt\Expression(
              new Node\Expr\Assign(
                var: $thisPropertyFetch,
                expr: $setterParamVariable
              )
            ),
            new Stmt\Return_(
              new Node\Expr\Variable('this')
            )
          ],
          'returnType' => new Node\Name('static'),
        ]
      );
    }

    return $methods;
  }
}