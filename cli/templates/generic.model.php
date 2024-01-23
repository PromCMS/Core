<?php
/**
 * @var \PromCMS\Core\PromConfig\Entity $entity
 */
echo "<?php\n";

$attributesAsArray = [
  "ORM\Entity",
  "ORM\Table(name: '" . $entity->tableName . "')",
  "PromMapping\PromModel(ignoreSeeding: " . json_encode($entity->ignoreSeeding) . ")",
  "ORM\HasLifecycleCallbacks"
];

if ($entity->localized) {
  $attributesAsArray[] = "GedmoMapping\TranslationEntity(class: \\" . $entity->getTranslationClassName() . "::class)";
}

?>

namespace <?php echo $entity->namespace; ?>;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;
<?php if ($entity->localized): ?>
use Gedmo\Mapping\Annotation as GedmoMapping;
<?php endif; ?>

#[<?php echo implode(', ', $attributesAsArray) ?>]
class <?php echo $entity->phpName ?> extends Base\<?php echo $entity->phpName ?> {
}
