# Q

Q is a ... no, you may expect now some buzzwords like "small" and "lightweight", 
but no abstracting away SQL within PHP is not a trivial task, especially when it comes to tables;
so: Q is a mixup between a "Database access Layer" and a Querybuilder that focuses on
human readable SQL statements.

```PHP
<main class="Blog">
<?php foreach(Q::Select("*")->From("Blog")->Limit(10)->Offset($Page * 10)->OrderBy("Date") as $Post): ?>
    <article class="Post">
        <h2><?= $Post["Title"] ?><h2>
        <p><?= $Post["Content"] ?></p>
    </article>
<?php endforeach; ?>
</main>
```

## Installation

Q is hosted on packagist. Simply call
``composer require devel0pmenthero/q``
in a console or add ``"devel0pmenthero/q": ^1.0.0`` to your composer.json.


## Usage

Q is built around a static facade for target RDBMS specific ``\Q\IProvider``-instances 
which contain methods for querying and escaping as well as factories for query builders and aggregate functions. 
The static Q facade acts as a proxy to the last connected "data provider"

```PHP
require "/vendor/autoload.php";

Q::Connect(\Q\MySQL\Provider::class, "localhost", 3306, $User, $Password, $Database, false);
Q::Execute("SELECT ID, Name, Price, Description FROM Shop.Products WHERE Stock <= 20");
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

The ``Q::Connect()``-method is basically just a virtual constructor which additionally 
propagates some provider specific values through static properties, so a direct call to the several implementations is possible too.
```PHP
$MySQL = new Q\MySQL\Provider("localhost", 3306, $User, $Password, ...);
$MySQL->Select("*")
      ->From("Shop.Products")
      ->Where(["Stock" => ["<=" => 20]]);
$PgSQL = new Q\PgSQL\Provider(...);
```

### SQL
Plain SQL strings can be executed on the server by passing the string through the ``Q::Execute()``-method.

````PHP
Q::Execute("SELECT ID, Name, Price FROM Shop.Products");
````

### Result sets
Every operation performed on the database, will end in the return of a specialized ``\Q\IResult``-instance 
which implements the ``\Traversable``-interface, thus removing the need of annoying while loops with "fetch_whatever()"-calls.

Directly iterating over a result set will yield the values returned by the ``IResult::ToMap()``-method for each row in the result set.
````PHP
foreach(Q::Execute("SELECT ID, Name, Price FROM Shop.Products") as $Product) {
    print $Product["ID"], $Product["Name"];
};
````

Invoking a result set is an alias of calling its ``ToValue()``-method which will return the first value of the result set.
````PHP
$ID = Q::Execute("SELECT ID FROM Shop.Products WHERE Price = 19.99")();
$Name = Q::Execute("SELECT Name FROM Shop.Products WHERE ID = $ID")->ToValue();
````

#### Streaming
By default, Q uses buffered result sets; to use result set streaming, most of Q's executing methods accept an optional boolean flag.
These apply to the ``Q::Execute($Statement, Buffered: false)`` and ``Q::Call($Procedure, Buffered: false)``-methods,
while Expressions require an additional step by manually calling their ``IExpression::Execute(Buffered: false)``-method.

Rewinding unbuffered result sets will throw a ``\RuntimeException()``.

### Escaping

#### Values

Values can be escaped via passing them to the ``Q::Escape()``-method, 
which is (except for the MsSQL-Provider) just an alias for the ``\mysqli::real_escape_string()`` and ``\pg_escape_string()``-methods.

To convert a value into a database compatible representation, 
the ``Q::Sanitize()``-method accepts any type of value and transforms it to an escaped string representation, 
while using ``\json_encode()`` for arrays and objects.

The Q library provides a simple ``\Q\IModel``-interface which requires only the implementation of an ``ID()``-method, 
that enables existing models being used as references.
````PHP
$Product = new \Models\Product(ID: $ID);
Q::Execute("SELECT * FROM Shop.Orders WHERE ProductID = " . Q::Sanitize($Product));

//Expressions wrap sanitation.
Q::Select("*")->From("Shop.Orders")->Where(["ProductID" => $Product])
````

#### Fields

Database-, schema-, table- and column-names can be escaped via passing them to the ``Q::EscapeField()``-method, 
which will check the name against a list of RDBMS specific keywords and quote it in case of its occurrence.
The ``Q::SanitizeField()``-method escapes an entire sequence of fields separated by the dot notation.

````PHP
Q::Execute("SELECT * FROM ". Q::SanitizeField("Shop.Orders") ." WHERE " . Q::EscapeField("ProductID") . " = " . Q::Sanitize($Product));
````

The PgSQL-Provider will quote every identifier by default due to PostgreSQL's folding to lowercase.

## Prepared statements
Prepared statements are created through the ``Q::Prepare()``-method which returns a specialized
implementation of the ``\Q\IPreparedStatement``-interface according the current database.

````PHP
$Statement = Q::Prepare("INSERT INTO Products (ID, Name, Price) VALUES (NULL, ?, ?)");

// Bulk import.
foreach($Products as $Product) {
    $Statement->Apply((string)$Product["Name"], (float)$Product["Price"])
    $Result = $Statement->Execute();
    if($Result->Status){
        $ID = Q::LastInsertID();
    }
}
````

## Transactions
Although stubs exist, transactions are currently under development.

## Expressions

"Expressions" are the main feature of Q - these are fluent interfaces that transform PHP-Code
into injection safe SQL while retaining its syntax as much as possible.

Expressions are early evaluated and have no form of syntactic or logic validation, you have to care on your own to call their methods in the correct order;
this library will only transfer an object-oriented API into plain SQL strings.

```PHP
$Result = Q::Select("ID", "Name", "Price")
           ->From("Shop.Products")
           ->Execute();
         
