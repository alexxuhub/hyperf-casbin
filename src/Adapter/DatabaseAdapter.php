<?php

declare(strict_types=1);

namespace XDApp\Casbin\Adapter;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use XDApp\Casbin\Entity\Rule;
use XDApp\Casbin\Repository\RuleRepository;

class DatabaseAdapter implements Adapter
{
    use AdapterHelper;

    protected RuleRepository $authPolicyRepo;

    public function __construct(RuleRepository $repo)
    {
        $this->authPolicyRepo = $repo;
    }

    public function loadPolicy(Model $model): void
    {
        $rows = $this->authPolicyRepo->getAll();
        foreach ($rows as $row) {
            if ($row instanceof \stdClass) {
                $row = get_object_vars($row);
            }
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    public function savePolicyLine(string $ptype, array $rule): void
    {
        $authPolicy = new Rule();
        $authPolicy->ptype = $ptype;
        foreach ($rule as $key => $value) {
            $authPolicy->{'v'.strval($key)} = $value;
        }

        $this->authPolicyRepo->save($authPolicy);
    }

    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $this->authPolicyRepo->removePolicy($ptype, $rule);
    }

    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $this->authPolicyRepo->removeFilteredPolicy($ptype, $fieldIndex, ...$fieldValues);
    }
}