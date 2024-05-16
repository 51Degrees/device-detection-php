# 51Degrees PHP Device Detection

![51Degrees](https://51degrees.com/DesktopModules/FiftyOne/Distributor/Logo.ashx?utm_source=github&utm_medium=repository&utm_content=readme_main&utm_campaign=php-open-source "Data rewards the curious") **PHP Device Detection**

[Developer Documentation](https://51degrees.com/device-detection-php/index.html?utm_source=github&utm_medium=repository&utm_content=documentation&utm_campaign=php-open-source "developer documentation")

## Introduction
This project contains the source code for the PHP implementation of 51Degrees' 
cloud-based device detection engine for use with the 
[Pipeline API](https://github.com/51Degrees/pipeline-php-core).

## Dependencies

The [tested versions](https://51degrees.com/documentation/_info__tested_versions.html) page shows 
the PHP versions that we currently test against. The software may run fine against other 
versions, but additional caution should be applied.

You will require a [resource key](https://51degrees.com/documentation/_info__resource_keys.html)
to use the Cloud API. You can create resource keys using our 
[configurator](https://configure.51degrees.com/), see our 
[documentation](https://51degrees.com/documentation/_concepts__configurator.html) on how to use this.

## Examples

**Note: Examples run on Linux and macOS. We don't test on Windows environments as it is a rare choice for PHP.**

To run the examples, you will need PHP and composer installed.
Once these are available, install (or update) the dependencies required by the examples. 
Navigate to the repository root and execute:

```sh
composer install
```
or 

```sh
composer update
```
if dependencies were install previously.


This will create the vendor directory containing autoload.php.

Ensure the `resource_key` environment variable is set to your resource key prior to running the example scripts.
Now navigate to the examples directory and start a PHP server with the relevant file. 
For example:

```
resource_key=YOUR_RESOURCE_KEY php -S localhost:3000 cloud/gettingStartedWeb.php
```

or to run in a terminal

```
resource_key=YOUR_RESOURCE_KEY php cloud/gettingStartedConsole.php
```

This will start a local web server listening on port 3000. 
Open your web browser and browse to http://localhost:3000/ to see the example in action.

The table below describes the examples that are available.

| Example                                | Description |
|----------------------------------------|-------------|
| gettingStartedConsole                  | How to use the 51Degrees Cloud service to determine details about a device based on its User-Agent and User-Agent Client Hints HTTP header values. |
| gettingStartedWeb                      | How to use the 51Degrees Cloud service to determine details about a device as part of a simple ASP.NET website. |
| tacLookupConsole                       | How to get device details from a TAC (Type Allocation Code) using the 51Degrees cloud service. |
| nativeModelLookupConsole               | How to get device details from a native model name using the 51Degrees cloud service. |
| metaDataConsole                        | Demonstrates how to access meta-data relating to the properties that device detection can populate. |
| failureToMatch                         | Demonstrates the functionality available when device detection is unable to identify the details of the device. |
| userAgentClientHints-Web               | Legacy example. Retained for the associated automated tests. See GettingStarted-Web instead. |

## Tests
This repo has tests for the examples. To run the tests, make sure PHPUnit is installed then,
in the root of this repo, call:

```
phpunit --fail-on-warning --display-warnings --log-junit test-results.xml
```

## On-premise device detection

The on-premise implementation of device detection is much faster but requires more 
memory and processing power than the cloud version. Unfortunately, distributing the 
on-premise package via composer would require the inclusion of binary executables (.so/.dll) 
that are not permitted. If you wish to use the on-premise version then you will need to clone 
the [on-premise repository](https://github.com/51Degrees/device-detection-php-onpremise) and
follow the instructions in the readme to build the required modules. This is
a fairly involved process so feel free to [contact us](mailto:support@51degrees.com) 
if you are having difficulties.

## Development

When making changes to this repository, it may be necessary to link to a local
development version of pipeline dependencies. For information on this, see
 [Composer local path](https://getcomposer.org/doc/05-repositories.md#path).

For exmaple, if a development version of `51degrees/fiftyone.pipeline.core` 
was stored locally, the location would be added with:

```
"repositories": [
	{
		"type": "path",
		"url": "../../path/to/packages/pipeline-php-core"
	}
]
```
then the dependency changed to:

```
"51degrees/fiftyone.pipeline.core": "*"
```
