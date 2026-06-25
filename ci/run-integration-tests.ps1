param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName,
    [Parameter(Mandatory=$true)]
    [hashtable]$Keys
)

if (!$Keys.TestResourceKey) {
    Write-Output "::warning file=$($MyInvocation.ScriptName),line=$($MyInvocation.ScriptLineNumber),title=No Resource Key::No resource key was provided, so integration tests will not run."
    return
} elseif (!(Test-Path $RepoName/tests/51Degrees.csv)) {
    Write-Output "::warning file=$($MyInvocation.ScriptName),line=$($MyInvocation.ScriptLineNumber),title=No CSV File::CSV file wasn't found, so integration tests will not run."
    return
}

$env:resource_key = $Keys.TestResourceKey
$env:AcceptChPlatformKey = $Keys.TestResourceKey
$env:AcceptChHardwareKey = $Keys.TestResourceKey
$env:AcceptChBrowserKey = $Keys.TestResourceKey
$env:AcceptChNoneKey = $Keys.TestResourceKey

./php/run-integration-tests.ps1 -RepoName $RepoName
$status = $LASTEXITCODE

Write-Host 'Running Selenium tests...'
try {
    # Start this repo's cloud example, pointed at the live cloud.
    Push-Location "$PSScriptRoot/../examples"
    try {
        composer install --working-dir=..
        $env:PORT = 8096
        $env:resource_key = $Keys.TestResourceKey
        $env:cloud_endpoint = "https://cloud.51degrees.com/api/v4/"
        $example = php -S "localhost:$env:PORT" cloud/gettingStartedWeb.php 2>&1 &
    } finally { Pop-Location }

    # Get the shared contract tests.
    if (-not (Test-Path selenium-api-tests)) {
        git clone --depth 1 https://github.com/51Degrees/selenium-api-tests.git
    }
    # Wait for the example to come up.
    curl -sS -o /dev/null --retry 5 --retry-connrefused "http://localhost:$env:PORT"

    $env:CLOUD_ROOT_URL = "https://cloud.51degrees.com/"
    $env:PAID_RESOURCE_KEY = $Keys.TestResourceKey
    $env:EXAMPLE_URL = "http://localhost:$env:PORT"
    $env:EXAMPLE_LANG = 'php'
    dotnet test selenium-api-tests -c Release --filter TestCategory=Contract
} catch {
    if ($example) { Write-Host '>>> example app output >>>'; Receive-Job $example | Out-Host; Write-Host '<<< app output <<<' }
    throw
} finally {
    if ($example) { Remove-Job -Force $example }
}
