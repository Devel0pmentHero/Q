# Q

Q is a ... no, you may expect now some buzzwords like "small" and "lightweight", but no

## Installation

Q is hosted on packagist. Simply call
``composer require devel0pmenthero/q``
in a console or add ````


## Usage

```PHP
Q::Connect(\Q\MySQL\Provider::class, "localhost", 3306, $User, $Password, $Database, false)
 ->Execute("SELECT ID, Name, Price, Description FROM Shop.Products WHERE Stock <= 20");
```

### Connecting to a database using a default Provider via global config
The Q facade checks upon load if there's a global "QConfig" constant array defined and if so
uses its values to automatically connect to a "default" database.
If you're only using a single database connection in your project, you can define the credentials in your app config
and simply call the Q facade from wherever you need it.

```PHP
const QConfig = [
    "Provider"   => \Q\MySQL\Provider::class,
    "Server"     => "localhost",
    "Port"       => 3306,
    "User"       => $User,
    "Password"   => $Password,
    "Database"   => $Database,
    "Persistent" => false
];

Q::Select("ID", "Name", "Price", "Description")
 ->From("Shop.Products")
 ->Where(["Stock" => ["<=" => 20]]);
```


### Working with multiple connections

The static Q facade acts as a proxy to the last connected "data provider"
The ``Q::Connect()``-method returns

The ``Q::Connect()``-method is basically just a virtual constructor that additionally 
propagates some provider specific values, so a direct call to the several implementations is possible too.
```PHP
$MySQL = new Q\MySQL\Provider("localhost", 3306, $User, $Password, ...);
$MySQL->Select("*")
 ->From("Shop.Products")
 ->Where(["Stock" => ["<=" => 20]]);
$PgSQL = new Q\PgSQL\Provider(...);
```

## Expressions

"Expressions" are the main feature of Q - these are fluent interfaces that transform PHP-Code
into injection safe SQL while retaining its syntax as much as possible.

There's no syntactic or logic validation, you have to care on your own to call the methods in the correct order;
this library will only transfer a fluid object interface into plain SQL strings.


### Select

Selecting database records can be done by using the ``Q::Select()``-method which returns a specialized
implementation of the ``\Q\Expression\ISelect``-Expression according the current database.

```PHP
Q::Select("*")
 ->From("Shop.Products")
 ->Where(["Stock" => ["<=" => 20]]);
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT *
FROM Shop.Products
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT *
FROM Shop.Products
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT *
FROM "Shop"."Products"
WHERE "Stock" <= 20 
```

</p>
</details>

#### Aliases

Aliases can be applied by passing a key-value-pair of the field and alias instead.

```PHP
Q::Select("Price", ["Category" => "Topic"], ["Stock" => "Count"])
 ->From("Shop.Products")
 ->Where(["Stock" => ["<=" => 20]]);
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT Price, Category AS Topic, Stock AS Count
FROM Shop.Products
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT Price, Category AS Topic, Stock AS Count
FROM Shop.Products
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT "Price", "Category" AS "Topic", "Stock" AS "Count"
FROM "Shop"."Products"
WHERE "Stock" <= 20 
```

</p>
</details>

#### Filtering records

To filter records, the ``\Q\Expression\ISelect``-, ``IUpdate`` and ``IDelete``-Expressions provide the ``IExpression::Where()``-method which accepts a map of filtering conditions.
```PHP
Q::Select("*")
 ->From("Shop.Products")
 ->Where([
     "ID"           => ["IN" => [1, 2, 3]],
     "Name"         => [\Like => "%invoice%"],
     "Category"     => [Where::In => ["SSD", "HDD", ""]],
     "Stock"        => ["BETWEEN" => [50, 100]],
     "Description"  => ["LIKE" => "%invoice%"],
     "CreationTime" => ["<" => Q::CurrentTimestamp()]
 ])
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT * 
FROM Shop.Products 
WHERE (
    ID IN (1,2,3) 
    AND Name LIKE '%invoice%' 
    AND Category IN ('SSD','HDD','') 
    AND (Stock BETWEEN 50 AND 100) 
    AND Description LIKE '%invoice%' 
    AND CreationTime < CURRENT_TIMESTAMP()
)
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT * 
FROM Shop.Products 
WHERE (
    ID IN (1,2,3)
    AND Name LIKE '%invoice%'
    AND Category IN ('SSD','HDD','') 
    AND (Stock BETWEEN 50 AND 100) 
    AND Description LIKE '%invoice%' 
    AND CreationTime < CURRENT_TIMESTAMP
)
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT *
FROM "Shop"."Products"
WHERE (
    "ID" IN (1, 2, 3)
    AND "Name" LIKE '%invoice%'
    AND "Category" IN ('SSD', 'HDD', '')
    AND ("Stock" BETWEEN 50 AND 100)
    AND "Description" LIKE '%invoice%'
    AND "CreationTime" < CURRENT_TIMESTAMP()
)
```

