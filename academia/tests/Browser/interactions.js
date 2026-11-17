async (page) => {
    const checks = [];
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://localhost:8080/alunos');
    await page.getByRole('button', { name: 'Abrir menu', exact: true }).click();
    checks.push({
        check: 'mobile menu expanded',
        ok:
            (await page
                .getByRole('button', { name: 'Abrir menu', exact: true })
                .getAttribute('aria-expanded')) === 'true'
    });
    await page.keyboard.press('Escape');
    checks.push({
        check: 'Escape closes menu',
        ok:
            (await page
                .getByRole('button', { name: 'Abrir menu', exact: true })
                .getAttribute('aria-expanded')) === 'false'
    });
    await page.getByRole('link', { name: 'Novo aluno', exact: true }).click();
    checks.push({ check: 'student dialog opens', ok: await page.getByRole('dialog').isVisible() });
    await page.getByRole('button', { name: 'Adicionar modalidade', exact: true }).click();
    checks.push({
        check: 'enrollment row added',
        ok: (await page.locator('[data-enrollment-row]').count()) === 2
    });
    await page.screenshot({
        path: 'academia/.local/design-review/student-form-mobile.png',
        fullPage: true
    });
    await page.keyboard.press('Escape');
    checks.push({
        check: 'dialog closes with Escape',
        ok: !(await page.getByRole('dialog').isVisible())
    });
    await page.goto('http://localhost:8080/checkin');
    await page.getByRole('button', { name: 'Iniciar presença facial', exact: true }).click();
    checks.push({
        check: 'missing facial enrollment gets guidance',
        ok: (await page.locator('[data-face-status]').textContent()).includes('Nenhuma facial')
    });
    for (const route of [
        'modalidades/novo',
        'turmas/novo',
        'graduacoes/nova',
        'professores/novo',
        'frequencia/novo',
        'promocoes/nova',
        'usuarios/novo'
    ]) {
        const response = await page.goto('http://localhost:8080/' + route);
        checks.push({
            check: 'form ' + route,
            ok: response.status() === 200 && (await page.locator('form').count()) > 0
        });
    }
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('http://localhost:8080/dashboard');
    checks.push({
        check: 'reduced motion renders dashboard',
        ok: await page.locator('h1').isVisible()
    });
    await page.setViewportSize({ width: 844, height: 390 });
    await page.goto('http://localhost:8080/checkin');
    checks.push({
        check: 'landscape fits viewport',
        ok: await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)
    });
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('http://localhost:8080/dashboard');
    await page.screenshot({
        path: 'academia/.local/design-review/dashboard-desktop-final.png',
        fullPage: true
    });
    await page.goto('http://localhost:8080/checkin');
    await page.screenshot({
        path: 'academia/.local/design-review/checkin-desktop-final.png',
        fullPage: true
    });
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://localhost:8080/dashboard');
    await page.screenshot({
        path: 'academia/.local/design-review/dashboard-mobile-final.png',
        fullPage: true
    });
    if (checks.some((check) => !check.ok))
        throw new Error(JSON.stringify(checks.filter((check) => !check.ok)));
    return checks;
};
