<?php 
 /**
   * @var \PromCMS\Core\PromConfig\Entity $entity
   */
echo "<?php\n"; 
?>

namespace <?php echo $entity->namespace; ?>;

class <?php echo $entity->phpName ?> extends Base\<?php echo $entity->phpName ?> {
}
