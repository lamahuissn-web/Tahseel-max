<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\WhatsAppControlCenterController;
use App\Models\Admin;
use App\Models\WhatsAppBatch;
use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppQueueState;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppQueueScalableUxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
        ]);
        DB::purge();
        $this->createTables();
        $admin = Admin::query()->create([
            'name' => 'Queue tester',
            'email' => 'queue@example.test',
            'password' => bcrypt('testing'),
        ]);
        $this->actingAs($admin, 'admin');
        Gate::before(fn () => false);
    }

    public function test_default_queue_loads_only_ten_attention_batches_and_no_message_details(): void
    {
        foreach (range(1, 14) as $index) {
            $this->batchWithMessages('attention-'.$index, 'queued', ['sent']);
        }
        foreach (range(1, 12) as $index) {
            $this->batchWithMessages('complete-'.$index, 'completed', ['sent']);
        }
        foreach (range(1, 11) as $index) {
            $this->batchWithMessages('cancelled-'.$index, 'cancelled', [$index === 1 ? 'failed' : 'cancelled']);
        }
        $this->batchWithMessages('completed-but-failed', 'completed', ['failed']);

        $data = $this->queueData(Request::create('/ar/admin/whatsapp/queue', 'GET'));

        $this->assertSame('attention', $data['section']);
        $this->assertSame(10, $data['batches']->perPage());
        $this->assertSame(15, $data['batches']->total());
        $this->assertSame('batches_page', $data['batches']->getPageName());
        $this->assertSame(['attention' => 15, 'completed' => 12, 'cancelled' => 11], $data['sectionCounts']);
        $this->assertNull($data['selectedBatch']);
        $this->assertNull($data['messages']);
        $this->assertArrayNotHasKey('recent', $data);
        $this->assertFalse($data['batches']->contains(fn (WhatsAppBatch $batch) => in_array($batch->status, ['completed', 'cancelled'], true)));
    }

    public function test_selected_batch_is_validated_and_its_filtered_messages_paginate_independently(): void
    {
        $selected = $this->batchWithMessages('selected', 'running', array_fill(0, 13, 'failed'), [
            'source' => 'automation',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $other = $this->batchWithMessages('other', 'running', array_fill(0, 12, 'failed'), ['source' => 'automation']);
        $archived = $this->batchWithMessages('archived', 'running', ['failed'], ['source' => 'automation', 'archived_at' => now()]);

        $request = Request::create('/ar/admin/whatsapp/queue', 'GET', [
            'section' => 'attention',
            'source' => 'automation',
            'status' => 'failed',
            'batch' => $selected->id,
            'batches_page' => 2,
        ]);
        $data = $this->queueData($request);

        $this->assertSame($selected->id, $data['selectedBatch']->id);
        $this->assertSame(10, $data['messages']->perPage());
        $this->assertSame(13, $data['messages']->total());
        $this->assertSame('messages_page', $data['messages']->getPageName());
        $this->assertTrue($data['messages']->every(fn (WhatsAppMessageLog $message) => $message->batch_id === $selected->id));
        $this->assertFalse($data['messages']->contains(fn (WhatsAppMessageLog $message) => $message->batch_id === $other->id));

        $archivedData = $this->queueData(Request::create('/ar/admin/whatsapp/queue', 'GET', [
            'batch' => $archived->id,
        ]));
        $this->assertNull($archivedData['selectedBatch']);
        $this->assertNull($archivedData['messages']);
    }

    public function test_queue_blade_contract_uses_compact_responsive_details_and_hidden_provenance(): void
    {
        $blade = file_get_contents(resource_path('views/dashbord/whatsapp/queue.blade.php'));

        $this->assertStringContainsString('queue-batch-list', $blade);
        $this->assertStringContainsString('queue-messages-desktop', $blade);
        $this->assertStringContainsString('queue-messages-mobile', $blade);
        $this->assertStringContainsString('<details', $blade);
        $this->assertStringContainsString('$maskPhone', $blade);
        $this->assertStringContainsString('messages_page', $blade);
        $this->assertStringContainsString("except(['batch','messages_page'])", $blade);
        $this->assertStringContainsString("except(['section','batches_page','batch','messages_page'])", $blade);
        $this->assertStringNotContainsString('<th>المرجع</th>', $blade);
        $this->assertStringNotContainsString('$recent', $blade);
        $this->assertSame(1, substr_count($blade, '{{ $log->message }}'));
    }

    public function test_default_queue_contract_guards_message_detail_region_by_selection(): void
    {
        $blade = file_get_contents(resource_path('views/dashbord/whatsapp/queue.blade.php'));

        $this->assertStringContainsString('@if($selectedBatch && $messages)', $blade);
        $this->assertStringNotContainsString('DataTable', $blade);
    }

    public function test_queue_blade_compiles(): void
    {
        $blade = file_get_contents(resource_path('views/dashbord/whatsapp/queue.blade.php'));
        $compiled = Blade::compileString($blade);

        $this->assertStringContainsString('queue-batch-list', $compiled);
        $this->assertStringContainsString('queue-messages-mobile', $compiled);
    }

    private function queueView(Request $request)
    {
        app()->instance('request', $request);

        return app(WhatsAppControlCenterController::class)
            ->queue($request, app(WhatsAppQueueState::class));
    }

    private function queueData(Request $request): array
    {
        return $this->queueView($request)->getData();
    }

    private function batchWithMessages(string $uuid, string $status, array $messageStatuses, array $overrides = []): WhatsAppBatch
    {
        $batch = WhatsAppBatch::query()->create(array_merge([
            'uuid' => $uuid,
            'source' => 'manual',
            'title' => $uuid,
            'status' => $status,
        ], $overrides));

        foreach ($messageStatuses as $offset => $messageStatus) {
            WhatsAppMessageLog::query()->create([
                'batch_id' => $batch->id,
                'client_id' => $offset + 1,
                'client_name' => 'Customer '.$offset,
                'phone' => '96170000'.str_pad((string) $offset, 3, '0', STR_PAD_LEFT),
                'message' => 'Private provider provenance '.$uuid,
                'template_type' => 'reminder',
                'sent_by' => 'admin:manual|batch:'.$uuid,
                'status' => $messageStatus,
                'created_at' => now()->subMinutes($offset),
                'updated_at' => now()->subMinutes($offset),
            ]);
        }

        return $batch;
    }

    private function createTables(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('admin');
            $table->timestamps();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('admin');
            $table->timestamps();
        });
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('app_config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        Schema::create('whatsapp_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('source');
            $table->string('title');
            $table->string('template_type')->nullable();
            $table->string('status')->default('queued');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->text('invoice_ids')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('template_type')->nullable();
            $table->string('sent_by')->nullable();
            $table->string('status')->default('pending');
            $table->uuid('delivery_token')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }
}
