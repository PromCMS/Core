<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as PromMapping;

#[ORM\Entity, ORM\Table(name: 'prom__general_translations'), PromMapping\PromModel(ignoreSeeding: true)]
class GeneralTranslation extends Base\GeneralTranslation {
}
