<?php
/**
 * @var \PromCMS\Core\PromConfig\Entity $entity
 */

$localizedColumns = $entity->getLocalizedColumns();
$entityName = $entity->getTranslationPhpName();
echo "<?php\n";
?>
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../<?php echo $entityName ?>.php as that is not overriden.
 */

declare(strict_types=1);

namespace <?php echo $entity->namespace; ?>\Base;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

class <?php echo $entityName; ?> extends AbstractPersonalTranslation
{
    #[ORM\ManyToOne(targetEntity: \<?php echo $entity->className; ?>::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;

    /**
     * Convenient constructor
     *
     * @param string $locale
     * @param string $field
     * @param string $value
     */
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }
}