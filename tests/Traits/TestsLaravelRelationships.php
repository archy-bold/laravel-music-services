<?php

namespace ArchyBold\LaravelMusicServices\Tests\Traits;

use Illuminate\Support\Collection;

trait TestsLaravelRelationships
{
    /**
     * Assert the collection doesn't contain the given model.
     *
     * @param Collection $collection
     * @param Model $model
     * @param string $message = null
     * @return void
     */
    public function assertCollectionContainsModel(Collection $collection, $model, $message = '')
    {
        $this->assertContains($model->id, $collection->pluck('id'), $message);
    }

    /**
     * Assert the collection doesn't contain the given model.
     *
     * @param Collection $collection
     * @param Model $model
     * @param string $message = null
     * @return void
     */
    public function assertCollectionNotContainsModel(Collection $collection, $model, $message = '')
    {
        $this->assertNotContains($model->id, $collection->pluck('id'), $message);
    }

    /**
     * Function for testing Laravel belongsTo relationships.
     *
     * @param string $relation - The relation name
     * @param string $class - The related class
     * @param Model $model - The model with the relationship
     * @param array $attributes - The additional attributes for the created model
     * @return void
     */
    public function assertBelongsToRelationship($relation, $class, $model, $attributes = [])
    {
        $rel = factory($class)->create($attributes);
        $model->$relation()->associate($rel);
        $model->save();
        $this->assertNotNull($model->$relation);
        $this->assertEquals($rel->id, $model->$relation->id);
    }

    /**
     * Function for testing Laravel belongsTo relationships, where related instances
     * already exist.
     *
     * @param string $relation - The relation name
     * @param Model $model - The model with the relationship
     * @param string $id - The id to set on the relatedCol
     * @param string $relatedCol - The column used by the relation
     * @param string $idCol - The column used to identify the related model
     * @return void
     */
    public function assertBelongsToRelationshipExisting($relation, $model, $id, $relatedCol, $idCol = 'id')
    {
        $model->$relatedCol = $id;
        $model->save();
        $this->assertNotNull($model->$relation);
        $this->assertEquals($id, $model->$relation->$idCol);
    }

    /**
     * Function for testing Laravel hasMany relationships.
     *
     * @param string $relation - The relation name
     * @param string $class - The related class
     * @param Model $model - The model with the relationship
     * @param array $attributes1 - The additional attributes for the created model
     * @param array $attributes2 - The additional attributes for the created model
     * @return void
     */
    public function assertHasManyRelationship($relation, $class, $model, $attributes1 = [], $attributes2 = [])
    {
        $rel1 = factory($class)->create($attributes1);
        $rel2 = factory($class)->create($attributes2);
        $model->$relation()->save($rel1);
        $model->$relation()->save($rel2);
        $this->assertCount(2, $model->$relation);
        $this->assertEquals($rel1->id, $model->$relation->get(0)->id);
        $this->assertEquals($rel2->id, $model->$relation->get(1)->id);
    }

    /**
     * Function for testing Laravel hasOne relationships.
     *
     * @param string $relation - The relation name
     * @param string $class - The related class
     * @param Model $model - The model with the relationship
     * @param array $attributes - The additional attributes for the created model
     * @return void
     */
    public function assertHasOneRelationship($relation, $class, $model, $attributes = [])
    {
        $rel = factory($class)->create($attributes);
        $model->$relation()->save($rel);
        $this->assertNotNull($model->$relation);
        $this->assertEquals($rel->id, $model->$relation->id);
    }

    /**
     * Function for testing Laravel morphedByMany relationships.
     *
     * @param string $relation - The relation name
     * @param string $class - The related class
     * @param Model $model - The model with the relationship
     * @return void
     */
    public function assertMorpheByManyRelationship($relation, $class, $model)
    {
        $this->assertHasManyRelationship($relation, $class, $model);
    }

    /**
     * Function for testing Laravel belongsToMany relationships.
     *
     * @param string $relation - The relation name
     * @param string $class - The related class
     * @param Model $model - The model with the relationship
     * @return void
     */
    public function assertBelongsToManyRelationship($relation, $class, $model)
    {
        $this->assertHasManyRelationship($relation, $class, $model);
    }
}