</p>
</details>

#### Joins

To join the records of a different table into the result set, the ``\Q\Expression\ISelect``-Expression provides the
``ISelect::InnerJoin()``, ``ISelect::LeftJoin()``, ``ISelect::RightJoin()`` and ``ISelect::FullJoin()``-methods which accept the name of the table to join.
Comparison rules can be applied through the ``ISelect::On()``-method by following the same rules of filtering result sets.

```PHP
Q::Select("Products.Name", "Products.Price", "Orders.Amount", "Orders.Date")
 ->From("Shop.Products")
 ->InnerJoin("Shop.Orders")
 ->On(["Products.ID" => "Orders.Product"])
 ->Where(["Orders.Paid" => true])
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT Price, Category AS Topic, Stock AS Count 
FROM Shop.Products 
INNER JOIN Shop.Orders ON Products.ID = Orders.Product 
WHERE Orders.Paid = 1 
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT Price, Category AS Topic, Stock AS Count 
FROM Shop.Products 
INNER JOIN Shop.Orders ON Products.ID = Orders.Product 
WHERE Orders.Paid = 1 
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT "Price", "Category" AS "Topic", "Stock" AS "Count" 
FROM "Shop"."Products" 
INNER JOIN "Shop"."Orders" ON "Products"."ID" = "Orders"."Product" 
WHERE "Orders"."Paid" = 1 
```

</p>
</details>

### Insert

Creating new database records can be done by using the ``Q::Insert()``-method which returns a specialized
implementation of the ``\Q\Expression\IInsert``-Expression according the current database.

```PHP
Q::Insert()
 ->Into("Shop.Products")
 ->Values([
     "ID"   => null,
     "Name" => "8GB DDR5 Ram",
     "Price" => 60.99,
     "Category" => "RAM"
 ]);
```

<details><summary>MySQL</summary>
<p>

```SQL
INSERT INTO Shop.Products (ID, Name, Price, Category) VALUES (NULL, '8GB DDR5 Ram', 60.99, 'RAM')
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
INSERT INTO Shop.Products (Name, Price, Category) VALUES ('8GB DDR5 Ram', 60.99, 'RAM')
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
INSERT INTO "Shop"."Products" ("ID", "Name", "Price", "Category") VALUES (DEFAULT, '8GB DDR5 Ram', 60.99, 'RAM')
```

</p>
</details>

### Update

Updating database records can be done by using the ``Q::Update()``-method which returns a specialized
implementation of the ``\Q\Expression\IUpdate``-Expression according the current database.

```PHP
Q::Update("Shop.Products")
 ->Set(["Stock" => 10, "Ordered" => true])
 ->Where(["ID" => 29]);
```

<details><summary>MySQL</summary>
<p>

```SQL
UPDATE Shop.Products SET Stock = 10, Ordered = 1 WHERE ID = 29
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
UPDATE Shop.Products SET Stock = 10, Ordered = 1 WHERE ID = 29
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
UPDATE "Shop"."Products"  SET "Stock" = 10, "Ordered" = 1 WHERE "ID" = 29
```

</p>
</details>

### Delete

Deleting database records can be done by using the ``Q::Delete()``-method which returns a specialized 
implementation of the ``\Q\Expression\IDelete``-Expression according the current database.

