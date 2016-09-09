<?php

namespace Heydavid713\RelationSpider\Eloquent\Relations;

class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (is_subclass_of($this, \Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo::class)) {
            if (static::$constraints) {

                /*
                 * Since the relationship may not exist in Neo4J, we can't use the usual
                 * method of fetching data.
                 */

                // Get the parent node's placeholder.
                $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());
                // Tell the query that we only need the related model returned.
                $this->query->select($this->relation);
                // Set the parent node's placeholder as the RETURN key.
                $this->query->getQuery()->from = array($parentNode);
                // Build the MATCH ()<-[]-() Cypher clause.
                $this->query->matchIn($this->parent, $this->related, $this->relation, $this->foreignKey, $this->otherKey, $this->parent->{$this->otherKey});
                // Add WHERE clause over the parent node's matching key = value.
                $this->query->where($this->otherKey, '=', $this->parent->{$this->otherKey});
            }
        }
    }
}
