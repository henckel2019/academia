GATE: OPEN 31 — três rodadas concluídas; não aprovado integralmente pelo checker Taste.

# Revisão do redesign — 09/11/2026

O front foi refeito na stack PHP existente. Mantém a paleta preta/grafite/vermelha e a logo original. Fontes Barlow e Barlow Condensed locais, navegação lateral por perfil, menus com foco de teclado, tabelas roláveis acessíveis, formulários, portal do aluno, financeiro, cadastro facial e check-in compartilham os mesmos controles e tokens.

## Evidências verificadas

- 13 telas internas em cinco larguras (1440, 1024, 768, 390 e 320): 65 combinações, HTTP 200, uma h1 por página, sem imagens quebradas, botões sem nome ou transbordamento horizontal da página.
- Página pública e login nas mesmas cinco larguras: 10 combinações sem falhas de carregamento ou transbordamento.
- 15 verificações de interação: menus, Escape, janela de cadastro, inclusão de modalidade, abertura dos formulários, orientação facial sem cadastro, redução de movimento e paisagem.
- axe-core com WCAG 2 A/AA e 2.1 AA: sem violações detectadas nas 15 páginas internas examinadas e na página pública. Isso não equivale a uma certificação de acessibilidade.
- Portal revisado em 1440, 768, 390 e 320, com conta e registros temporários de teste removidos depois das capturas.
- 27 testes PHPUnit (82 assertions), 19 verificações PostgreSQL e 3 testes de comparação facial passaram. Sintaxe PHP e JavaScript conferida.
- Grade conferida no DOM: 14 aulas, segunda a quinta. Início sem horário final inventado. Muay Thai na terça às 20h preserva Fundamentos de Boxe. Importação repetida não altera a grade existente.
- Telefone, WhatsApp e mapa conferidos nos hrefs e nas capturas. Fonte: endereço e telefone fornecidos pelo proprietário nesta conversa; perfil Google fornecido: https://share.google/UbT0f8XJaZL74IiiI.
- Logo SHA-256 antes/depois: `0AE8B5F2FD479D24CC30C559648208DC6F3DF20F7736D16ACFA31786C0F4FF3F`.

## Material e decisões

As seis fotos `public/assets/landing/academy-*.webp` foram copiadas da pasta `C:/Users/fukud/Pictures/aço`, sem gerar retratos ou atribuir qualificações ao mestre. Não há imagem editorial de terceiros no redesign final. O mestre corresponde a `unnamed (5).webp`; a grade foi transcrita de `unnamed (6).webp`. Não foram inventados horários de funcionamento, duração das aulas, depoimentos ou números de alunos.

A imagem de treino do hero tem resolução original de 435×510 e aparece ampliada no desktop: sua nitidez é limitada. O retrato do mestre mantém largura máxima de 340px. A logo PNG original tem aproximadamente 2,54 MiB e foi preservada sem compressão por solicitação do usuário. O desempenho real da câmera no celular antigo, HTTPS de produção e conexão Supabase continuam dependendo de validação/implantação fora desta revisão. A revisão visual usa Chrome no computador, não um celular físico.

## Skills e auditoria

UI/UX Pro Max e Web Design Guidelines já estavam instaladas. Taste Skill de Leonxlnx e Playwright CLI foram instalados sequencialmente e lidos nesta sessão. O pacote `@playwright/cli` foi instalado globalmente. As novas skills ficam disponíveis nas próximas conversas. A Web Design Guidelines foi consultada na fonte da Vercel e aplicada aos arquivos alterados.

O gate Taste não passou integralmente. Seus critérios rígidos de composição, telas com altura fixa, imagens em todas as seções e momentos de animação continuam gerando os apontamentos abaixo. Alguns divergem do conteúdo operacional completo e do uso das fotos fornecidas; não foram escondidos nem declarados aprovados. Os testes de navegador e acessibilidade são verificações distintas do gate estético. A terceira rodada encerrou as iterações conforme a própria skill.

## Exceções registradas no checker

- `phone-number` / `visit__phone`: Número comercial fornecido explicitamente pelo proprietário: (41) 99921-3112. O briefing autoriza publicar e criar o link tel.
- `copy-headings` / `disciplines-title`: O conteúdo está em português. Modalidades, horários de treino, mestre e Boxe nomeiam a oferta da academia; o dicionário inglês do checker não reconhece esses substantivos.

## Apontamentos abertos da última rodada

1. **nav** — `header.site-header > div.wrap.site-header__inner > a.site-brand "GARAGEM AÇO"`

   nav link wraps to a second line

2. **no-visual** — `section#horarios "HORÁRIOS DE TREINO."`

   no picture, effect or mock in this section; a feature section needs a visual

3. **no-visual** — `section#visita "NOS VEMOS NO TREINO."`

   no picture, effect or mock in this section; a feature section needs a visual

