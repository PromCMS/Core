<?php
/**
 * @var \PromCMS\Core\PromConfig\Entity $entity
 */
echo "<?php\n";
$entityName = $entity->getTranslationPhpName();

$attributesAsArray = [
  "ORM\Entity(repositoryClass: TranslationRepository::class))",
  "ORM\Table(name: '" . $entity->getTranslationTableName() . "')",
  "ORM\UniqueConstraint(name: '" . $entity->getTranslationTableName() . "_unique_idx', columns: ['locale', 'object_id', 'field'])",
  "ORM\HasLifecycleCallbacks"
];

if ($entity->localized) {
  $attributesAsArray[] = "GedmoMapping\TranslationEntity(class: \\" . $entity->getTranslationClassName() . "::class)";
}
?>

namespace <?php echo $entity->namespace; ?>;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

#[<?php echo implode(', ', $attributesAsArray) ?>]
class <?php echo $entityName ?> extends Base\<?php echo $entityName ?> {
}