```PHP
Q::Delete()
 ->From("Shop.Orders")
 ->Where(["Canceled" => true], ["Delivered" => true]);
```

<details><summary>MySQL</summary>
<p>

```SQL
DELETE FROM Shop.Orders WHERE (Canceled = 1 OR Delivered = 1)
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DELETE FROM Shop.Orders WHERE (Canceled = 1 OR Delivered = 1)
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DELETE FROM "Shop"."Orders" WHERE ("Canceled" = 1 OR "Delivered" = 1)
```

</p>
</details>

### Create

Creating new databases, schemas and tables can be done by using the ``Q::Create()``-method which returns a specialized 
implementation of the ``\Q\Expression\ICreate``-Expression according the current database.

#### Database

```PHP
Q::Create()
 ->Database("HardwareStore");
```

<details><summary>MsSQL</summary>
<p>

```SQL
CREATE DATABASE HardwareStore COLLATE Latin1_General_100_CI_AI_SC_UTF8
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
CREATE DATABASE "HardwareStore" WITH ENCODING 'UTF8'
```

</p>
</details>

#### Schema

```PHP
Q::Create()
 ->Schema("Shop");
```

<details><summary>MySQL</summary>
<p>

```SQL
CREATE DATABASE Shop
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
CREATE SCHEMA Shop
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
CREATE SCHEMA "Shop"
```

</p>
</details>

#### Table

```PHP
Q::Create()
 ->Table(
    "Shop.Products",
    [
        "ID"          => ["Type" => Type::BigInt | Type::Unsigned, "Autoincrement" => true],
        "Name"        => ["Type" => Type::TinyText, "Collation" => Collation::UTF8],
        "Price"       => ["Type" => Type::Double | Type::Unsigned,],
        "Category"    => ["Type" => Type::TinyText, "Collation" => Collation::UTF8],
        "Stock"       => ["Type" => Type::Int | Type::Unsigned, "Size" => 2],
        "Description" => ["Type" => Type::Text, "Collation" => Collation::UTF8, "Default" => "No description available"]
    ],
    [
        "Primary" => ["Fields" => ["ID", "Name"]]
    ]
);
```

<details><summary>MySQL</summary>
<p>

```SQL
CREATE TABLE Shop.Products (
    ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    Price DOUBLE UNSIGNED NOT NULL,
    Category TINYTEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    Stock INT(2) UNSIGNED NOT NULL,
    Description TEXT COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No description available',
    PRIMARY KEY (ID, Name)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
CREATE TABLE Shop.Products (
    ID BIGINT NOT NULL IDENTITY (1, 1),
    Name NVARCHAR(255) COLLATE Latin1_General_100_CI_AI_SC_UTF8 NOT NULL,
    Price DOUBLE PRECISION NOT NULL, 
    Category NVARCHAR(255) COLLATE Latin1_General_100_CI_AI_SC_UTF8 NOT NULL, 
    Stock INT NOT NULL, 
    Description NVARCHAR(MAX) COLLATE Latin1_General_100_CI_AI_SC_UTF8 NOT NULL DEFAULT 'No description available', 
    PRIMARY KEY (ID, Name)
);
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
CREATE TABLE "Shop"."Products" (
    "ID" BIGSERIAL NOT NULL, 
    "Name" VARCHAR(255) NOT NULL, 
    "Price" DOUBLE PRECISION NOT NULL, 
    "Category" VARCHAR(255) NOT NULL, 
    "Stock" INTEGER NOT NULL, 
    "Description" VARCHAR(65535) NOT NULL DEFAULT 'No description available',
     PRIMARY KEY ("ID", "Name")
); 
```

</p>
</details>

### Alter

Altering databases, schemas and tables can be done by using the ``Q::Alter()``-method which returns a specialized
implementation of the ``\Q\Expression\IAlter``-Expression according the current database.

#### Database

```PHP
Q::Alter()
 ->Database("HardwareStore")
 ->Rename("HardwareShop");
```

<details><summary>MsSQL</summary>
<p>

