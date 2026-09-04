[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$taskNames = @('DonPaty-LAN-Web', 'DonPaty-LAN-Scheduler')

foreach ($taskName in $taskNames) {
    $task = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

    if ($null -eq $task) {
        continue
    }

    Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

Write-Host 'Las tareas de inicio automático de DonPaty LAN fueron eliminadas.'