for($i = 0; $i < $Result->Count; $i++){
    [$ID, $Name, $Price] = $Result->ToArray();
}
```

Invoking an Expression will immediately execute it and yield the value returned from calling the ``IResult::ToValue()``-method of its result set.

```PHP
$Name = Q::Select("Name")
         ->From("Shop.Products")
         ->Where(["ID" => 37])();
```

Iterating over an Expression will immediately execute it and return its result set.

```PHP
foreach(
    Q::Select("ID", "Name", "Price")
     ->From("Shop.Products")
     ->Where(["Stock" => ["<=" => 20]])
    as
    $Product
) {
    var_dump($Product["ID"], $Product["Name"], $Product["Price"]);
}
```

Expressions can be print via simply casting them to a string.
```PHP
echo Q::Select("*")->From("Shop.Products");
// SELECT * FROM Shop.Products
```

### Select

Selecting database records is done through the ``Q::Select()``-method which returns a specialized
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

#### Distinct


Distinct selects are performed by passing the fields to select to a following call to the ``ISelect::Distinct()``-method.

```PHP
Q::Select()->Distinct("Name", "Category")
 ->From("Shop.Products")
 ->Where(["Price" => [">" => 19.99]]);
```

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

#### Aggregate functions

Aggregate functions can be applied through the 
``Q::Avg()``, ``Q:Min()``, ``Q:Max()``, ``Q:Sum()``, ``Q:Count()``, ``Q:Now()``, ``Q:Group()`` and ``Q:CurrentTimestamp()``-methods.

```PHP
Q::Select(Q::Avg("Price"), [Q::Count("Stock"), "Amount"])
 ->From("Shop.Products")
 ->Where(["Stock" => ["<=" => 20]]);
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT AVG(Price), COUNT(Stock) AS Amount 
FROM Shop.Products 
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT AVG(Price), COUNT(Stock) AS Amount 
FROM Shop.Products 
WHERE Stock <= 20 
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT AVG("Price"), COUNT("Stock") AS "Amount"
FROM "Shop"."Products" 
WHERE "Stock" <= 20 
```

</p>
</details>

#### Filtering records

To filter records, the ``\Q\Expression\ISelect``-, ``IUpdate`` and ``IDelete``-Expressions provide the ``IExpression::Where()``-method which accepts a map of filtering conditions.
The values of a set of filtering conditions will be chained with "AND"-statements, while every set of filtering conditions will be chained together with "OR"-statements.
```PHP
Q::Select("*")
 ->From("Shop.Products")
 ->Where(
     [
         "ID"       => ["IN" => [1, 2, 3]],
         "Name"     => [\Like => "%Memory%"],
         "Category" => [Where::In => ["SSD", "HDD", "RAM"]],
     ],
     [
         "Stock"        => ["BETWEEN" => [50, 100]],
         "Description"  => ["LIKE" => "%high performance%"],
         "CreationTime" => ["<" => $MsSQL->CurrentTimestamp()]
     ]
 );
