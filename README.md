Easy to use PHP PDO MySQL framework for ```SELECT```, ```INSERT```, ```INSERT ON DUPLICATE UPDATE```,  ```UPDATE```, ```DELETE``` and ```TRUNCATE``` tasks.

All the queries are done pre-prepared, helping fight SQL injection.

[![Generic badge](https://img.shields.io/badge/php-7.4+-blue.svg)](https://shields.io/)
[![Generic badge](https://img.shields.io/badge/-PDO_MYSQL-blue.svg)](https://shields.io/)

## Usage

To use simply ```require_once('easyPDO.php);``` and/or extend your class from ```easyPDO```.

This framework is nothing groundbreaking it just suits vertically and is more readable.

```php
$db->insertDB('tablename', ['col','col2','col3','col4','col5'], ['value','value2','value3','value4','value5']);
```

OR

```php
$cols = ['col','col2','col3','col4','col5'];
$values = ['value','value2','value3','value4','value5'];
$db->insertDB('tablename', $cols, $values);
```

Instead of doing traditional:

```php
$db = db_connect();
$insert = $db->prepare('INSERT INTO `tablename` (`col`, `col2`, `col3`, `col4`, col5`) VALUES (?,?,?,?,?)');
$insert->execute(['value', 'value2', 'value3', 'value4', 'value5']);
```

# Examples

**Note** queries are done with pre-prepared statements, SQL examples shown below in raw for better clarity.

#### SELECT

Types:

```php
selectFetchAll();//Returns all (columns & rows) as array
selectFetch();//Returns row as array
selectFetchCol();//Returns column as string
```
Building queries:

*a*
```php
selectFetchAll('images', [], ['id'], ['60'], ['>']);
```

```sql
SELECT * FROM `images` WHERE `id` > 60
```
*b*
```php
selectFetchAll('images', ['id', 'name', 'size'], [], [], [], ['size', 'DESC']);
```

```sql
SELECT `id`, `name`, `size` FROM `images` ORDER BY `size` DESC
```
*c*
```php
selectFetchAll('images', ['id', 'name', 'size'], ['id'], ['55'], ['>='], ['size', 'DESC'], [12]);
```

```sql
SELECT `id`, `name`, `size` FROM `images` WHERE `id` >= 55 ORDER BY `size` DESC LIMIT 12
```
*d*
```php
selectFetchAll('images', [], [], [], [], ['uploaded', 'DESC'], [], ['uploaded', '2020-10-11 00:00:01', '2020-10-11 23:59:59']);
```

```sql
SELECT * FROM `images` WHERE `uploaded` BETWEEN '2020-10-11 00:00:01' AND '2020-10-11 23:59:59' ORDER BY `uploaded` DESC
```
*e*
```php
selectFetch('images', ['name'], ['id'], [55]);
```

```sql
SELECT `name` FROM `images` WHERE `id` = 55
```

#### INSERT

Types:

```php
insertDB();
```
Building queries:

*a*

```php
insertDB('images', ['name','size','location'], ['DCIM_101', 8745, 'waterfalls']);
```

```mysql
INSERT INTO `images` (`name`, `size`, `location`) VALUES ('DCIM_101', 8745, 'waterfalls')
```

*b*

```php
insertDB('images', ['name','size','location'], ['DCIM_101', 8745, 'waterfalls'], false, false, true);
```

```mysql
INSERT IGNORE INTO `images` (`name`, `size`, `location`) VALUES ('DCIM_101', 8745, 'waterfalls')
```

*c*

```php
insertDB('images', ['name','size','location'], ['DCIM_101', 8745, 'waterfalls'], true);
```

```mysql
INSERT INTO `images` (`name`, `size`, `location`) VALUES ('DCIM_101', 8745, 'waterfalls')
 ON DUPLICATE KEY UPDATE `name` = 'DCIM_101', `size` = 8745, `location` = 'waterfalls'
```

#### UPDATE

Types:

```php
updateDB();
```
Building queries:

*a*

```php
updateDB('images', ['name'], ['waterfall_pano'], ['id'], [], ['360']);
```

```mysql
UPDATE `images` SET `name` = 'waterfall_pano' WHERE `id` = 360
```
*b*

```php
updateDB('images', ['location'], ['waterfalls'], ['id'], ['>'], ['310']);
```

```mysql
UPDATE `images` SET `location` = 'waterfalls' WHERE `id` > 310
```

*c*

```php
updateDB('images', ['location'], ['waterfalls'], ['id'], ['>'], ['310'], [16]);
```

```mysql
UPDATE `images` SET `location` = 'waterfalls' WHERE `id` > 310 LIMIT 16
```

*d*

```php
updateDB('images', ['is_thumb'], [1], ['id', 'size'], ['>', '<='], ['310', 800], [16]);
```

```mysql
UPDATE `images` SET `is_thumb` = 1 WHERE `id` > 310 AND `size` <= 310 LIMIT 16
```

#### DELETE

Types:

```php
deleteDB();
```
Building queries:

*a*

```php
deleteDB('images', ['id'], ['>'], ['500']);
```

```mysql
DELETE FROM `images` WHERE `id` > 500
```

*b*

```php
deleteDB('images', ['id', 'size'], ['>', '>='], ['500', 2500], [8]);
```

```mysql
DELETE FROM `images` WHERE `id` > 500 AND `size` >= 2500 LIMIT 8
```

#### TRUNCATE

Types:

```php
truncateTable();
```
Building queries:

*a*

```php
truncateTable('images');
```

```mysql
TRUNCATE TABLE `images`
```
