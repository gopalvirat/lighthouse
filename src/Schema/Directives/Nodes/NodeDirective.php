<?php

namespace Nuwave\Lighthouse\Schema\Directives\Nodes;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\NodeValue;
use Nuwave\Lighthouse\Support\Contracts\NodeMiddleware;

class NodeDirective extends BaseDirective implements NodeMiddleware
{
    /**
     * Directive name.
     *
     * @return string
     */
    public function name()
    {
        return 'node';
    }

    /**
     * Handle type construction.
     *
     * @param NodeValue $value
     *
     * @return NodeValue
     */
    public function handleNode(NodeValue $value)
    {
        graphql()->nodes()->node(
            $value->getNodeName(),
            $this->getResolver($value),
            $this->getResolveType($value)
        );

        $this->registerInterface($value);

        return $value;
    }

    /**
     * Get node resolver.
     *
     * @param NodeValue $value
     *
     * @return \Closure
     */
    protected function getResolver(NodeValue $value)
    {
        $resolver = $this->directiveArgValue('resolver');

        $namespace = array_get(explode('@', $resolver), '0');
        $method = array_get(explode('@', $resolver), '1');

        return function ($id) use ($namespace, $method) {
            $instance = app($namespace);

            return call_user_func_array([$instance, $method], [$id]);
        };
    }

    /**
     * Get interface type resolver.
     *
     * @param NodeValue $value
     *
     * @return \Closure
     */
    protected function getResolveType(NodeValue $value)
    {
        $resolver = $this->directiveArgValue('typeResolver');

        $namespace = array_get(explode('@', $resolver), '0');
        $method = array_get(explode('@', $resolver), '1');

        return function ($value) use ($namespace, $method) {
            $instance = app($namespace);

            return call_user_func_array([$instance, $method], [$value]);
        };
    }

    /**
     * Register Node interface.
     *
     * @param NodeValue $value
     */
    protected function registerInterface(NodeValue $value)
    {
        if (! $value->hasInterface('Node')
            && ! is_null(config('lighthouse.global_id_field'))
        ) {
            $value->attachInterface('Node');
        }
    }
}
