<?php
declare(strict_types=1);

namespace XDApp\Casbin\Entity;


use Hyperf\Utils\Contracts\Arrayable;

class Collector implements Arrayable
{

    /**@var int*/
    public int $id = 0;
    /**@var string*/
    public string $targetClass;
    /**@var string*/
    public string $object;

    /**@var string*/
    public string $description;

    /**@var string*/
    public string $targetAction;

    /**@var string*/
    public string $targetDesc;


    public function toArray(): array
    {
        return [
           'id'            => $this->id,
           'targetClass'   => $this->targetClass,
           'object'        => $this->object,
           'description'   => $this->description,
           'targetAction'     => $this->targetAction,
           'targetDesc'       => $this->targetDesc,
        ];

    }


}