```

<details><summary>MySQL</summary>
<p>

```SQL
SELECT *
FROM Shop.Products 
WHERE (
        (
            ID IN (1,2,3) 
            AND Name LIKE '%Memory%' 
            AND Category IN ('SSD','HDD','RAM')
        ) 
    OR 
        (
            (Stock BETWEEN 50 AND 100) 
            AND Description LIKE '%high performance%' 
            AND CreationTime < CURRENT_TIMESTAMP()
        )
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
        (
            ID IN (1,2,3) 
            AND Name LIKE '%Memory%' 
            AND Category IN ('SSD','HDD','RAM')
        ) 
    OR 
        (
            (Stock BETWEEN 50 AND 100) 
            AND Description LIKE '%high performance%' 
            AND CreationTime < CURRENT_TIMESTAMP
        )
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
        (
            "ID" IN (1,2,3) 
            AND "Name" LIKE '%Memory%' 
            AND "Category" IN ('SSD','HDD','RAM')
        ) 
    OR 
        (
            ("Stock" BETWEEN 50 AND 100) 
            AND "Description" LIKE '%high performance%' 
            AND "CreationTime" < CURRENT_TIMESTAMP()
        )
) 
```

</p>
</details>

#### Ordering records
To order records, the ``\Q\Expression\ISelect``-Expressions provides the ``IExpression::OrderBy()``-method which accepts a map of ordering conditions.
The key of the map represents the column while the value must be a boolean flag whether to sort in ascending(true) or descending(false) order
```PHP
Q::Select("*")
 ->From("Shop.Products")
 ->OrderBy([
    "Price" => true,
    "Stock" => false
]);
```
<details><summary>MySQL</summary>
<p>

```SQL
SELECT * FROM Shop.Products ORDER BY Price ASC, Stock DESC
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT * FROM Shop.Products ORDER BY Price ASC, Stock DESC
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT * FROM "Shop"."Products" ORDER BY "Price" ASC, "Stock" DESC
```

</p>
</details>

#### Limiting records
To limit records, the ``\Q\Expression\ISelect``-Expressions provides the ``IExpression::Limit()`` and ``IExpression::Offset()``-methods.
```PHP
Q::Select("*")
 ->From("Shop.Products")
 ->Limit(10)
 ->Offset(20);
```
<details><summary>MySQL</summary>
<p>

```SQL
SELECT * FROM Shop.Products LIMIT 10 OFFSET 20 
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT * FROM Shop.Products LIMIT 10 OFFSET 20 
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT * FROM "Shop"."Products" LIMIT 10 OFFSET 20 
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

#### Sub selects
Sub selects can be used in the ``ISelect::From()`` and ``ISelect::Exists()``-methods via passing another select Expression.

Sub selects in the "FROM"-clause require an alias as a second argument in the field list of the top select.
```PHP
Q::Select(Q::Count("Products.ID"))
 ->From(
     Q::Select("*")
      ->From("Orders")
      ->Where(["Delivered" => true]),
      "Products"
);
```
<details><summary>MySQL</summary>
<p>

