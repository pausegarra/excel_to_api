# Excel to API export

---

_Export the data from an Excel to any API with JWT auth support._

## Usage

Install dependencies

```bash
  composer install
```

After installing fill this constants and put an excel with the name "data.xlsx" on the root folder.

```php
  const URL_BASE = 'http://localhost:8000/';
  const URL_FINAL = self::URL_BASE . '/';
  const ENDPOINT = "endpoint";

  const USERNAME = 'username';
  const PASSWORD = 'secret';
```

> **Important!** The excel must have headers, that headerx will be used to create the arrays with the key => value, for example.
> The following table in excel:
> | name | surname |
> |------|---------|
> | Jhon | Doe     |
> | Adam | Yakub   |
>
> Will result in the following array:
> ```php
> [
>   [
>     'name' => 'Jhon',
>     'surname' => 'Doe'
>   ],
>   [
>     'name' => 'Adam',
>     'surname' => 'Yakub'
>   ],
> ]
> ```

## Author

[Pau Segarra](https://github.com/pausegarra)

## License

This project is under the (MIT) License. See [LICENSE.md](LICENSE.md) for more info.
