param (
    [string]$DeviceDetection,
    [string]$CsvUrl
)

if ($env:GITHUB_JOB -eq "PreBuild") {
    Write-Host "Skipping assets fetching"
    exit 0
}

./steps/fetch-assets.ps1 -DeviceDetection:$DeviceDetection -CsvUrl:$CsvUrl -Assets "51Degrees.csv"
New-Item -ItemType SymbolicLink -Force -Target "$PWD/assets/51Degrees.csv" -Path "$PSScriptRoot/../tests/51Degrees.csv"