5. **heading-scale** — `section#visita > div.wrap.visit__grid > div.visit__account > h3 "PORTAL DO ALUNO."`

   h3 headings in 2 sizes, 30px ("JIU-JITSU") to 48px (x1.60)

6. **photo-upscale** — `section#inicio > div.site-hero__photo > img "img: Treino acompanhado de artes marciai"`

   academy-muaythai.webp: 435x510 shown at 598x646, blown up 1.38x

8. **moment-none** — `(the page)`

   a place page (--type) deals no moment: every kind carries one, two on a long page (moments.md)

9. **section-height** — `footer.site-footer "GARAGEMAÇORua Alexandre Toscani Filho, 1"`

   the section is 240px tall (0.3 screens), 660px short of one screen (900px): every section after the hero is one screen or two, 2px either way (SR-2)

10. **section-height** — `section#horarios "03 / SUA ROTINAHORÁRIOSDE TREINO.Confira"`

   the section is 955px tall (1.1 screens), 55px over one screen (900px): every section after the hero is one screen or two, 2px either way (SR-2)

11. **section-height** — `section#visita "04 / NOS ENCONTRAMOS AQUINOS VEMOSNO TRE"`

   the section starts at 3655px, 55px below the screen grid (4 x 900px): sections start on whole screens (SR-2)

12. **section-air** — `section#horarios > div.wrap > div.schedule-board "Grade de lutas · horários de inícioHorár"`

   a top border across 100% of the container, between the section's content: sections and their parts are told apart by air and ground, not lines (SR-3)

13. **one-section** — `footer.site-footer "GARAGEMAÇORua Alexandre Toscani Filho, 1"`

   the screen at scroll 3895px (the top of section 6) shows 240px of section 6 and the content of 2 sections, over 24px each beside section 5: a screen shows one section (another may peek 8px, 24px WARN) (SR-4)

14. **one-section** — `section#horarios "03 / SUA ROTINAHORÁRIOSDE TREINO.Confira"`

   the screen at scroll 3600px (stride 4) shows 55px of section 4 beside section 5: a screen shows one section (another may peek 8px, 24px WARN) (SR-4)

15. **container-edges** — `a.skip-link "Pular para o conteúdo"`

   a <a> at the top of the page, outside header.nav, main, footer.closing and nav.actionbar (SR-6)

16. **container-edges** — `header.site-header "GARAGEMAÇO ModalidadesA academiaHorários"`

   a <header> at the top of the page, outside header.nav, main, footer.closing and nav.actionbar (SR-6)

