<?php

namespace ArchyBold\LaravelMusicServices\Repositories;

abstract class Repository
{
    /**
     * The entity for this repository.
     *
     * @var string $entity
     */
    protected $entity = null;

    /**
     * Get the query builder for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder()
    {
        return $this->entity::query();
    }
}
