<?php

namespace Code16\Sharp\Tests\Unit\Form\Eloquent\Relationships;

use Code16\Sharp\Form\Eloquent\Relationships\HasManyRelationUpdater;
use Code16\Sharp\Tests\Fixtures\Person;
use Code16\Sharp\Tests\Unit\Form\Eloquent\SharpFormEloquentBaseTest;

class HasManyRelationUpdaterTest extends SharpFormEloquentBaseTest
{
    /** @test */
    function we_can_update_a_hasMany_relation()
    {
        $mother = Person::create(["name" => "Jane Wayne"]);
        $son1 = Person::create(["name" => "A", "mother_id" => $mother->id]);

        $updater = new HasManyRelationUpdater();

        $updater->update($mother, "sons", [[
            "id" => $son1->id,
            "name" => "John Wayne"
        ]]);

        $this->assertDatabaseHas("people", [
            "id" => $son1->id,
            "mother_id" => $mother->id,
            "name" => "John Wayne"
        ]);
    }

    /** @test */
    function we_can_create_a_new_related_item_in_a_hasMany_relation()
    {
        $mother = Person::create(["name" => "Jane Wayne"]);

        $updater = new HasManyRelationUpdater();

        $updater->update($mother, "sons", [[
            "id" => null,
            "name" => "John Wayne"
        ]]);

        $this->assertDatabaseHas("people", [
            "mother_id" => $mother->id,
            "name" => "John Wayne"
        ]);
    }

    /** @test */
    function the_optional_getDefaultAttributesFor_method_is_called_on_an_item_creation()
    {
        $mother = new class extends Person {
            protected $table = "people";
            function getDefaultAttributesFor($attribute) {
                return $attribute == "sons" ? ["age" => 18] : [];
            }
        };
        $mother->name = "Jane Wayne";
        $mother->save();

        $updater = new HasManyRelationUpdater();

        $updater->update($mother, "sons", [[
            "id" => null, "name" => "John Wayne"
        ]]);

        $this->assertDatabaseHas("people", [
            "mother_id" => $mother->id,
            "name" => "John Wayne",
            "age" => 18
        ]);
    }

    /** @test */
    function we_can_delete_an_existing_related_item_in_a_hasMany_relation()
    {
        $mother = Person::create(["name" => "Jane Wayne"]);
        $son1 = Person::create(["name" => "John Wayne", "mother_id" => $mother->id]);
        $son2 = Person::create(["name" => "Mary Wayne", "mother_id" => $mother->id]);

        $updater = new HasManyRelationUpdater();

        $updater->update($mother, "sons", [[
            "id" => $son1->id, "name" => "John Wayne"
        ]]);

        $this->assertDatabaseMissing("people", [
            "id" => $son2->id,
            "mother_id" => $mother->id
        ]);
    }
}