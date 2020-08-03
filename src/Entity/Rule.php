<?php

declare (strict_types=1);

namespace XDApp\Casbin\Entity;

use Hyperf\Utils\Contracts\Arrayable;

class Rule implements Arrayable
{
    public int $id = 0;
    public string $ptype = "";
    public string $v0 = "";
    public string $v1 = "";
    public string $v2 = "";
    public string $v3 = "";
    public string $v4 = "";
    public string $v5 = "";

    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'ptype' => $this->ptype,
            'v0'    => $this->v0,
            'v1'    => $this->v1,
            'v2'    => $this->v2,
            'v3'    => $this->v3,
            'v4'    => $this->v4,
            'v5'    => $this->v5,
        ];
    }
}