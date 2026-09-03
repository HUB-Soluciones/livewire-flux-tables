<?php

namespace HubSoluciones\LivewireFluxTables\Tests\Feature;

use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTable;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Livewire\UsersTableStriped;
use HubSoluciones\LivewireFluxTables\Tests\Fixtures\Models\FixtureUser;
use HubSoluciones\LivewireFluxTables\Tests\TestCase;
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
            ->assertSeeHtml('class="relative min-w-0 basis-full sm:basis-auto sm:flex-1 sm:max-w-xs"');
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

    public function test_flux_pro_detection_can_be_forced_to_base(): void
    {
        config()->set('livewire-flux-tables.flux_tier', 'base');

        $this->assertFalse(Livewire::test(UsersTable::class)->instance()->usesFluxPro());
    }

    public function test_flux_pro_mode_fails_with_an_actionable_message_when_unavailable(): void
    {
        if (\Flux\Flux::pro()) {
            $this->markTestSkipped('Flux Pro is installed in this test environment.');
        }

        config()->set('livewire-flux-tables.flux_tier', 'pro');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Flux Pro is required');

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();
        $component->usesFluxPro();
    }

    public function test_flux_table_renderer_compiles_when_pro_is_available(): void
    {
        if (! \Flux\Flux::pro()) {
            $this->markTestSkipped('Flux Pro credentials are not available in this test environment.');
        }

        config()->set('livewire-flux-tables.flux_tier', 'auto');

        Livewire::test(UsersTable::class)
            ->assertSeeHtml('data-flux-table');
    }

    public function test_invalid_flux_tier_is_rejected(): void
    {
        config()->set('livewire-flux-tables.flux_tier', 'enterprise');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Flux tier must be auto, base, or pro');

        $component = app(UsersTable::class);
        $component->boot(
            app(QueryPipeline::class),
            app(StickyColumnManager::class),
            app(CellRenderer::class),
        );
        $component->mount();
        $component->usesFluxPro();
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

    public function test_base_renderer_uses_flux_table_and_base_components(): void
    {
        config()->set('livewire-flux-tables.flux_tier', 'base');

        Livewire::test(UsersTable::class)
            ->assertSeeHtml('data-flux-table')
            ->assertSeeHtml('data-flux-button')
            ->assertSeeHtml('data-flux-card');
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
