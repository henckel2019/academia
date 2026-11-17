async (page) => {
    const pages = [
        'dashboard',
        'alunos',
        'modalidades',
        'turmas',
        'graduacoes',
        'professores',
        'frequencia',
        'financeiro',
        'checkin',
        'biometria',
        'promocoes',
        'usuarios',
        'configuracoes'
    ];
    const results = [];
    for (const width of [1440, 1024, 768, 390, 320]) {
        await page.setViewportSize({ width, height: 900 });
        for (const route of pages) {
            const response = await page.goto('http://localhost:8080/' + route);
            await page.evaluate(() => document.fonts.ready);
            const metrics = await page.evaluate(() => ({
                overflow: document.documentElement.scrollWidth > innerWidth + 1,
                headings: document.querySelectorAll('h1').length,
                active: document.querySelector('.nav-link.is-active')?.textContent.trim(),
                brokenImages: [...document.images]
                    .filter((i) => !i.complete || i.naturalWidth === 0)
                    .map((i) => i.src),
                blankButtons: [...document.querySelectorAll('button')].filter(
                    (b) => !b.textContent.trim() && !b.getAttribute('aria-label')
                ).length
            }));
            results.push({ width, route, status: response.status(), ...metrics });
            if (
                [1440, 390].includes(width) &&
                ['dashboard', 'alunos', 'financeiro', 'checkin', 'biometria'].includes(route)
            )
                await page.screenshot({
                    path: 'academia/.local/design-review/' + route + '-' + width + '.png',
                    fullPage: true
                });
        }
    }
    const failures = results.filter(
        (r) =>
            r.status !== 200 ||
            r.overflow ||
            r.headings !== 1 ||
            r.brokenImages.length ||
            r.blankButtons
    );
    if (failures.length) throw new Error(JSON.stringify(failures));
    return {
        total: results.length,
        failures,
        routes: results
            .filter((r) => r.width === 1440)
            .map((r) => ({ route: r.route, active: r.active }))
    };
};
