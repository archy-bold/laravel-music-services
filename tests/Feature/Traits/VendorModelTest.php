<?php

namespace ArchyBold\LaravelMusicServices\Tests\Feature\Traits;

use ArchyBold\LaravelMusicServices\Tests\TestCase;
use ArchyBold\LaravelMusicServices\Traits\VendorModel;
use ArchyBold\LaravelMusicServices\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class VendorModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The the vendorFind scope function.
     *
     * @return void
     */
    public function test_scope_vendorFind()
    {
        $vendor = 'spotify';
        $vendorId = 'abc123';
        $user = factory(User::class)->create([
            'vendor' => $vendor,
            'vendor_id' => $vendorId,
        ]);

        $class = $this->getClass();
        $instance = new $class;

        // Finds the existing user
        $model = $instance->vendorFind($vendor, $vendorId)->first();
        $this->assertNotNull($model);
        $this->assertEquals($user->id, $model->id);

        // Null for non-existent
        $model = $instance->vendorFind('napster', 'def456')->first();
        $this->assertNull($model);
    }

    /**
     * Test the uri attribute.
     *
     * @return void
     */
     public function test_uri_attribute()
    {
        $class = $this->getClass();
        $instance = new $class();
        $this->assertNull($instance->uri);
        $instance = new $class(['vendor_id' => 'abc123']);
        $this->assertEquals('abc123', $instance->uri);
    }

    private function getClass()
    {
        return new class() extends Model {
            use VendorModel;

            protected $fillable = ['vendor_id'];

            public function getTable()
            {
                return (new User())->getTable();
            }
        };
    }
}
