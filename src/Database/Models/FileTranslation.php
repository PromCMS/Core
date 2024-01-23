<?php

namespace PromCMS\Core\Database\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: 'prom__files_translations'), ORM\UniqueConstraint(name: 'prom__files_translations_unique_idx', columns: ['locale', 'object_id', 'field']), ORM\HasLifecycleCallbacks]
class FileTranslation extends Base\FileTranslation {
}