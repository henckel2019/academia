async (page) => {
    const results = [];
    for (const route of ['/', '/login'])
        for (const width of [1440, 1024, 768, 390, 320]) {
            await page.setViewportSize({ width, height: 844 });
            const response = await page.goto('http://localhost:8080' + route);
            await page.evaluate(() => document.fonts.ready);
            const metrics = await page.evaluate(() => ({
                overflow: document.documentElement.scrollWidth > innerWidth + 1,
                brokenImages: [...document.images]
                    .filter((i) => i.complete && i.naturalWidth === 0)
                    .map((i) => i.src),
                h1: document.querySelectorAll('h1').length
            }));
            results.push({ route, width, status: response.status(), ...metrics });
            if ([1440, 390].includes(width))
                await page.screenshot({
                    path:
                        'academia/.local/design-review/' +
                        (route === '/' ? 'landing' : 'login') +
                        '-' +
                        width +
                        '.png',
                    fullPage: true
                });
        }
    await page.goto('http://localhost:8080/');
    await page.getByRole('button', { name: 'Abrir menu', exact: true }).click();
    results.push({
        check: 'public mobile menu opens',
        ok: !(await page.locator('#mobile-navigation').getAttribute('hidden'))
    });
    await page
        .getByRole('navigation', { name: 'Navegação no celular' })
        .getByRole('link', { name: 'Horários', exact: true })
        .click();
    results.push({
        check: 'anchor navigation closes public menu',
        ok: await page.locator('#mobile-navigation').isHidden()
    });
    await page.goto('http://localhost:8080/login');
    await page.locator('#senha').fill('test-display-only');
    await page.getByRole('button', { name: 'Mostrar senha' }).click();
    results.push({
        check: 'password visibility toggle',
        ok: (await page.locator('#senha').getAttribute('type')) === 'text'
    });
    await page.getByRole('button', { name: 'Ocultar senha' }).click();
    results.push({
        check: 'password hides again',
        ok: (await page.locator('#senha').getAttribute('type')) === 'password'
    });
    const failures = results.filter(
        (r) =>
            r.ok === false ||
            r.overflow ||
            r.brokenImages?.length ||
            (r.status && r.status !== 200) ||
            (r.h1 && r.h1 !== 1)
    );
    if (failures.length) throw new Error(JSON.stringify(failures));
    return results;
};
