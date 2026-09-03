const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/Browser',
    outputDir: './test-results',
    fullyParallel: false,
    retries: 0,
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL: 'http://127.0.0.1:4173',
        locale: 'es-MX',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'mobile-chromium',
            use: {
                ...devices['iPhone 13'],
                browserName: 'chromium',
                viewport: { width: 390, height: 844 },
            },
        },
    ],
    webServer: {
        command: 'php vendor/bin/testbench serve --host=127.0.0.1 --port=4173 --no-reload',
        url: 'http://127.0.0.1:4173',
        reuseExistingServer: true,
        timeout: 120_000,
    },
});
