# Counter Cache for Laravel
Counter Cache for Laravel

## Why ?
Imagine if you have to show 50 products in a list and have to show a counter for how many comments that product have, too much queries, right ? This package will allow you to super reduce the number of queries made.

## Feature Overview
- Increment counter automatically when creating a new record.
- Decrement counter automatically when deleting a record.
- Update counter automatically when updating a record
- ...

## Installation
Add this to your composer.json file, in the require object:

```javascript
"kanazaca/counter-cache": "1.0.*"
```

After that, run composer install to install the package.
Add the service provider to `config/app.php`, within the `providers` array.

```php
'providers' => array(
	// ...
	kanazaca\CounterCache\CounterCacheServiceProvider::class,
)
```

## Basic Usage

I will use the example products/comments, one product have many comments

### Migration
You need to create a field in the table that you want access the counter, like the example below:
```php
Schema::create('products', function (Blueprint $table) {
      $table->increments('id');
      $table->string('name');
      $table->string('ref');
      $table->integer('comments_count')->default(0); // This is the counter that you have to add
      $table->timestamps();
  });
```
After this run `php artisan migrate`

### Model
Comments model, you have to use the trait, define the $counterCacheOptions and make the relation with the product : 
```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use kanazaca\CounterCache\CounterCache;

class Comments extends Model
{
    use CounterCache;
    
    // you can have more than one counter 
    public $counterCacheOptions = [
        'Product' => ['field' => 'comments_count', 'foreignKey' => 'product_id']
    ];
    
    public function Product()
    {
        return $this->belongsTo('App\Product');
    }
}
```

## Filters

If you want to do some filtering before the counter cache magic happens, you have to add the key `filter` to `$counterCacheOptions` with the name of your method that will provide the filter, as string, like below:
```php
public $counterCacheOptions = [
    'Product' => [
        'field' => 'comments_count',
        'foreignKey' => 'product_id',
        'filter' => 'CommentValidatedFilter'
    ]
]; // you can have more than one counter 

// this code will be executed before the counting (save and update method)
public function CommentValidatedFilter() 
{
    if ($this->validated)
    {
        return true;
    }
    
    return false;
}
```

## Credits
Hugo Neto
