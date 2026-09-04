[CmdletBinding()]
param(
    [string] $ProjectPath,
    [string] $PhpPath
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($ProjectPath)) {
    $ProjectPath = Join-Path $PSScriptRoot '..\..'
}

if ([string]::IsNullOrWhiteSpace($PhpPath)) {
    $PhpPath = (Get-Command php -ErrorAction Stop).Source
}

$resolvedProject = (Resolve-Path -LiteralPath $ProjectPath).Path
$resolvedPhp = (Resolve-Path -LiteralPath $PhpPath).Path
$runner = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot 'Run-DonPatyLanProcess.ps1')).Path
$currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name

Push-Location -LiteralPath $resolvedProject
try {
    & $resolvedPhp artisan app:lan-readiness

    if ($LASTEXITCODE -ne 0) {
        throw 'DonPaty no superó app:lan-readiness; no se registraron tareas.'
    }
} finally {
    Pop-Location
}

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -RestartCount 10 `
    -RestartInterval (New-TimeSpan -Minutes 1) `
    -ExecutionTimeLimit (New-TimeSpan -Seconds 0) `
    -MultipleInstances IgnoreNew

$trigger = New-ScheduledTaskTrigger -AtLogOn -User $currentUser
$principal = New-ScheduledTaskPrincipal -UserId $currentUser -LogonType Interactive -RunLevel Limited

$tasks = @{
    'DonPaty-LAN-Web' = 'web'
    'DonPaty-LAN-Scheduler' = 'scheduler'
}

foreach ($task in $tasks.GetEnumerator()) {
    $arguments = @(
        '-NoProfile',
        '-NonInteractive',
        '-WindowStyle', 'Hidden',
        '-ExecutionPolicy', 'Bypass',
        '-File', "`"$runner`"",
        '-Role', $task.Value,
        '-ProjectPath', "`"$resolvedProject`"",
        '-PhpPath', "`"$resolvedPhp`""
    ) -join ' '

    $action = New-ScheduledTaskAction `
        -Execute 'powershell.exe' `
        -Argument $arguments `
        -WorkingDirectory $resolvedProject

    Register-ScheduledTask `
        -TaskName $task.Key `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Description "DonPaty LAN: proceso $($task.Value)" `
        -Force | Out-Null

    Start-ScheduledTask -TaskName $task.Key
}

Write-Host 'DonPaty LAN quedó registrado para iniciar con la sesión actual.'
Write-Host 'Consulta storage\logs\lan-web.log y lan-scheduler.log si un proceso no inicia.'