```SQL
SELECT COUNT(Products.ID) 
FROM (
    SELECT * 
    FROM Orders 
    WHERE Delivered = 1
) AS Products 
```
</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT COUNT(Products.ID) 
FROM (
    SELECT * 
    FROM Orders 
    WHERE Delivered = 1
) AS Products 
```
</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT COUNT("Products"."ID") 
FROM (
    SELECT * 
    FROM "Orders" 
    WHERE "Delivered" = 1
) AS "Products" 
```
</p>
</details>


The "EXISTS"-clause is currently implemented in a separate method and requires a preceding call to the ``ISelect::Where()``-method
without passing any parameters to it. (This maybe changed to a filtering condition in the future)

```PHP
Q::Select("Customer")
 ->From("Orders")
 ->Where()
 ->Exists(
     Q::Select("Name")
      ->From("Products")
      ->Where(["Orders.ProductID" => "Products.ID"])
 );
```
Note: The ``ISelect::Where()``-method is only aware of the names and aliases of its own instance, 
this means a sub select doesn't know when to treat the value of a filtering condition as a reference to a foreign table field.
References to foreign columns have to be always defined as the key of filtering conditions, otherwise they'll be just treated as simple strings.

<details><summary>MySQL</summary>
<p>

```SQL
SELECT Customer 
FROM Orders 
WHERE EXISTS (
    SELECT Name 
    FROM Products 
    WHERE Orders.ProductID = Products.ID
)
```
</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT Customer 
FROM Orders 
WHERE EXISTS (
    SELECT Name 
    FROM Products 
    WHERE Orders.ProductID = Products.ID
)
```
</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT "Customer" 
FROM "Orders" 
WHERE EXISTS (
    SELECT "Name" 
    FROM "Products" 
    WHERE "Orders"."ProductID" = "Products"."ID"
)
```
</p>
</details>

#### Union selects

To combine result sets, the ``ISelect::Union()`` accepts an additional select Expression to use; 
followed by an optional boolean flag determining whether to use an "UNION ALL" statement instead.

```PHP
Q::Select("ID", "CustomerName")
 ->From("Orders")
 ->Union(
     Q::Select("ID", "ProductName")
      ->From("Products")
      ->Where(["Orders.ProductID" => "Products.ID"]),
      All: false
 );
```
<details><summary>MySQL</summary>
<p>

```SQL
SELECT ID, CustomerName 
FROM Orders 
UNION SELECT ID, ProductName 
FROM Products 
WHERE Orders.ProductID = Products.ID 
```
</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
SELECT ID, CustomerName 
FROM Orders 
UNION SELECT ID, ProductName 
FROM Products 
WHERE Orders.ProductID = Products.ID 
```
</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
SELECT "ID", "CustomerName" 
FROM "Orders" 
UNION SELECT "ID", "ProductName" 
FROM "Products" 
WHERE "Orders"."ProductID" = "Products"."ID" 
```
</p>
</details>

### Insert

Creating new database records is done through the ``Q::Insert()``-method which returns a specialized
implementation of the ``\Q\Expression\IInsert``-Expression according the current database.

If the first field name ends with the characters "ID" and its value is null, 
it's being replaced with "DEFAULT" by the PgSQL-Provider while being completely omitted by the MsSQL-Provider.

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

#### Retrieving autogenerated values 

Insert Expressions provide an ``IInsert::ID()``-method which is a shortcut to calling ``Q::LastInsertID()`` 
and will retrieve the last autogenerated value of identity columns.
```PHP
$ID = (int)Q::Insert()
            ->Into("Shop.Products")
            ->Values(["ID" => null])
            ->ID();
```

### Update

Updating database records is done through the ``Q::Update()``-method which returns a specialized
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

#### Conditional updates

The ``IUpdate::SetIf()``-method accepts an array of boolean flags as keys for the values to set, 
omitting false conditions from the resulting SQL statement.

