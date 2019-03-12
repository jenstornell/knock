# Changelog

## 1.2

- Force cookie to `httponly` is now `true`.
- Added cookie options `cookie.domain` and `cookie.secure`.
- Added `cookie.username.key` and `cookie.hash.key` options to have a bit more cryptic cookies.

## 1.1

- If temp folder is missing it's now added.
- Changed option `prefix` to `cookie.prefix`
- Check if user file exists before trying to use the file
- Option `delay` implemented. It will delay the login time to prevent brute force attacks

## 1.0

- Initial release