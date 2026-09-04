[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('web', 'scheduler')]
    [string] $Role,

    [Parameter(Mandatory = $true)]
    [string] $ProjectPath,

    [Parameter(Mandatory = $true)]
    [string] $PhpPath
)

$ErrorActionPreference = 'Stop'

$resolvedProject = (Resolve-Path -LiteralPath $ProjectPath).Path
$resolvedPhp = (Resolve-Path -LiteralPath $PhpPath).Path
$artisan = Join-Path $resolvedProject 'artisan'

if (-not (Test-Path -LiteralPath $artisan -PathType Leaf)) {
    throw "No se encontró artisan en $resolvedProject."
}

$logDirectory = Join-Path $resolvedProject 'storage\logs'
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null

$logPath = Join-Path $logDirectory "lan-$Role.log"
$rotatedLogPath = "$logPath.1"

if ((Test-Path -LiteralPath $logPath) -and (Get-Item -LiteralPath $logPath).Length -ge 10MB) {
    Move-Item -LiteralPath $logPath -Destination $rotatedLogPath -Force
}

$artisanArguments = if ($Role -eq 'web') {
    @($artisan, 'app:lan')
} else {
    @($artisan, 'schedule:work')
}

Set-Location -LiteralPath $resolvedProject
& $resolvedPhp @artisanArguments *>> $logPath
exit $LASTEXITCODE
