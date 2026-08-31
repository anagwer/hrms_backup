<?php

namespace App\Http\Controllers;

use App\Models\Organization;

class OrganizationController extends Controller
{

    private $nodes;

    public function index()
    {
        return Organization::get()->toTree();
    }

    public function createOrUpdateTree()
    {
        $data = json_decode(request()->organization, true);
        $this->nodes = $data;
        foreach ($this->nodes as &$node) {
            $this->parseNode($node);
        }
        try {
            Organization::rebuildTree($this->nodes, true);
        } catch (\Exception $error) {
            return [
                'success' => false,
                'error' => $error
            ];
        }
        return [
            'success' => true
        ];
    }

    private function parseNode(&$node)
    {
        unset($node['state']);
        unset($node['domId']);
        unset($node['checked']);
        unset($node['target']);
        unset($node['checkState']);
        if (isset($node['children'])) {
            foreach ($node['children'] as &$childrenNode) {
                $this->parseNode($childrenNode);
            }
        }
    }
}
