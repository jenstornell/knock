# Changelog

## 1.5

- Added expires cookie to be able to see when the cookie expires.
- Added cookie option `cookie.expires.key` to make the expires cookie a bit more cryptic.
- Added cookie option `cookie.refresh` in case `knock::keepAlive()` is used.
- Added function `knock::keepAlive()` to trigger the cookies to refreh itself, if the cookie time is close to expire.
- Added function `knock::refresh()` to trigger the cookies to refresh.
- Added function `knock::getCookieExpires()` to get the cookies expire timestamp.
- Updated all tests.

## 1.4

- Added post option `post.username.key` and `post.password.key` to make it less guess fiendly.
- Changed the default cookie expire time from 20 years to 2 days.

## 1.3

- Added `salt` option.

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