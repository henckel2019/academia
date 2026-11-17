param([int]$Port = 8080)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$postgresDirectory = Join-Path $projectRoot '.local/postgres'
$postgresBin = 'C:/Program Files/PostgreSQL/17/bin'
$phpExecutable = (Get-Command php -ErrorAction Stop).Source
if (!(Test-Path -LiteralPath "$postgresBin/pg_ctl.exe")) {
    throw 'PostgreSQL 17 não encontrado. Instale-o ou ajuste postgresBin neste script.'
}
New-Item -ItemType Directory -Force -Path (Join-Path $projectRoot '.local') | Out-Null
if (!(Test-Path -LiteralPath "$postgresDirectory/PG_VERSION")) {
    & "$postgresBin/initdb.exe" -D $postgresDirectory -U academia --encoding=UTF8 --locale=C --auth-local=trust --auth-host=trust
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao preparar o PostgreSQL local.' }
}
& "$postgresBin/pg_ctl.exe" -D $postgresDirectory status *> $null
if ($LASTEXITCODE -ne 0) {
    & "$postgresBin/pg_ctl.exe" -D $postgresDirectory -l (Join-Path $projectRoot '.local/postgres.log') -o '-p 55432 -h 127.0.0.1' start
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao iniciar o PostgreSQL local.' }
}
$databaseExists = & "$postgresBin/psql.exe" -h 127.0.0.1 -p 55432 -U academia -d postgres -w -Atc "SELECT 1 FROM pg_database WHERE datname='academia_garagem_aco'"
if ($databaseExists -ne '1') {
    & "$postgresBin/createdb.exe" -h 127.0.0.1 -p 55432 -U academia -w academia_garagem_aco
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao criar o banco local.' }
}
# Process-only overrides preserve the remote .env and keep local data separate.
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='55432'
$env:DB_DATABASE='academia_garagem_aco'
$env:DB_USERNAME='academia'
$env:DB_PASSWORD=''
$env:DB_SSLMODE='disable'
$env:APP_ENV='local'
$env:APP_URL="http://localhost:$Port"
$env:ACADEMIA_LOCAL_SETUP='1'
Push-Location $projectRoot
try {
    & $phpExecutable bin/migrate.php
    if ($LASTEXITCODE -ne 0) { throw 'Falha nas migrations.' }
    & $phpExecutable bin/import-schedule.php
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao importar a grade inicial.' }
    & $phpExecutable bin/prepare-local.php
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao preparar o acesso local.' }
    & $phpExecutable bin/gerar-mensalidades.php
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao gerar mensalidades.' }
    & $phpExecutable bin/processar-vencimentos.php
    Write-Output "Academia disponível em http://localhost:$Port/login"
    Write-Output 'Acesso local: .local/acesso-local.txt. Ctrl+C encerra o servidor web.'
    Write-Output 'No celular, a câmera requer um endereço HTTPS válido. Veja README.md.'
    if (!(Test-Path -LiteralPath 'integrations/whatsapp/node_modules/whatsapp-web.js')) {
        $env:PUPPETEER_SKIP_DOWNLOAD='true'
        & npm ci --prefix integrations/whatsapp
        if ($LASTEXITCODE -ne 0) { throw 'Falha ao instalar o serviço WhatsApp.' }
    }
    & node bin/serve-local.mjs $phpExecutable $Port
} finally { Pop-Location }