17. **container-edges** — `footer.site-footer "GARAGEMAÇORua Alexandre Toscani Filho, 1"`

   a <footer> at the top of the page, outside header.nav, main, footer.closing and nav.actionbar (and 3 more like it: section#inicio > div.site-hero__photo, "Santa Felicidade · Curit", "Santa Felicidade · Curit") (SR-6)

18. **container-edges** — `section#horarios > div.wrap > p.schedule-note > a "Acesse seu portal"`

   1 text block starts on a left line at x 213, 149px from the container's left: text starts on the container's left, or on one split line (x 621, 732, 843) (SR-6)

19. **split-pair** — `section#visita > div.wrap.visit__grid "04 / NOS ENCONTRAMOS AQUINOS VEMOSNO TRE"`

   the split's columns are off the page's one pair of lines: its right column starts at x 950, the first split's at 890; its right column starts at x 950, on none of the split lines 621 (k=5), 732 (k=6), 843 (k=7) (SR-8)

20. **split-pair** — `section#academia > div.wrap.academy__grid "02 / A ACADEMIAO MESTRE.A TÉCNICA.O SEU "`

   the split's columns are off the page's one pair of lines: its right column starts at x 890, on none of the split lines 621 (k=5), 732 (k=6), 843 (k=7) (SR-8)

21. **spill** — `section#inicio > div.wrap.site-hero__inner > div.site-hero__copy > a.site-button "Consultar horários"`

   runs 78px past the left edge of the panel it sits on (section#inicio > div.site-hero__photo)

22. **card-visual** — `div.wrap > div.schedule-board > table.weekly-schedule > tbody "16:00 Segunda Karatê Shubu-Dô Quarta Kar"`

   4 cards hold only text: no picture, icon, UI fragment or effect in any of them

23. **off-grid** — `section#academia > div.wrap.academy__grid > figure.master-portrait > img "img: Mestre da Garagem Aço usando quimon"`

   left edge at 25px, 5px off the page margin at 20px (51 elements, like div)

24. **right-edge** — `section#inicio > div.site-hero__photo`

   its right edge runs 20px past the content's right edge (370px, where 6 boxes end)

25. **edge-left** — `#modalidades p "Da primeira aula ao próximo desa"`

   starts at x 1082.6; the lines are 64 and the column starts

26. **edge-left** — `#academia figure.master-portrait`

   starts at x 890.5; the lines are 64 and the column starts

27. **edge-right** — `#academia figure.master-portrait`

   ends at x 1230.5; the right line is 1376

28. **edge-left** — `#horarios p "Confira os horários publicados p"`

   starts at x 1058.8; the lines are 64 and the column starts

29. **edge-left** — `#visita div.visit__account "PARA QUEM JÁ FAZ PARTEPORTALDO A"`

   starts at x 950.4; the lines are 64 and the column starts

30. **edge-left** — `#visita h3 "PORTALDO ALUNO."`

   starts at x 999.4; the lines are 64 and the column starts

31. **edge-left** — `#visita p "Consulte suas modalidades, mensa"`

   starts at x 999.4; the lines are 64 and the column starts

32. **one-sided** — `#academia section.site-section.academy "02 / A ACADEMIAO MESTRE.A TÉCNIC"`

   its visuals end at x 1230.5, short of the right line 1376

33. **screen-height** — `#modalidades section.site-section.disciplines "01 / O TREINOMODALIDADESPARA O S"`

   2047px, 2.43 screens; a section is exactly one or two (--h), or on a phone at least one that its content fills

## Avisos mantidos

- `caption` — div.wrap > div.discipline-grid > article.discipline:nth-of-type(1) > div.discipline__body "01 JIU-JITSU Controle, posições e estrat": 4 lines of words sit right under a picture, like "01 JIU-JITSU Controle, posições e estrat": the section's text says it, or the picture speaks for itself
- `no-visual` — section#academia "O MESTRE. A TÉCNICA. O SEU TREINO.": pictures, effects and mocks cover 23% of this section; a feature section needs a visual (about 25% or more)
- `copy-buttons` — header.site-header > div.wrap.site-header__inner > a.site-login "Área do aluno": "Área do aluno" does not start with a verb (book, buy, try, start, get, see...) (and 2 more like it: "Consultar horários", "Como chegar")
- `copy-actions` — (the page): the main action "Consultar horários" does not read word for word the same in the closing section
- `col-edge` — section#academia > div.wrap.academy__grid > figure.master-portrait: the second column starts at 4 places: 890px (section#academia), 950px (section#visita), 1059px (section#horarios), 1083px (section#modalidades)
- `section-gaps` — section#inicio / section#modalidades: 151px between these sections, -47% against the median 286px
- `section-gaps` — section#academia / section#horarios: 378px between these sections, +32% against the median 286px
- `photo-set` — div.discipline-grid > article.discipline:nth-of-type(4) > a.discipline__photo > img "academy-community.webp": 6 photographs in more than one look: lightness from 0.21 to 0.58; saturation from 0.05 to 0.46; warm and cool photographs mixed (b* -18 to +17); the odd one out is academy-community.webp
- `nav-labels` — header.site-header > div.wrap.site-header__inner > nav.site-nav > a:nth-of-type(2) "A academia": "A academia" opens a section headed "O MESTRE. A TÉCNICA. O SEU TREINO."
- `nav-labels` — header.site-header > div.wrap.site-header__inner > nav.site-nav > a:nth-of-type(4) "Onde estamos": "Onde estamos" opens a section headed "NOS VEMOS NO TREINO."
- `photo-repeat` — div.discipline-grid > article.discipline:nth-of-type(3) > a.discipline__photo > img "academy-muaythai.webp": the photograph academy-muaythai.webp is shown 2 times on the page: a picture the visitor has already seen
- `skip-link` — a.skip-link "Pular para o conteúdo": the first Tab stop is "Pular para o conteúdo", not a skip link: a keyboard user tabs through the whole nav on every visit
- `share-meta` — head: the head lacks og:image, twitter:card: a shared link shows no picture or title of its own
- `section-air` — section#visita "04 / NOS ENCONTRAMOS AQUINOS VEMOSNO TRE": the content fills 53.7% of the section's height (484 of 900px): 55% or more (40% WARN) (SR-3)
- `tap-size` — div.wrap > div.site-footer__top > div.site-footer__contact > a:nth-of-type(2) "(41) 99921-3112": a 350x30px tap target: under the 44px a phone needs
- `contrast` — h1#hero-title > span "NA GARAGEM.": large text over a picture reads at 2.7:1 at its worst tenth (needs 3:1)
- `visual-caption` — section#inicio > div.wrap.site-hero__inner > div.site-hero__bottom > span "GARAGEM AÇO / DESDE 2011": a muted 10px line of 1 row sits 14px under the picture academy-muaythai.webp: it reads as a caption: "GARAGEM AÇO / DESDE 2011"; no caption or description under any image, render or visual (house rule)

## Reproduzir a revisão

Os scripts em `tests/Browser/` usam Playwright CLI e lançam erro se suas verificações falharem. Execute `responsive.js`, `interactions.js` e `accessibility.js` em uma sessão da equipe; `public.js` em sessão sem login. As capturas e dependências da auditoria ficam em `.local/design-review/` e `.local/audit/`, fora do Git. O relatório do gate fica em `.taste-skill/`, também fora do Git.
