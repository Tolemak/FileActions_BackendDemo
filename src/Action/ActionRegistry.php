<?php

namespace App\Action;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class ActionRegistry
{
    /** @var array<string, FileAction> */
    private array $actions = [];

    /**
     * @param iterable<FileAction> $actions
     */
    public function __construct(#[AutowireIterator('app.file_action')] iterable $actions)
    {
        foreach ($actions as $action) {
            $this->actions[$action->name()] = $action;
        }
    }

    public function find(string $name): ?FileAction
    {
        return $this->actions[$name] ?? null;
    }

    /**
     * @return list<FileAction>
     */
    public function all(): array
    {
        return array_values($this->actions);
    }
}
