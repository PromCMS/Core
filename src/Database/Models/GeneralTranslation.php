<?php

namespace PromCMS\Core\Database\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

#[ORM\Entity, ORM\Table(name: 'prom__general_translations'), PromMapping\PromModel(ignoreSeeding: true), ORM\HasLifecycleCallbacks]
class GeneralTranslation extends Base\GeneralTranslation
{
}
