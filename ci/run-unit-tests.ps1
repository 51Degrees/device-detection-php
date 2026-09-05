param (
    [Parameter(Mandatory=$true)]
    [string]$RepoName
)

# The unit suite runs against fixed cloud responses, so it needs no resource
# key, no licence key and no network connection. It runs on every build,
# which is what makes a broken cloud example show as red on the dashboard
# even when the integration tests are skipped for want of a key.
./php/run-unit-tests.ps1 -RepoName $RepoName

exit $LASTEXITCODE