```SQL
EXECUTE sp_rename 'HardwareStore', 'HardwareShop'
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
ALTER DATABASE "HardwareStore" RENAME TO "HardwareShop"
```

</p>
</details>

#### Schema

```PHP
Q::Alter()
 ->Schema("Shop")
 ->Rename("Store");
```

<details><summary>MySQL</summary>
<p>

```SQL
ALTER DATABASE Shop RENAME Store
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
ALTER SCHEMA Shop TRANSFER dbo.Store
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
ALTER SCHEMA "Shop" RENAME TO "Store"
```

</p>
</details>

#### Table

```PHP
Q::Alter()
 ->Table("Shop.Products")
 ->Add(
     ["SpecialOffer" => ["Type" => Type::Boolean, "Autoincrement" => true]],
     ["Prices" => ["Unique" => true, "Fields" => ["ID", "Sender", "Recipient"]]]
 )
 ->Modify(
     ["Description" => ["Type" => Type::TinyText, "Collation" => Collation::ASCII]],
     ["Prices" => "Price"]
 )
 ->Rename("Items")
 ->Drop(
     ["SpecialOffer"],
     ["Prices"]
 );
```

<details><summary>MySQL</summary>
<p>

```SQL
ALTER TABLE Shop.Products 
    ADD COLUMN SpecialOffer TINYINT(1) UNSIGNED NOT NULL AUTO_INCREMENT, 
    ADD UNIQUE INDEX Prices (ID, Sender, Recipient),
    MODIFY COLUMN Description TINYTEXT CHARACTER SET ascii NOT NULL,
    RENAME INDEX Prices TO Price, RENAME Items, 
    DROP COLUMN SpecialOffer,
    DROP INDEX Prices
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DROP INDEX Prices ON Shop.Products;
ALTER TABLE Shop.Products DROP COLUMN SpecialOffer;
ALTER TABLE Shop.Products ADD SpecialOffer TINYINT NOT NULL IDENTITY (1, 1);
CREATE  UNIQUE INDEX Prices ON Shop.Products (ID, Sender, Recipient);
ALTER TABLE Shop.Products ALTER COLUMN Description VARCHAR(255) COLLATE Latin1_General_100_CI_AI NOT NULL;
EXECUTE sp_rename 'Shop.Products.Prices', 'Price', 'INDEX';
EXECUTE sp_rename 'Shop.Products', 'Items'
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
ALTER TABLE "Shop"."Products" RENAME TO "Items" 
    ADD COLUMN "SpecialOffer" SMALLSERIAL NOT NULL,
    ALTER COLUMN "Description" TYPE VARCHAR(255), 
    DROP COLUMN "SpecialOffer"; 
CREATE UNIQUE INDEX "Prices" ON "Shop"."Products" ("ID", "Sender", "Recipient"); 
ALTER INDEX "Prices" RENAME TO "Price"; 
DROP INDEX "Prices" ON "Shop"."Products"
```

</p>
</details>

### Drop

Dropping databases, schemas and tables can be done by using the ``Q::Drop()``-method which returns a specialized
implementation of the ``\Q\Expression\IDrop``-Expression according the current database.

#### Database

```PHP
Q::Drop()
 ->Database("HardwareStore");
```

<details><summary>MySQL</summary>
<p>

```SQL
EXECUTE sp_rename 'HardwareStore', 'HardwareShop'
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DROP DATABASE HardwareStore
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DROP DATABASE "HardwareStore"
```

</p>
</details>

#### Schema


```PHP
Q::Drop()
 ->Schema("HardwareStore");
```

<details><summary>MySQL</summary>
<p>

```SQL
DROP DATABASE Shop
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DROP SCHEMA Shop
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DROP SCHEMA "Shop"
```

</p>
</details>

#### Table


```PHP
Q::Drop()
 ->Table("Shop.Products");
```

<details><summary>MySQL</summary>
<p>

```SQL
DROP TABLE Shop.Products
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DROP TABLE Shop.Products
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DROP TABLE "Shop"."Products"
```

</p>
</details>
