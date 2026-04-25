<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Feature;

use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTable;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTableWithSelection;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;
use HubSoluciones\LivewireFluxTables\Tests\TestCase;
use Livewire\Livewire;

class SelectionColumnTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FixtureUser::query()->insert([
            ['name' => 'Ana Gomez', 'email' => 'ana@example.test', 'role' => 'admin', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carlos Ruiz', 'email' => 'carlos@example.test', 'role' => 'user', 'status' => 'inactive', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maria Lopez', 'email' => 'maria@example.test', 'role' => 'admin', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pedro Sanz', 'email' => 'pedro@example.test', 'role' => 'user', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_table_without_selection_column_has_no_selection_state(): void
    {
        $component = Livewire::test(UsersTable::class);

        $component->assertSet('selectedKeys', []);
        $component->assertSet('selectAllRecords', false);

        $instance = $component->instance();
        $this->assertFalse($instance->hasSelection());
        $this->assertNull($instance->selectionColumn());
    }

    public function test_selection_column_activates_has_selection(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();

        $this->assertTrue($instance->hasSelection());
        $this->assertNotNull($instance->selectionColumn());
    }

    public function test_toggle_row_adds_key(): void
    {
        Livewire::test(UsersTableWithSelection::class)
            ->call('toggleRow', '1')
            ->assertSet('selectedKeys', ['1']);
    }

    public function test_toggle_row_removes_key_when_already_selected(): void
    {
        Livewire::test(UsersTableWithSelection::class)
            ->call('toggleRow', '1')
            ->call('toggleRow', '1')
            ->assertSet('selectedKeys', []);
    }

    public function test_toggle_page_selection_selects_all_keys_on_page(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('togglePageSelection');

        $selected = $component->get('selectedKeys');
        $this->assertCount(2, $selected);
    }

    public function test_toggle_page_selection_deselects_when_fully_selected(): void
    {
        Livewire::test(UsersTableWithSelection::class)
            ->call('togglePageSelection')
            ->call('togglePageSelection')
            ->assertSet('selectedKeys', []);
    }

    public function test_enable_select_all_records_sets_flag(): void
    {
        Livewire::test(UsersTableWithSelection::class)
            ->call('enableSelectAllRecords')
            ->assertSet('selectAllRecords', true);
    }

    public function test_clear_selection_resets_all_state(): void
    {
        Livewire::test(UsersTableWithSelection::class)
            ->call('togglePageSelection')
            ->call('enableSelectAllRecords')
            ->call('clearSelection')
            ->assertSet('selectedKeys', [])
            ->assertSet('selectAllRecords', false);
    }

    public function test_page_selection_state_is_none_initially(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();

        $this->assertSame('none', $instance->pageSelectionState());
    }

    public function test_page_selection_state_is_partial_when_one_selected(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('toggleRow', '1');

        $this->assertSame('partial', $component->instance()->pageSelectionState());
    }

    public function test_page_selection_state_is_full_when_all_page_selected(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('togglePageSelection');

        $this->assertSame('full', $component->instance()->pageSelectionState());
    }

    public function test_page_selection_state_is_full_when_select_all_records_true(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('enableSelectAllRecords');

        $this->assertSame('full', $component->instance()->pageSelectionState());
    }

    public function test_all_filtered_keys_returns_all_ids_across_pages(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();

        $keys = $instance->allFilteredKeys();

        $this->assertCount(4, $keys);
    }

    public function test_all_filtered_keys_respects_active_search_filter(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->set('search', 'Ana');

        $keys = $component->instance()->allFilteredKeys();

        $this->assertCount(1, $keys);
    }

    public function test_bulk_action_uses_all_filtered_keys_when_select_all_true(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('enableSelectAllRecords')
            ->call('markInactive');

        $markedInactive = $component->get('markedInactive');
        $this->assertCount(4, $markedInactive);

        $component->assertSet('selectedKeys', []);
        $component->assertSet('selectAllRecords', false);
    }

    public function test_bulk_action_uses_only_selected_keys_when_select_all_false(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('toggleRow', '1')
            ->call('markInactive');

        $markedInactive = $component->get('markedInactive');
        $this->assertCount(1, $markedInactive);
        $this->assertContains('1', $markedInactive);
    }

    public function test_selection_column_is_sticky_left_by_default(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();
        $selectionColumn = $instance->selectionColumn();

        $this->assertNotNull($selectionColumn);
        $this->assertTrue($selectionColumn->isSticky());
        $this->assertSame('left', $selectionColumn->stickyPosition());
    }

    public function test_selection_column_has_selection_column_flag(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();
        $selectionColumn = $instance->selectionColumn();

        $this->assertTrue($selectionColumn->isSelectionColumn());
    }

    public function test_regular_column_does_not_have_selection_column_flag(): void
    {
        $instance = Livewire::test(UsersTable::class)->instance();

        foreach ($instance->resolvedColumns() as $column) {
            $this->assertFalse($column->isSelectionColumn());
        }
    }

    public function test_is_row_selected_returns_correct_state(): void
    {
        $component = Livewire::test(UsersTableWithSelection::class)
            ->call('toggleRow', '1');

        $instance = $component->instance();
        $rows = $instance->rows()->items();

        $firstRow = $rows[0];
        $secondRow = $rows[1];

        $this->assertTrue($instance->isRowSelected($firstRow));
        $this->assertFalse($instance->isRowSelected($secondRow));
    }

    public function test_selection_column_not_hideable(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();
        $selectionColumn = $instance->selectionColumn();

        $this->assertFalse($selectionColumn->isHideable());
    }

    public function test_selection_column_bulk_actions_are_accessible(): void
    {
        $instance = Livewire::test(UsersTableWithSelection::class)->instance();
        $bulkActions = $instance->selectionColumn()->getBulkActions();

        $this->assertArrayHasKey('markInactive', $bulkActions);
        $this->assertSame('Marcar inactivos', $bulkActions['markInactive']);
    }
}
