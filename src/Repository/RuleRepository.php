<?php

declare(strict_types=1);

namespace XDApp\Casbin\Repository;


use Hyperf\DbConnection\Db;
use XDApp\Casbin\Entity\Rule;

class RuleRepository
{
    protected Db $db;

    protected string $table = 'auth_rule';

    public function __construct(Db $db, array $config = [])
    {
        if (isset($config['table'])) {
            $this->table = $config['table'];
        }

        $this->db = $db;
    }

    public function setDb(Db $db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        return $this->db->table($this->table)
            ->select(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])
            ->get();
    }

    public function save(Rule $val)
    {
        $this->db->table($this->table)->updateOrInsert($val->toArray());
    }

    public function removePolicy(string $ptype, array $rule)
    {
        $instance = $this->db->table($this->table)
            ->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $instance->where('v'.strval($key), $value);
        }

        $instance->delete();
    }

    public function removeFilteredPolicy(string $ptype, int $fieldIndex, string ...$fieldValues) {
        $instance = $this->db->table($this->table)
            ->where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v'.strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }
        $instance->delete();
    }
}