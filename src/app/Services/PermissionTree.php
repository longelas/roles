<?php

namespace LaravelEnso\Roles\App\Services;

use Illuminate\Support\Collection;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Permissions\App\Models\Permission;

class PermissionTree
{
    private Obj $tree;
    private Obj $current;

    public function __construct()
    {
        $this->tree = $this->emptyNode();
    }

    public function get(): Obj
    {
        Permission::with('menu:permission_id')
            ->orderBy('name')
            ->get()
            ->each(fn ($permission) => $this->push($permission));

        return $this->tree;
    }

    private function push(Permission $permission): void
    {
        $this->current = $this->tree;
        $this->nodes($permission);
        $this->current->get('_items')->push($permission);
    }

    private function nodes($permission): void
    {
        (new Collection(explode('.', $permission->name)))->slice(0, -1)
            ->each(fn ($segment) => $this->node($segment));
    }

    private function node($segment): void
    {
        if (! $this->current->has($segment)) {
            $this->current->set($segment, $this->emptyNode());
        }

        $this->current = $this->current->get($segment);
    }

    private function emptyNode(): Obj
    {
        return new Obj(['_items' => []]);
    }
}
