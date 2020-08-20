<?php

namespace ArchyBold\LaravelMusicServices\Repositories\Contracts;

interface Repository
{
    /**
     * Get the query builder for the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder();
}
