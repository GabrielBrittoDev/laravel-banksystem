<?php

namespace Tests\Feature\User;

use App\Domain\Enums\RoleEnum;
use App\Domain\Enums\TransactionCategoryEnum;
use App\Domain\Enums\TransactionStatusEnum;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionFile;
use App\Models\User;
use App\Models\Wallet\Wallet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function testShouldCreatePendingDeposit()
    {
        Storage::fake(config('filesystems.default'));
        $user             = User::factory()->customer()->create();
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $data = [
            'file'  => UploadedFile::fake()->image('check.png'),
            'amount' => $this->faker->randomNumber(3),
        ];

        $response = $this->post(route('transaction.deposit'), $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'status_id',
                'amount',
                'transaction_category_id'
            ],
            'message'
        ]);


        $responseData    = $response->decodeResponseJson();
        $transactionFile = TransactionFile::where('transaction_id', $responseData['data']['id'])->first();
        $this->assertNotNull($transactionFile);
        $this->assertEquals($transactionCount + 1, Transaction::count());
        $this->assertEquals($user->id, $responseData['data']['user_id']);
        $this->assertEquals($data['amount'], $responseData['data']['amount']);
        $this->assertEquals(TransactionCategoryEnum::DEPOSIT, $responseData['data']['transaction_category_id']);
        $this->assertEquals(TransactionStatusEnum::PENDING, $responseData['data']['status_id']);

        Storage::disk(config('filesystems.default'))->assertExists($transactionFile->file);
    }

    public function testShouldDenyCreatePendingDepositWithAdmin()
    {
        $user             = User::factory()->administrator()->create();
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $data = [
            'file'  => UploadedFile::fake()->image('check.png'),
            'amount' => $this->faker->randomNumber(3),
        ];

        $response = $this->post(route('transaction.deposit'), $data);
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldCreatePurchase()
    {
        $user             = User::factory()->customer()->create();
        $wallet           = Wallet::factory()->create([
            'user_id' => $user->id,
        ]);
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $data = [
            'description' => $this->faker->text,
            'amount'      => $wallet->balance - 1,
        ];

        $response = $this->post(route('transaction.purchase'), $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'status_id',
                'amount',
                'transaction_category_id'
            ],
            'message'
        ]);


        $responseData = $response->decodeResponseJson();
        $wallet       = Wallet::where('user_id', $user->id)->first();
        $this->assertEquals($wallet->balance, 1);
        $this->assertEquals($transactionCount + 1, Transaction::count());
        $this->assertEquals($user->id, $responseData['data']['user_id']);
        $this->assertEquals($data['amount'], $responseData['data']['amount'] * -1);
        $this->assertEquals(TransactionCategoryEnum::EXPANSE, $responseData['data']['transaction_category_id']);
        $this->assertEquals(TransactionStatusEnum::APPROVED, $responseData['data']['status_id']);
    }

    public function testShouldDenyCreatePurchaseWithAdmin()
    {
        $user             = User::factory()->administrator()->create();
        $wallet           = Wallet::factory()->create([
            'user_id' => $user->id,
        ]);
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $data = [
            'description' => $this->faker->text,
            'amount'      => $wallet->balance - 1,
        ];

        $response = $this->post(route('transaction.purchase'), $data);
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldAproveDeposit()
    {
        $user             = User::factory()->administrator()->create();
        $wallet           = Wallet::factory()->create([
            'user_id' => $user->id
        ]);
        $balance          = $wallet->balance;
        $transaction      = Transaction::factory()->create([
            'user_id'                 => $user->id,
            'status_id'               => TransactionStatusEnum::PENDING,
            'transaction_category_id' => TransactionCategoryEnum::DEPOSIT
        ]);
        $data             = ['option' => TransactionStatusEnum::APPROVED];
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $response = $this->post(route('transaction.admin.finish-deposit', [
            'transactionId' => $transaction->id
        ]), $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $transaction->refresh();
        $wallet->refresh();
        $this->assertEquals($transaction->amount + $balance, $wallet->balance);
        $this->assertEquals($transaction->status_id, $data['option']);
        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldRejectDeposit()
    {
        $user             = User::factory()->administrator()->create();
        $wallet           = Wallet::factory()->create([
            'user_id' => $user->id
        ]);
        $balance          = $wallet->balance;
        $transaction      = Transaction::factory()->create([
            'user_id'                 => $user->id,
            'status_id'               => TransactionStatusEnum::PENDING,
            'transaction_category_id' => TransactionCategoryEnum::DEPOSIT
        ]);
        $data             = ['option' => TransactionStatusEnum::REJECTED];
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $response = $this->post(route('transaction.admin.finish-deposit', [
            'transactionId' => $transaction->id
        ]), $data);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $transaction->refresh();
        $wallet->refresh();
        $this->assertEquals($balance, $wallet->balance);
        $this->assertEquals($transaction->status_id, $data['option']);
        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldNotFinishDepositCompleted()
    {
        $user             = User::factory()->administrator()->create();
        $wallet           = Wallet::factory()->create([
            'user_id' => $user->id,
        ]);
        $balance          = $wallet->balance;
        $statusId         = $this->faker->randomElement([
            TransactionStatusEnum::APPROVED,
            TransactionStatusEnum::REJECTED
        ]);
        $transaction      = Transaction::factory()->create([
            'user_id'                 => $user->id,
            'status_id'               => $statusId,
            'transaction_category_id' => TransactionCategoryEnum::DEPOSIT
        ]);
        $data             = ['option' => TransactionStatusEnum::APPROVED];
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $response = $this->post(route('transaction.admin.finish-deposit', [
            'transactionId' => $transaction->id
        ]), $data);

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'message'
        ]);

        $transaction->refresh();
        $wallet->refresh();
        $this->assertEquals($transaction->status_id, $statusId);
        $this->assertEquals($wallet->balance, $balance);
        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldNotFinishDepositWithCustomer()
    {
        $user             = User::factory()->customer()->create();
        $wallet           = Wallet::factory()->create(['user_id' => $user->id]);
        $balance          = $wallet->balance;
        $statusId         = $this->faker->randomElement([
            TransactionStatusEnum::APPROVED,
            TransactionStatusEnum::REJECTED
        ]);
        $transaction      = Transaction::factory()->create([
            'user_id'                 => $user->id,
            'status_id'               => $statusId,
            'transaction_category_id' => TransactionCategoryEnum::DEPOSIT
        ]);
        $data             = ['option' => TransactionStatusEnum::APPROVED];
        $transactionCount = Transaction::count();
        Sanctum::actingAs($user);
        $response = $this->post(route('transaction.admin.finish-deposit', [
            'transactionId' => $transaction->id
        ]), $data);

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);

        $transaction->refresh();
        $wallet->refresh();
        $this->assertEquals($transaction->status_id, $statusId);
        $this->assertEquals($wallet->balance, $balance);
        $this->assertEquals($transactionCount, Transaction::count());
    }

    public function testShouldListPendingDeposits()
    {
        $user             = User::factory()->administrator()->create();
        $transactionCount = $this->faker->randomNumber(1) + 1;
        Transaction::factory()->times($transactionCount)->pending()->create();
        Transaction::factory()->times($transactionCount)->approved()->create();
        Transaction::factory()->times($transactionCount)->rejected()->create();
        Sanctum::actingAs($user);
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('temporaryUrl')->andReturn('');
        $response = $this->get(route('transaction.admin.pending-deposits'));


        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'status_id',
                    'amount',
                    'description',
                    'transaction_category_id',
                    'file_url',
                    'created_at',
                    'updated_at'
                ]
            ],
        ]);

        $responseData = $response->decodeResponseJson();
        $this->assertCount($transactionCount, $responseData['data']);
        $transaction = current($responseData['data']);
        $this->assertNotNull($transaction['id']);
        $this->assertNotNull($transaction['user_id']);
        $this->assertNotNull($transaction['status_id']);
        $this->assertNotNull($transaction['amount']);
        $this->assertNotNull($transaction['description']);
        $this->assertNotNull($transaction['transaction_category_id']);
        $this->assertNotNull($transaction['file_url']);
        $this->assertNotNull($transaction['created_at']);
        $this->assertNotNull($transaction['updated_at']);
    }

    public function testShouldListTransactions()
    {
        $user             = User::factory()->customer()->create();
        $transactionCount = $this->faker->randomNumber(1) + 1;
        $transactionData  = ['user_id' => $user->id];
        Transaction::factory()->times($transactionCount)->pending()->create($transactionData);
        Transaction::factory()->times($transactionCount)->approved()->create($transactionData);
        Transaction::factory()->times($transactionCount)->rejected()->create($transactionData);
        Transaction::factory()->times($transactionCount)->create();

        Sanctum::actingAs($user);
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('temporaryUrl')->andReturn('');

        $response = $this->get(route('transaction.list', ['per_page' => 50]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'status_id',
                    'amount',
                    'description',
                    'transaction_category_id',
                    'created_at',
                    'updated_at'
                ]
            ],
        ]);

        $responseData = $response->decodeResponseJson();
        $this->assertCount($transactionCount * 3, $responseData['data']);
        $transaction = current($responseData['data']);
        $this->assertNotNull($transaction['id']);
        $this->assertNotNull($transaction['user_id']);
        $this->assertNotNull($transaction['status_id']);
        $this->assertNotNull($transaction['amount']);
        $this->assertNotNull($transaction['description']);
        $this->assertNotNull($transaction['transaction_category_id']);
        $this->assertNotNull($transaction['created_at']);
        $this->assertNotNull($transaction['updated_at']);
    }
}