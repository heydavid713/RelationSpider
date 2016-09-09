<?php

namespace Heydavid713\RelationSpider;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

            $instance = new $related;
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

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $otherKey
     * @param string $relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation)) {
            list($current, $caller) = debug_backtrace(false, 2);

            $relation = $caller['function'];
        }


        // Check if it is a relation with an original model.
        if (is_subclass_of($related, 'Jenssegers\Mongodb\Eloquent\Model')) {
            // If no foreign key was supplied, we can use a backtrace to guess the proper
            // foreign key name by using the name of the relationship function, which
            // when combined with an "_id" should conventionally match the columns.
            if (is_null($foreignKey)) {
                $foreignKey = Str::snake($relation) . '_id';
            }

            $instance = new $related();

            // Once we have the foreign key names, we'll just create a new Eloquent query
            // for the related models and returns the relationship instance which will
            // actually be responsible for retrieving and hydrating every relations.
            $query = $instance->newQuery();

            $otherKey = $otherKey ?: $instance->getKeyName();

            return new \Jenssegers\Mongodb\Relations\BelongsTo($query, $this, $foreignKey, $otherKey, $relation);

        }

        if (is_subclass_of($related, 'Vinelab\NeoEloquent\Eloquent\Model')) {

            // If no foreign key was supplied, we can use a backtrace to guess the proper
            // foreign key name by using the name of the calling class, which
            // will be uppercased and used as a relationship label
            if (is_null($foreignKey)) {
                $foreignKey = strtoupper($caller['class']);
            }
            $instance = new $related;
            // Once we have the foreign key names, we'll just create a new Eloquent query
            // for the related models and returns the relationship instance which will
            // actually be responsible for retrieving and hydrating every relations.
            $query = $instance->newQuery();
            $otherKey = $otherKey ?: $instance->getKeyName();
            return new \Heydavid713\RelationSpider\Eloquent\Relations\BelongsTo($query, $this, $foreignKey, $otherKey, $relation);
            return new \Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo($query, $this, $foreignKey, $otherKey, $relation);
        }


        return parent::belongsTo($related, $foreignKey, $otherKey, $relation);
    }

}
