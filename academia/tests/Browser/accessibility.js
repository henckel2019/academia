async (page) => {
    const findings = [];
    for (const route of [
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
        'configuracoes',
        'usuarios/novo',
        'frequencia/novo'
    ]) {
        await page.goto('http://localhost:8080/' + route);
        await page.addScriptTag({ path: 'academia/.local/audit/node_modules/axe-core/axe.min.js' });
        const report = await page.evaluate(
            async () =>
                await axe.run(document, {
                    runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21aa'] }
                })
        );
        findings.push({
            route,
            violations: report.violations.map((v) => ({
                id: v.id,
                impact: v.impact,
                nodes: v.nodes.map((n) => ({ target: n.target, summary: n.failureSummary }))
            }))
        });
    }
    if (findings.some((f) => f.violations.length))
        throw new Error(JSON.stringify(findings.filter((f) => f.violations.length)));
    return findings;
};
