<?php
/**
 * @var \PromCMS\Core\PromConfig\Entity $entity
 */
echo "<?php\n";
$entityName = $entity->getTranslationPhpName();
?>

namespace <?php echo $entity->namespace; ?>;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: '<?php echo $entity->getTranslationTableName() ?>'), ORM\UniqueConstraint(name: '<?php echo $entity->getTranslationTableName() ?>_unique_idx', columns: ['locale', 'object_id', 'field']), ORM\HasLifecycleCallbacks]
class <?php echo $entityName ?> extends Base\<?php echo $entityName ?> {
}