<?php
declare(strict_types=1);
$published = $landingModalities ?? [];
$disciplines = $published ?: [
    ['nome' => 'Boxe', 'descricao' => 'Movimentação, defesa e precisão a cada golpe.'],
    ['nome' => 'Muay Thai', 'descricao' => 'Oito armas. Técnica para usar cada uma delas.'],
    ['nome' => 'Jiu-Jitsu', 'descricao' => 'Controle, posições e estratégia no tatame.'],
    ['nome' => 'Karatê Shubu-Dô', 'descricao' => 'Fundamentos, concentração e prática constante.'],
];
$photoFor = static function (string $name): string {
    $name = mb_strtolower($name);
    return match (true) {
        str_contains($name, 'karat') => 'academy-karate',
        str_contains($name, 'jiu') => 'academy-jiujitsu',
        str_contains($name, 'muay') => 'academy-muaythai',
        str_contains($name, 'box') => 'academy-boxing',
        default => 'academy-community',
    };
};
$days = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo'];
$weeklySchedule = [];
$scheduleDays = [];
foreach ($published as $modality) {
    foreach (($modality['horarios'] ?? []) as $slot) {
        $start = substr((string) $slot['horario_inicio'], 0, 5);
        $day = (int) $slot['dia_semana'];
        $scheduleDays[$day] = $days[$day];
        $weeklySchedule[$start][$day][] = ['name' => $modality['nome'], 'note' => $slot['observacao'] ?? '', 'end' => $slot['horario_fim'] ?? ''];
    }
}
ksort($weeklySchedule);
ksort($scheduleDays);
$whatsappUrl = (string) app_config('whatsapp_url', '');
$address = (string) app_config('contact_address');
$phone = (string) app_config('contact_phone');
$phoneDigits = preg_replace('/[^0-9]/', '', (string) app_config('contact_phone_digits'));
$mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Garagem Aço, ' . $address);
$hasWhatsapp = preg_match('~^https://(wa\.me|api\.whatsapp\.com)/~', $whatsappUrl) === 1;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-type="place">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#090a0c"><meta name="page-type" content="place"><meta name="site-kind" content="other">
<meta name="description" content="Artes marciais em Santa Felicidade, Curitiba. Conheça a Garagem Aço, as modalidades e a grade de treinos.">
<meta property="og:title" content="Garagem Aço · Artes marciais"><meta property="og:description" content="Conheça a academia em Santa Felicidade, Curitiba.">
<title>Garagem Aço · Artes marciais em Curitiba</title>
<link rel="icon" href="<?= e(asset('/assets/logo-garagem-aco.png')) ?>">
<link rel="preload" href="<?= e(asset('/fonts/barlow-condensed-700.ttf')) ?>" as="font" type="font/ttf" crossorigin>
<link rel="stylesheet" href="<?= e(asset('/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/landing.css')) ?>">
</head>
<body class="landing-page">
<a class="skip-link" href="#inicio">Pular para o conteúdo</a>
<header class="site-header"><div class="wrap site-header__inner">
<a class="site-brand" href="<?= e(url('/')) ?>" aria-label="Garagem Aço — início"><img src="<?= e(asset('/assets/logo-garagem-aco.png')) ?>" alt="" width="62" height="62"><span translate="no">GARAGEM<strong>AÇO</strong></span></a>
<nav class="site-nav" aria-label="Navegação principal"><a href="#modalidades">Modalidades</a><a href="#academia">A academia</a><a href="#horarios">Horários</a><a href="#visita">Onde estamos</a></nav>
<a class="site-login" href="<?= e(url('/login')) ?>"><?= icon('user-round') ?> Área do aluno <?= icon('arrow-right') ?></a>
<button class="site-menu-button" type="button" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobile-navigation"><?= icon('menu') ?></button>
</div></header>
<nav id="mobile-navigation" class="site-mobile-nav" aria-label="Navegação no celular" hidden><a href="#modalidades">Modalidades</a><a href="#academia">A academia</a><a href="#horarios">Horários</a><a href="#visita">Onde estamos</a><a href="<?= e(url('/login')) ?>">Entrar na minha conta <?= icon('arrow-right') ?></a></nav>
<main>
<section class="site-hero" id="inicio" aria-labelledby="hero-title">
<div class="site-hero__photo"><img src="<?= e(asset('/assets/landing/academy-muaythai.webp')) ?>" width="435" height="510" alt="Treino acompanhado de artes marciais na Garagem Aço" fetchpriority="high"></div>
<div class="wrap site-hero__inner"><div class="site-hero__copy"><p class="site-kicker"><span></span> Santa Felicidade · Curitiba</p>
<h1 id="hero-title">ARTES<br>MARCIAIS.<br><span>NA GARAGEM.</span></h1>
<p class="site-hero__lead">Muay Thai, Jiu-Jitsu, No-Gi e Karatê.<br>Seu próximo treino começa aqui.</p>
<a class="site-button" href="#horarios">Consultar horários <?= icon('arrow-right') ?></a>
</div><div class="site-hero__bottom"><span>GARAGEM AÇO <i>/</i> DESDE 2011</span><a href="#modalidades">Encontre sua modalidade <?= icon('arrow-down') ?></a></div></div>
</section>
<section class="site-section disciplines" id="modalidades" aria-labelledby="disciplines-title"><div class="wrap">
<div class="section-heading"><div><span class="site-section-number">01 / O TREINO</span><h2 id="disciplines-title">MODALIDADES<br>PARA O SEU TREINO.</h2></div><p>Da primeira aula ao próximo desafio.<br>Encontre a prática que combina com você.</p></div>
<div class="discipline-grid"><?php foreach ($disciplines as $index => $discipline): ?>
<article class="discipline"><a class="discipline__photo" href="#horarios" aria-label="Consultar horários de <?= e($discipline['nome']) ?>"><img src="<?= e(asset('/assets/landing/' . $photoFor($discipline['nome']) . '.webp')) ?>" width="634" height="510" alt="Treinos e alunos da Garagem Aço" loading="lazy"></a><div class="discipline__body"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($discipline['nome']) ?></h3><p><?= e($discipline['descricao'] ?? 'Conheça os treinos e horários desta modalidade.') ?></p><a href="#horarios" aria-label="Ver a grade de <?= e($discipline['nome']) ?>"><?= icon('arrow-right') ?></a></div></article>
<?php endforeach ?></div></div></section>
<section class="site-section academy" id="academia" aria-labelledby="academy-title"><div class="wrap academy__grid">
<div class="academy__copy"><span class="site-section-number">02 / A ACADEMIA</span><h2 id="academy-title">O MESTRE.<br>A TÉCNICA.<br><span>O SEU TREINO.</span></h2><p>O aprendizado começa nos fundamentos. O mestre acompanha o treino, orienta os movimentos e ajuda cada aluno a avançar na prática.</p><p>A Garagem Aço reúne artes marciais em Santa Felicidade, com espaço para quem está começando e para quem já faz da luta parte da rotina.</p><a class="site-text-link" href="#visita">Conhecer a academia <?= icon('arrow-right') ?></a></div>
<figure class="master-portrait"><img src="<?= e(asset('/assets/landing/academy-master.webp')) ?>" alt="Mestre da Garagem Aço usando quimono branco" width="340" height="510" loading="lazy"></figure>
</div></section>
<section class="site-section schedule" id="horarios" aria-labelledby="schedule-title"><div class="wrap">
<div class="section-heading"><div><span class="site-section-number">03 / SUA ROTINA</span><h2 id="schedule-title">HORÁRIOS<br>DE TREINO.</h2></div><p>Confira os horários publicados pela academia.<br>Escolha o treino que cabe no seu dia.</p></div>
<div class="schedule-board">
<?php if ($weeklySchedule): ?>
<table class="weekly-schedule"><caption>Grade de lutas · horários de início</caption><thead><tr><th scope="col">Horário</th><?php foreach ($scheduleDays as $day => $dayLabel): ?><th scope="col"><?= e($dayLabel . ($day <= 5 ? '-feira' : '')) ?></th><?php endforeach ?></tr></thead><tbody>
<?php foreach ($weeklySchedule as $time => $slots): ?><tr><th scope="row"><?= e($time) ?></th><?php foreach ($scheduleDays as $day => $dayLabel): ?><td class="<?= empty($slots[$day]) ? 'is-empty-slot' : '' ?>"><span class="weekly-day-label" aria-hidden="true"><?= e($dayLabel) ?></span><?php if (empty($slots[$day])): ?><span aria-label="Sem aula">—</span><?php else: ?><?php foreach ($slots[$day] as $lesson): ?><div class="weekly-lesson"><strong><?= e($lesson['name']) ?></strong><?php if ($lesson['note']): ?><small><?= e($lesson['note']) ?></small><?php endif ?><?php if ($lesson['end']): ?><small>Até <?= e(substr((string) $lesson['end'], 0, 5)) ?></small><?php endif ?></div><?php endforeach ?><?php endif ?></td><?php endforeach ?></tr><?php endforeach ?>
</tbody></table>
<?php else: ?><div class="schedule-empty"><span><?= icon('calendar') ?></span><h3>A grade está sendo organizada.</h3><p>Consulte a recepção para confirmar os horários dos treinos.</p><a class="site-text-link" href="#visita">Ver onde estamos <?= icon('arrow-right') ?></a></div><?php endif ?>
</div><p class="schedule-note">Já treina com a gente? <a href="<?= e(url('/login')) ?>">Acesse seu portal <?= icon('arrow-right') ?></a></p>
</div></section>
<section class="site-section visit" id="visita" aria-labelledby="visit-title"><div class="wrap visit__grid"><div><span class="site-section-number">04 / NOS ENCONTRAMOS AQUI</span><h2 id="visit-title">NOS VEMOS<br><span>NO TREINO.</span></h2><div class="visit__contact"><address><?= e($address) ?></address><a class="visit__phone" href="tel:+<?= e($phoneDigits) ?>"><?= e($phone) ?></a><a class="visit__google" href="<?= e(app_config('google_profile_url')) ?>" target="_blank" rel="noopener noreferrer">Ver perfil no Google <?= icon('external') ?></a></div><div class="visit__actions"><a class="site-button" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer">Como chegar <?= icon('arrow-right') ?></a><?php if ($hasWhatsapp): ?><a class="site-text-link" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer">Conversar no WhatsApp <?= icon('arrow-right') ?></a><?php endif ?></div></div><div class="visit__account"><span class="site-kicker">PARA QUEM JÁ FAZ PARTE</span><h3>PORTAL<br>DO ALUNO.</h3><p>Consulte suas modalidades, mensalidades e pagamentos pelo portal do aluno.</p><a class="site-text-link" href="<?= e(url('/login')) ?>">Entrar na minha conta <?= icon('arrow-right') ?></a></div></div></section>
</main>
<footer class="site-footer"><div class="wrap"><div class="site-footer__top"><a class="site-brand" href="#inicio" aria-label="Garagem Aço — voltar ao início"><img src="<?= e(asset('/assets/logo-garagem-aco.png')) ?>" alt="" width="62" height="62" loading="lazy"><span translate="no">GARAGEM<strong>AÇO</strong></span></a><div class="site-footer__contact"><a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e($address) ?></a><a href="tel:+<?= e($phoneDigits) ?>"><?= e($phone) ?></a></div><a href="<?= e(url('/login')) ?>">Acesso da equipe <?= icon('arrow-right') ?></a></div><div class="site-footer__bottom"><span>© <?= date('Y') ?> Garagem Aço</span><span>Muay Thai · Jiu-Jitsu · No-Gi · Karatê Shubu-Dô</span></div></div></footer>
<script defer src="<?= e(asset('/js/landing.js')) ?>"></script>
</body></html>
