# sLMS

[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/slms?label=version)](https://packagist.org/packages/seiger/slms)
[![Evolution CMS version requirement](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FSeiger%2FsLMS%2Fmain%2Fcomposer.json&query=%24.require%5B%27evolution-cms%2Fevolution%27%5D&label=CMS&prefix=Evolution%20&color=brightgreen)](https://github.com/evolution-cms/evolution)
[![PHP: 8.4, 8.5 and 8.6](https://img.shields.io/badge/PHP-8.4_%7C_8.5_%7C_8.6-777BB4?logo=php&logoColor=white)](#compatibility)
[![License](https://img.shields.io/packagist/l/seiger/slms)](https://packagist.org/packages/seiger/slms)
[![Issues](https://img.shields.io/github/issues/Seiger/sLMS)](https://github.com/Seiger/sLMS/issues)
[![Stars](https://img.shields.io/packagist/stars/seiger/slms)](https://packagist.org/packages/seiger/slms)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/slms)](https://packagist.org/packages/seiger/slms)
![Made in Ukraine](https://img.shields.io/badge/made_in-Ukraine-ffd700.svg?labelColor=0057b7)

sLMS is a learning management package for Evolution CMS. It is being developed to manage courses, lessons, enrolments, progress, assessments, and certificates.

The initial version registers the package service provider. The learning workflows will be added as their interfaces are validated in the host project.

## Installation

After the package is available on Packagist, run the following command from the Evolution CMS `core` directory. Use `dev-main` while the package is on its development branch.

```sh
php artisan package:installrequire seiger/slms dev-main
```

Once version 0.1.0 is tagged, use `^0.1` for installations that track tagged releases.

Evolution CMS discovers `Seiger\sLMS\sLMSServiceProvider` during package installation.

## License

sLMS is distributed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
