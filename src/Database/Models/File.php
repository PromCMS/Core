<?php

namespace PromCMS\Core\Database\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as Mapping;

#[ORM\Entity, ORM\Table(name: 'prom__files'), Mapping\PromModel(ignoreSeeding: true), ORM\HasLifecycleCallbacks]
class File extends Base\File
{
}