```PHP
$StockChanged = true;
$Reordered = Reorder($ID);

Q::Update("Shop.Products")
 ->SetIf([
    "Stock"   => [$StockChanged => 10], 
    "Ordered" => [$Reordered => true]
 ])
 ->Where(["ID" => $ID]);
```

### Delete

Deleting database records is done through the ``Q::Delete()``-method which returns a specialized 
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

Creating new databases, schemas and tables is done through the ``Q::Create()``-method which returns a specialized 
implementation of the ``\Q\Expression\ICreate``-Expression according the current database.

#### Database

```PHP
Q::Create()
 ->Database("Vendor");
```

Note: This method won't have any effect while using the MySQL-Provider to stay compatible to schema based databases.
Databases can be created through the ``ICreate::Schema()``-method, this applies to the ``IAlter::Database()`` and ``IDrop::Database()``-methods too. 

<details><summary>MsSQL</summary>
<p>

```SQL
CREATE DATABASE Vendor COLLATE Latin1_General_100_CI_AI_SC_UTF8
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
CREATE DATABASE "Vendor" WITH ENCODING 'UTF8'
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

#### Index

```PHP
Q::Create()
 ->Index("SpecialOffer")
 ->On("Shop.Products", ["Price", "Stock"]);
```

<details><summary>MySQL</summary>
<p>

```SQL
CREATE INDEX SpecialOffer ON Shop.Products (Price, Stock)
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
CREATE INDEX SpecialOffer ON Shop.Products (Price, Stock)
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
CREATE INDEX "SpecialOffer" ON "Shop"."Products" ("Price", "Stock")
```

</p>
</details>

### Alter

Altering databases, schemas and tables is done through the ``Q::Alter()``-method which returns a specialized
implementation of the ``\Q\Expression\IAlter``-Expression according the current database.

#### Database

```PHP
Q::Alter()
 ->Database("Vendor")
 ->Rename("HardwareShop");
```

<details><summary>MsSQL</summary>
<p>

```SQL
EXECUTE sp_rename 'Vendor', 'HardwareShop'
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
ALTER DATABASE "Vendor" RENAME TO "HardwareShop"
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

Dropping databases, schemas and tables is done through the ``Q::Drop()``-method which returns a specialized
implementation of the ``\Q\Expression\IDrop``-Expression according the current database.

#### Database

```PHP
Q::Drop()->Database("Vendor");
```

<details><summary>MsSQL</summary>
<p>

```SQL
DROP DATABASE Vendor
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DROP DATABASE "Vendor"
```

</p>
</details>

#### Schema


```PHP
Q::Drop()->Schema("Shop");
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
Q::Drop()->Table("Shop.Products");
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

#### Index

```PHP
Q::Drop()->Index("SpecialOffer")->On("Shop.Products");
```

<details><summary>MySQL</summary>
<p>

```SQL
DROP INDEX SpecialOffer ON Shop.Products
```

</p>
</details>
<details><summary>MsSQL</summary>
<p>

```SQL
DROP INDEX SpecialOffer ON Shop.Products
```

</p>
</details>
<details><summary>PgSQL</summary>
<p>

```SQL
DROP INDEX "SpecialOffer" ON "Shop"."Products"
```

</p>
</details>

## About

This project is based on the database access layer of another hobby project of me,
if you're interested in further information, you can check out the documentation on https://vdesk.cloud/Documentation/Category/Server/Topic/Database 

Features will be downstreamed from the original project; however, the following features/changes are currently planned:
- Support for DB2, Oracle and Firebird(maybe) DBs.
- Further separation of Create, Alter and Drop-Expressions into separate classes.
- Support for transactions.
- Insert Expressions may return the last autogenerated value by default upon invocation.
- More verbose methods for table Expressions like ``AddColumn()``.
- Support for creating, altering and dropping views.
- Lazy Expressions which evaluate upon execution time.