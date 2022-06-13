<?php

namespace Tests\Unit\Repositories;

use App\Domain\Repositories\BaseRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    private MockObject $baseRepository;
    private Model $genericModel;
    private string $fakeTableName = 'user_fakes';

    protected function setUp(): void
    {
        $this->createApplication();
        parent::setUp();
        $this->createFakeTable();

        $this->genericModel = new class extends Model {
            protected $fillable = ['name', 'height', 'birthdate'];
            protected $table    = 'user_fakes';
        };

        $this->baseRepository = $this->getMockForAbstractClass(
            BaseRepository::class,
            [$this->genericModel]
        );
    }

    private function createFakeTable()
    {
        Schema::dropIfExists($this->fakeTableName);

        Schema::create($this->fakeTableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->date('birthdate');
            $table->integer('height')->nullable();
            $table->timestamps();
        });
    }

    public function testShouldReturnAllValues()
    {
        $count = 5;
        $this->insertDataOnDummyTable([], $count);
        $result = $this->baseRepository->all();

        $this->assertCount($count, $result);
    }

    public function testShouldFindCorrectModel()
    {
        $data = [
            'name'      => $this->faker->name,
            'birthdate' => $this->faker->date,
            'height'    => $this->faker->numberBetween(152, 200)
        ];

        $id       = $this->insertDataOnDummyTable($data);
        $userFake = $this->baseRepository->find($id);
        $this->assertInstanceOf(Model::class, $userFake);
        $this->assertEquals($data['name'], $userFake->name);
        $this->assertEquals($data['height'], $userFake->height);
        $this->assertEquals($data['birthdate'], $userFake->birthdate);
    }

    public function testShouldInsertData()
    {
        $data = [
            'name'      => $this->faker->name,
            'birthdate' => $this->faker->date,
            'height'    => $this->faker->numberBetween(152, 200)
        ];

        $saved = $this->baseRepository->save($data);

        $userFake = $this->genericModel->first();
        $this->assertEquals($data['name'], $userFake->name);
        $this->assertEquals($data['height'], $userFake->height);
        $this->assertEquals($data['birthdate'], $userFake->birthdate);
        $this->assertInstanceOf(Model::class, $saved);
        $this->assertEquals($data['name'], $saved->name);
        $this->assertEquals($data['height'], $saved->height);
        $this->assertEquals($data['birthdate'], $saved->birthdate);
    }

    public function testShouldNotInsertDataWithInvalidAttributes()
    {
        $data = [
            'name_fake'      => $this->faker->name,
            'birthdate_fake' => $this->faker->date,
            'height_fake'    => $this->faker->numberBetween(152, 200)
        ];

        $this->expectExceptionMessageMatches('/Integrity constraint/');
        $this->expectExceptionCode(23000);
        $this->baseRepository->save($data);
    }

    public function testShouldFindOneByFilters()
    {
        $date           = CarbonImmutable::now()->subYears(20);
        $nameShouldFind = $this->faker->name;
        $dateShouldFind = $date->toDateString();
        $this->insertDataOnDummyTable([
            'birthdate' => $date->subDays(3)->toDateString()
        ]);
        $this->insertDataOnDummyTable([
            'birthdate' => $dateShouldFind,
            'name'      => $nameShouldFind
        ]);

        $userFake = $this->baseRepository->findOneBy([
            'birthdate' => $dateShouldFind
        ]);

        $this->assertInstanceOf(Model::class, $userFake);
        $this->assertEquals($userFake->name, $nameShouldFind);
        $this->assertEquals($userFake->birthdate, $dateShouldFind);
    }

    public function testShouldFindNone()
    {
        $date = CarbonImmutable::now()->subYears(20);
        $this->insertDataOnDummyTable([
            'birthdate' => $date->subDays(3)->toDateString()
        ]);

        $userFake = $this->baseRepository->find(-1);

        $this->assertNull($userFake);
    }

    public function testShouldFindNoneWithFilters()
    {
        $date = CarbonImmutable::now()->subYears(20);
        $this->insertDataOnDummyTable([
            'birthdate' => $date->subDays(3)->toDateString()
        ]);

        $userFake = $this->baseRepository->findOneBy([
            'birthdate' => $date->toDateString()
        ]);

        $this->assertNull($userFake);
    }

    public function testShouldFindMultiplesByFilters()
    {
        $date = CarbonImmutable::now()->subYears(20);
        $data = [
            'birthdate' => $date->subDays(3)->toDateString(),
            'height'    => 180
        ];

        $this->insertDataOnDummyTable($data, 3);

        $this->insertDataOnDummyTable([
            'birthdate' => $date->subDays(20)->toDateString(),
            'height'    => 150
        ], 9);

        $result = $this->baseRepository->findBy([
            'birthdate' => $data['birthdate'],
            'height'    => $data['height']
        ]);
        $model  = $result->first();

        $this->assertCount(3, $result);
        $this->assertEquals($model->birthdate, $data['birthdate']);
        $this->assertEquals($model->height, $data['height']);
    }

    public function testShouldFindByBringNoResults()
    {
        $date = CarbonImmutable::now()->subYears(20);
        $data = [
            'birthdate' => $date->subDays(3)->toDateString(),
            'height'    => 180
        ];

        $this->insertDataOnDummyTable($data, 3);


        $result = $this->baseRepository->findBy([
            'birthdate' => $data['birthdate'],
            'height'    => $data['height'] + 1
        ]);

        $this->assertEmpty($result);
    }

    public function testShouldDeleteNoneRows()
    {
        $this->insertDataOnDummyTable([], 5)->first();
        $rowsBeforeDeletion = $this->genericModel->all()->count();
        $deleted            = $this->baseRepository->delete(-1);
        $rowsAfterDeletion  = $this->genericModel->all()->count();

        $this->assertFalse($deleted);
        $this->assertEquals($rowsAfterDeletion, $rowsBeforeDeletion);
    }

    public function testShouldDeleteOneRow()
    {
        [$firstId, $secondId] = $this->insertDataOnDummyTable([], 2);

        $deleted     = $this->baseRepository->delete($firstId);
        $firstModel  = $this->genericModel->find($firstId);
        $secondModel = $this->genericModel->find($secondId);

        $this->assertNull($firstModel);
        $this->assertInstanceOf(Model::class, $secondModel);
        $this->assertTrue($deleted);
    }

    public function testShouldUpdateOneRow()
    {
        $originalHeight = 150;
        $updatedHeight  = 200;
        [$firstId, $secondId] = $this->insertDataOnDummyTable([
            'height' => $originalHeight
        ], 2);

        $updated = $this->baseRepository->update([
            'height' => $updatedHeight
        ], $firstId);

        $firstModel  = $this->genericModel->find($firstId);
        $secondModel = $this->genericModel->find($secondId);

        $this->assertTrue($updated);
        $this->assertEquals($updatedHeight, $firstModel->height);
        $this->assertEquals($originalHeight, $secondModel->height);
    }

    public function testShouldUpdateNoneRows()
    {
        $data = [
            'birthdate' => CarbonImmutable::now()->subYears(20)->toDateString(),
        ];

        $id    = $this->insertDataOnDummyTable($data);
        $model = $this->genericModel->find($id);

        $updated = $this->baseRepository->update([
            'birthdate' => CarbonImmutable::now()->subYears(21)
        ], -1);

        $modelAfterUpdate = $this->genericModel->find($id);

        $this->assertFalse($updated);
        $this->assertEquals($modelAfterUpdate->toArray(), $model->toArray());
    }

    public function testShouldFindByNullField()
    {
        $data = [
            'height' => null,
        ];

        $id    = $this->insertDataOnDummyTable($data);
        $model = $this->baseRepository->findOneBy($data);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($id, $model->id);
        $this->assertNull($model->height);
    }

    public function testShouldFindByFieldInArray()
    {
        $data = [
            'height' => 175,
        ];

        $id = $this->insertDataOnDummyTable($data);
        $this->insertDataOnDummyTable([
            'height' => 150,
        ]);

        $model = $this->baseRepository->findOneBy([
            'height' => [180, 170, 175]
        ]);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($id, $model->id);
        $this->assertEquals($data['height'], $model->height);
    }

    public function testShouldNotFindByFieldInArray()
    {
        $data = [
            'height' => 175,
        ];

        $id = $this->insertDataOnDummyTable($data);
        $this->insertDataOnDummyTable([
            'height' => 150,
        ]);

        $model = $this->baseRepository->findOneBy([
            'height' => [180, 190, 200, 151]
        ]);

        $this->assertNull($model);
    }

    private function insertDataOnDummyTable(
        array $data = [],
        int $times = 1
    ): int|Collection {
        if (!array_key_exists('name', $data)) {
            $data['name'] = $this->faker->name;
        }

        if (!array_key_exists('birthdate', $data)) {
            $data['birthdate'] = $this->faker->date;
        }

        if (!array_key_exists('height', $data)) {
            $data['height'] = $this->faker->numberBetween(152, 200);
        }

        $collection = collect();
        for ($i = 0; $i < $times; $i++) {
            DB::insert(
                'INSERT INTO '
                . $this->fakeTableName
                . ' (name, birthdate, height) VALUES (:name, :birthdate, :height)',
                $data
            );
            $collection->push(DB::getPdo()->lastInsertId());
        }

        return $collection->count() === 1 ? $collection->first() : $collection;
    }
}
