<?php
/**
 * @var \PromCMS\Core\PromConfig\Entity $entity
 */
echo "<?php\n";
?>

namespace <?php echo $entity->namespace; ?>;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

#[ORM\Entity, ORM\Table(name: '<?php echo $entity->tableName ?>'), PromMapping\PromModel(ignoreSeeding: <?php echo json_encode($entity->ignoreSeeding) ?>), ORM\HasLifecycleCallbacks]
class <?php echo $entity->phpName ?> extends Base\<?php echo $entity->phpName ?> {
}
