<?php

namespace Heydavid713\RelationSpider;

trait RelationSpider
{
    /**
     * Define a one-to-one relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null, $relation = null)
    {
        if (is_subclass_of($related, 'Jenssegers\Mongodb\Eloquent\Model')) {
            $foreignKey = $foreignKey ?: $this->getForeignKey();

            $instance = new $related();

            $localKey = $localKey ?: $this->getKeyName();

            return new \Jenssegers\Mongodb\Relations\HasOne($instance->newQuery(), $this, $foreignKey, $localKey);
        }

        // Check if it is a relation with an original model.

        if (!is_subclass_of($related, 'Vinelab\NeoEloquent\Eloquent\Model')) {
            if (is_null($relation)) {
                list(, $caller) = debug_backtrace(false);
                $relation = $caller['function'];
            }

            // If no foreign key was supplied, we can use a backtrace to guess the proper
            // foreign key name by using the name of the calling class, which
            // will be uppercased and used as a relationship label
            if (is_null($foreignKey)) {
                $foreignKey = strtoupper($caller['class']);
            }

            $instance = new $related();
            // Once we have the foreign key names, we'll just create a new Eloquent query
            // for the related models and returns the relationship instance which will
            // actually be responsible for retrieving and hydrating every relations.
            $query = $instance->newQuery();

            //$localKey === $otherKey
            $otherKey = $localKey ?: $instance->getKeyName();

            return new \Vinelab\NeoEloquent\Eloquent\Relations\HasOne($query, $this, $foreignKey, $otherKey, $relation);
        }

        return parent::hasOne($related, $foreignKey, $localKey);
    }
}
