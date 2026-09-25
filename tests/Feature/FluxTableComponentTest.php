<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Feature;

use Flux\DateRange;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTable;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTableDefaultFiltersSize;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTableStriped;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;
use HubSoluciones\LivewireFluxTables\Tests\TestCase;
use HubSoluciones\LivewireFluxTables\Filters\TextFilter;
use HubSoluciones\LivewireFluxTables\Livewire\FluxTableComponent;
use HubSoluciones\LivewireFluxTables\Query\QueryPipeline;
use HubSoluciones\LivewireFluxTables\Rendering\CellRenderer;
use HubSoluciones\LivewireFluxTables\Rendering\StickyColumnManager;
use HubSoluciones\LivewireFluxTables\Columns\Column;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class FluxTableComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FixtureUser::query()->create([
            'name' => 'Ana Gomez',
            'email' => 'ana@example.test',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => '2026-01-03 10:00:00',
            'updated_at' => '2026-01-03 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@example.test',
            'role' => 'user',
            'status' => 'inactive',
            'created_at' => '2026-01-15 10:00:00',
            'updated_at' => '2026-01-15 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Maria Diaz',
            'email' => 'maria@example.test',
            'role' => 'admin',
            'status' => 'active',
            'created_at' => '2026-02-10 10:00:00',
            'updated_at' => '2026-02-10 10:00:00',
        ]);

        FixtureUser::query()->create([
            'name' => 'Pedro Leon',
            'email' => 'pedro@example.test',
            'role' => 'user',
            'status' => 'inactive',
            'created_at' => '2026-02-20 10:00:00',
            'updated_at' => '2026-02-20 10:00:00',
        ]);
    }

    public function test_global_search_filters_only_searchable_columns(): void
    {
        Livewire::test(UsersTable::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Diaz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_text_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.name', 'Ana')
            ->assertSee('Ana Gomez')
            ->assertDontSee('Carlos Ruiz');
    }

    public function test_select_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.role', 'admin')
            ->assertSee('Ana Gomez')
            ->assertSee('Maria Diaz')
            ->assertDontSee('Carlos Ruiz');
    }

    public function test_date_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.created_at', '2026-01-15')
            ->assertSee('Carlos Ruiz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_date_range_filter_applies_to_the_query(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableFilters.created_between.from', '2026-02-01')
            ->set('tableFilters.created_between.to', '2026-02-28')
            ->assertSee('Maria Diaz')
            ->assertSee('Pedro Leon')
            ->assertDontSee('Ana Gomez');
    }

    public function test_filters_panel_renders_flux_components(): void
    {
        Livewire::test(UsersTable::class)
            ->set('showFilters', true)
            ->assertSeeHtml('data-flux-field')
            ->assertSeeHtml('data-flux-select')
            ->assertSeeHtml('data-flux-date-picker');
    }

    public function test_date_range_picker_syncs_into_table_filters(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableDateRanges.created_between', new DateRange('2026-02-01', '2026-02-28'))
            ->assertSet('tableFilters.created_between.from', '2026-02-01')
            ->assertSet('tableFilters.created_between.to', '2026-02-28')
            ->assertSee('Maria Diaz')
            ->assertSee('Pedro Leon')
            ->assertDontSee('Ana Gomez');
    }

    public function test_date_range_preset_syncs_into_table_filters(): void
    {
        \Illuminate\Support\Carbon::setTestNow('2026-02-15 12:00:00');

        Livewire::test(UsersTable::class)
            ->set('tableDateRanges.created_between', DateRange::thisMonth())
            ->assertSet('tableFilters.created_between.from', '2026-02-01')
            ->assertSet('tableFilters.created_between.to', '2026-02-28');

        \Illuminate\Support\Carbon::setTestNow();
    }

    /**
     * Regression: over the wire, `flux:date-picker mode="range"` delivers a plain
     * `['start' => ..., 'end' => ..., 'preset' => ...]` array — Livewire has no prior
     * type metadata for a path that started out `null`, so `Flux\DateRangeSynth` isn't
     * applied on the way back in and the property never actually becomes a `DateRange`
     * instance. `dateRangeToFilterState()` must handle that shape directly, not just a
     * hydrated `DateRange` object (reproduced against the live workbench app, where this
     * previously threw a `TypeError` and surfaced as a 500 on every preset selection).
     */
    public function test_date_range_picker_syncs_from_the_raw_wire_payload_shape(): void
    {
        Livewire::test(UsersTable::class)
            ->set('tableDateRanges.created_between', [
                'start' => '2026-02-01',
                'end' => '2026-02-28',
                'preset' => 'thisMonth',
            ])
            ->assertSet('tableFilters.created_between.from', '2026-02-01')
            ->assertSet('tableFilters.created_between.to', '2026-02-28')
            ->assertSee('Maria Diaz')
            ->assertSee('Pedro Leon')
            ->assertDontSee('Ana Gomez');
    }

    public function test_date_range_picker_is_hydrated_from_the_url_on_mount(): void
    {
        $table = Livewire::withQueryParams([
            'created_between' => ['from' => '2026-02-01', 'to' => '2026-02-28'],
        ])->test(UsersTable::class)->instance();

        $range = $table->tableDateRanges['created_between'] ?? null;

        $this->assertInstanceOf(DateRange::class, $range);
        $this->assertSame('2026-02-01', $range->start()?->format('Y-m-d'));
        $this->assertSame('2026-02-28', $range->end()?->format('Y-m-d'));
    }

    public function test_sorting_toggles_between_ascending_and_descending(): void
    {
        Livewire::test(UsersTable::class)
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Ana Gomez', 'Carlos Ruiz'])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['Pedro Leon', 'Maria Diaz']);
    }

    public function test_pagination_moves_between_pages(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSee('Ana Gomez')
            ->assertDontSee('Maria Diaz')
            ->call('gotoPage', 2)
            ->assertSee('Maria Diaz')
            ->assertDontSee('Ana Gomez');
    }

    public function test_query_string_state_is_hydrated(): void
    {
        Livewire::withQueryParams([
            'search' => 'Carlos',
            'role' => 'user',
            'sort' => 'name',
            'direction' => 'desc',
            'perPage' => 25,
        ])->test(UsersTable::class)
            ->assertSet('search', 'Carlos')
            ->assertSet('tableFilters.role', 'user')
            ->assertSet('sort', 'name')
            ->assertSet('direction', 'desc')
            ->assertSet('perPage', 25);
    }

    public function test_all_hideable_columns_are_visible_by_default(): void
    {
        $fields = array_map(
            fn (Column $column) => $column->field(),
            Livewire::test(UsersTable::class)->instance()->visibleColumns,
        );

        $this->assertSame(['id', 'name', 'email', 'role', 'status', 'created_at'], $fields);
    }

    public function test_unchecking_a_column_hides_it_from_the_table(): void
    {
        $component = Livewire::test(UsersTable::class)
            ->set('visibleColumnFields', ['id', 'name', 'role', 'status', 'created_at']);

        $fields = array_map(fn (Column $column) => $column->field(), $component->instance()->visibleColumns);

        $this->assertNotContains('email', $fields);
    }

    public function test_column_visibility_choice_is_remembered_across_a_fresh_mount(): void
    {
        Livewire::test(UsersTable::class)
            ->set('visibleColumnFields', ['id', 'name', 'role', 'status', 'created_at']);

        Livewire::test(UsersTable::class)->assertSet(
            'visibleColumnFields',
            ['id', 'name', 'role', 'status', 'created_at'],
        );
    }

    public function test_custom_cell_views_render_inside_table_cells(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSee('Estado: active');
    }

    public function test_sticky_columns_generate_metadata(): void
    {
        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $metadata = $component->stickyMetadata();

        $this->assertStringContainsString('position: sticky', $metadata[1]['style']);
        $this->assertStringContainsString('left: 0px', $metadata[1]['style']);
    }

    public function test_zebra_striping_is_disabled_by_default(): void
    {
        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertFalse($component->isStriped());
    }

    public function test_zebra_striping_can_be_enabled_via_config(): void
    {
        config()->set('livewire-flux-tables.zebra_striping', true);

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertTrue($component->isStriped());
    }

    public function test_zebra_striping_can_be_overridden_per_component(): void
    {
        config()->set('livewire-flux-tables.zebra_striping', false);

        $component = app(UsersTableStriped::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertTrue($component->isStriped());
    }

    public function test_row_background_class_alternates_when_striped(): void
    {
        config()->set('livewire-flux-tables.zebra_striping', true);
        config()->set('livewire-flux-tables.zebra_odd_class', 'bg-white dark:bg-zinc-900');
        config()->set('livewire-flux-tables.zebra_even_class', 'bg-zinc-50 dark:bg-zinc-800/40');

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertSame('bg-white dark:bg-zinc-900', $component->rowBackgroundClass(1, false));
        $this->assertSame('bg-zinc-50 dark:bg-zinc-800/40', $component->rowBackgroundClass(2, false));
        $this->assertSame('bg-white dark:bg-zinc-900', $component->rowBackgroundClass(3, false));
        $this->assertSame('bg-zinc-50 dark:bg-zinc-800/40', $component->rowBackgroundClass(4, false));
    }

    public function test_selected_row_class_overrides_zebra(): void
    {
        config()->set('livewire-flux-tables.zebra_striping', true);

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertSame('bg-blue-50/50 dark:bg-blue-900/20', $component->rowBackgroundClass(1, true));
        $this->assertSame('bg-blue-50/50 dark:bg-blue-900/20', $component->rowBackgroundClass(2, true));
    }

    public function test_row_background_class_uses_base_class_when_not_striped(): void
    {
        config()->set('livewire-flux-tables.zebra_striping', false);
        config()->set('livewire-flux-tables.row_base_class', 'bg-white dark:bg-zinc-900');

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertSame('bg-white dark:bg-zinc-900', $component->rowBackgroundClass(1, false));
        $this->assertSame('bg-white dark:bg-zinc-900', $component->rowBackgroundClass(2, false));
    }

    public function test_sticky_cells_no_longer_have_hardcoded_bg_white(): void
    {
        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $metadata = $component->stickyMetadata();

        $this->assertStringNotContainsString('bg-white', $metadata[1]['class']);
        $this->assertTrue($metadata[1]['is_sticky']);
    }

    public function test_mobile_summary_and_details_are_resolved_without_duplicates(): void
    {
        $component = Livewire::test(UsersTable::class)->instance();

        $summary = $component->mobileSummaryColumns();
        $details = $component->mobileDetailColumns();

        $this->assertSame(['name'], array_map(fn (Column $column) => $column->field(), $summary));
        $this->assertSame(['id', 'email', 'role', 'created_at'], array_map(fn (Column $column) => $column->field(), $details));
    }

    public function test_mobile_cards_render_a_valid_alpine_row_key_expression(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSeeHtml('x-data="{ rowKey: \'1\' }"')
            ->assertDontSeeHtml("x-data='{ rowKey:")
            ->assertSeeHtml('x-show="expanded ===');
    }

    public function test_mobile_cards_safely_encode_special_characters_in_row_keys(): void
    {
        Livewire::test(MobileCardsWithSpecialRowKey::class)
            ->assertSeeHtml('x-data="{ rowKey: \'O\\u0027Reilly \\u0026 \\u0022quoted\\u0022\' }"');
    }

    public function test_search_uses_the_full_mobile_toolbar_width(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSeeHtml('class="min-w-0 basis-full sm:basis-auto sm:flex-1 sm:max-w-xs"');
    }

    public function test_mobile_sort_actions_validate_declared_sortable_fields(): void
    {
        $component = Livewire::test(UsersTable::class)
            ->call('setSortField', 'email')
            ->assertSet('sort', 'email');

        $component->call('toggleSortDirection')->assertSet('direction', 'desc');
        $component->call('setSortField', 'not_a_column')->assertSet('sort', 'email');
        $component->call('setSortField', null)->assertSet('sort', null);
    }

    public function test_multiple_sticky_columns_accumulate_offsets_on_both_sides(): void
    {
        $manager = app(StickyColumnManager::class);
        $metadata = $manager->map([
            Column::make('A', 'a')->sticky('left')->width('4rem'),
            Column::make('B', 'b')->sticky('left')->width('6rem'),
            Column::make('C', 'c'),
            Column::make('D', 'd')->sticky('right')->width('5rem'),
            Column::make('E', 'e')->sticky('right')->width('7rem'),
        ]);

        $this->assertStringContainsString('left: 0px', $metadata[0]['style']);
        $this->assertStringContainsString('left: calc(0px + 4rem)', $metadata[1]['style']);
        $this->assertStringContainsString('right: 0px', $metadata[4]['style']);
        $this->assertStringContainsString('right: calc(0px + 7rem)', $metadata[3]['style']);
        $this->assertSame('left', $metadata[1]['sticky_edge']);
        $this->assertSame('right', $metadata[3]['sticky_edge']);
    }

    public function test_sticky_rejects_unknown_positions(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Column::make('Invalid')->sticky('top');
    }

    public function test_flux_table_renderer_compiles(): void
    {
        Livewire::test(UsersTable::class)
            ->assertSeeHtml('data-flux-table');
    }

    public function test_paginator_query_is_reused_throughout_a_render(): void
    {
        $queries = [];

        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            if (str_contains($query->sql, 'fixture_users')) {
                $queries[] = $query->sql;
            }
        });

        Livewire::test(UsersTable::class)->assertSee('Ana Gomez');

        $this->assertCount(2, $queries, 'A render should execute one count query and one page query.');
    }

    public function test_lazy_placeholder_is_accessible(): void
    {
        $component = Livewire::test(UsersTable::class)->instance();
        $html = $component->placeholder()->render();

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Loading records', $html);
    }

    public function test_filter_width_defaults_to_config_value(): void
    {
        config()->set('livewire-flux-tables.filter_default_width', 'md');

        $filter = TextFilter::make('Nombre', 'name');

        $this->assertSame('md', $filter->widthValue());
    }

    public function test_filter_width_can_be_overridden_per_filter(): void
    {
        $filter = TextFilter::make('Nombre', 'name')->width('lg');

        $this->assertSame('lg', $filter->widthValue());
    }

    public function test_filter_width_rejects_invalid_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TextFilter::make('Nombre', 'name')->width('huge');
    }

    public function test_filter_width_shortcuts_set_the_expected_value(): void
    {
        $this->assertSame('sm', TextFilter::make('Nombre', 'name')->small()->widthValue());
        $this->assertSame('md', TextFilter::make('Nombre', 'name')->medium()->widthValue());
        $this->assertSame('lg', TextFilter::make('Nombre', 'name')->large()->widthValue());
        $this->assertSame('full', TextFilter::make('Nombre', 'name')->fullWidth()->widthValue());
    }

    public function test_filters_panel_renders_configured_widths(): void
    {
        Livewire::test(UsersTable::class)
            ->set('showFilters', true)
            ->assertSeeHtml('xl:col-span-2') // 'sm' width on the Nombre filter
            ->assertSeeHtml('col-span-full'); // 'full' width on the Periodo filter
    }

    public function test_filters_size_defaults_to_compact(): void
    {
        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertSame('sm', $component->filtersSize());
    }

    public function test_filters_size_can_be_overridden_per_component(): void
    {
        $component = app(UsersTableDefaultFiltersSize::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();

        $this->assertNull($component->filtersSize());
    }

}

class MobileCardsWithSpecialRowKey extends FluxTableComponent
{
    public function records(): array
    {
        return [[
            'key' => 'O\'Reilly & "quoted"',
            'name' => 'Special key',
            'email' => 'special@example.test',
        ]];
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')->mobileSummary(),
            Column::make('Email', 'email'),
        ];
    }

    protected function rowKeyField(): string
    {
        return 'key';
    }
}
