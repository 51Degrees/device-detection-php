# API Specific CI/CD Approach
This API complies with the `common-ci` approach.

The following secrets are required:
* `ACCESS_TOKEN` - GitHub [access token](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#about-personal-access-tokens) for cloning repos, creating PRs, etc.
    * Example: `github_pat_l0ng_r4nd0m_s7r1ng`
* `SUPER_RESOURCE_KEY` - [resource key](https://51degrees.com/documentation/4.4/_info__resource_keys.html) for integration tests
    * Example: `R4nd0m-S7r1ng`
* `DEVICE_DETECTION_KEY` - [license key](https://51degrees.com/pricing) for downloading assets (TAC hashes file and TAC CSV data file)
    * Example: `V3RYL0NGR4ND0M57R1NG`
