<?php

namespace PromCMS\Core\Database\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;
use Gedmo\Mapping\Annotation as GedmoMapping;

#[ORM\Entity, ORM\Table(name: 'prom__files'), PromMapping\PromModel(ignoreSeeding: true), ORM\HasLifecycleCallbacks, GedmoMapping\TranslationEntity(class: \PromCMS\Core\Database\Models\FileTranslation::class)]
class File extends Base\File {
}
