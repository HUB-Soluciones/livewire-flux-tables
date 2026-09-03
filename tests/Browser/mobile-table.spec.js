const path = require('path');
const { test, expect } = require('@playwright/test');

test('la tabla móvil muestra búsqueda completa, cards y acciones masivas', async ({ page }) => {
    const consoleErrors = [];
    const pageErrors = [];

    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.goto('/');

    const toolbar = page.locator('[data-flux-table-toolbar]');
    const search = page.locator('[data-flux-table-search]');
    await expect(toolbar).toBeVisible();
    await expect(search).toBeVisible();

    const toolbarBox = await toolbar.boundingBox();
    const searchBox = await search.boundingBox();
    expect(toolbarBox).not.toBeNull();
    expect(searchBox).not.toBeNull();
    expect(Math.abs(searchBox.width - toolbarBox.width)).toBeLessThanOrEqual(1);
    expect(Math.abs(searchBox.x - toolbarBox.x)).toBeLessThanOrEqual(1);

    const firstCard = page.getByRole('article').first();
    const detailsButton = firstCard.locator('button[aria-controls]');
    await expect(firstCard).toBeVisible();
    await detailsButton.click();
    await expect(detailsButton).toHaveAttribute('aria-expanded', 'true');
    await expect(firstCard.getByText('Email', { exact: true })).toBeVisible();
    await detailsButton.click();
    await expect(detailsButton).toHaveAttribute('aria-expanded', 'false');

    await firstCard.getByRole('checkbox').check();
    await expect(page.getByText(/1 usuario (selected on this page|seleccionados en esta página)\./)).toBeVisible();
    await expect(page.getByRole('button', { name: 'Exportar CSV' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Exportar JSON' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Marcar activos' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Marcar inactivos' })).toBeVisible();

    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({
        path: path.resolve('test-results/mobile-table-complete.png'),
        fullPage: true,
        animations: 'disabled',
    });

    const csvDownloadPromise = page.waitForEvent('download');
    await page.getByRole('button', { name: 'Exportar CSV' }).click();
    const csvDownload = await csvDownloadPromise;
    expect(csvDownload.suggestedFilename()).toBe('usuarios-seleccionados.csv');

    const jsonDownloadPromise = page.waitForEvent('download');
    await page.getByRole('button', { name: 'Exportar JSON' }).click();
    const jsonDownload = await jsonDownloadPromise;
    expect(jsonDownload.suggestedFilename()).toBe('usuarios-seleccionados.json');

    await page.getByRole('button', { name: 'Marcar inactivos' }).click();
    await expect(firstCard.getByText('🔴 Inactivo')).toBeVisible();
    await expect(firstCard.getByRole('checkbox')).not.toBeChecked();

    expect(consoleErrors).toEqual([]);
    expect(pageErrors).toEqual([]);
});
