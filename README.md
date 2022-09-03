# DB Model

PHPでリレーショナルデータベースを操作するための O/Rマッパーです。以前作成した **php-ormapper** の改良版です。
このライブラリを使って、テーブルの作成・更新やレコードの作成・更新・取得などが可能です。

## 使い方

## モデルクラス

*\Taro\DBModel\Models\Model* クラスを継承する

```php
class Post extends Model
{
    // フィールド名を protected で定義
    protected $title;

    protected $body;

    // リレーションメソッドを定義
    public function relatedComments()
    {
        return $this->hasMany(Comment::class);
    } 
}
```

### リレーションの定義

モデルクラス内に、リレーションメソッドを定義する
以下の関係を定義できる。

* 1対1：hasOne(関係先のモデルクラス);
* 1対多：hasMany(関係先のモデルクラス);
* 多対1：belongsTo(関係先のモデルクラス);
* 多対多：manyToMany(関係先のモデルクラス, 中間テーブル名);
* 中間モデルを介した１対多：hasManyThrough(関係先のモデルクラス, 中間モデルクラス);
* 中間モデルを介した多対1：belongsToThrough(関係先のモデルクラス, 中間モデルクラス);

```php
// モデル と Post が1対1
public function post()
{
    return $this->hasOne(Post::class);
}

// モデル と Post が1対多
public function posts()
{
    return $this->hasMany(Post::class);
}

// モデル と User が多対1
public function user()
{
    return $this->belongsTo(User::class);
}   

// モデル と Post が favorites 中間テーブルを介して 多対多
public function favoritePosts()
{
    return $this->manyToMany(Post::class , 'favorites');
}

// モデル と Comment が Postモデルを介して 1対多
public function comments()
{
    return $this->hasManyThrough(Comment::class, Post::class);
}

// モデル と User が Postモデルを介して 多対1
public function users()
{
    return $this->belongsToThrough(User::class, Post::class);
} 

```



### レコードの新規作成

1. インスタンスを作成し、プロパティに値を代入して保存

   ```php
   $post = new Post;
   $post->title = 'title1';
   $post->body = 'test';
   $post->insert();
   ```

   

2. **fill**メソッドに、配列を渡して一括保存

```php
$post = new Post();
$data = [
    'title'=>'How to cook pizza2',
    'content'=>'test create from array'
];
$post->fill($data)->insert();

```




### レコードの更新

1. インスタンスのプロパティに更新データを代入

   ```php
   $post = Post::query()->findById(1);
   $post->body = 'updated';
   $post->update();
   ```

   

2. インスタンスの**fill**メソッドに、更新データを配列で渡して更新

   ```php
   $post = Post::query()->findById(1);
   $data = [
       'body' => 'updated'
   ];
   $post->fill($data)->update();
   ```

### レコードの削除

```php
$post = Post::query()->findById(1);
$post->delete();
```

## クエリビルダとクエリの実行

モデルクラスの **query** を起点にクエリを作成していく。

### クエリビルダの作成

必要なメソッドをメソッドチェーンで繋いでいく

```php
// where(カラム名, 値) 又は、 where(カラム名, oper, 値)
Post::query()->where('title', 'IN', ['test1', 'test2'])
Post::query()->where('id', '1')
// whereIn(カラム名, 値の配列)
Post::query()->whereIn('id', [1,2,3])
// whereBetween  (カラム名, min, max)  
Post::query()->whereBetween('id', 2, 4)

// orderBy(カラム名, ['DESC' | 'ASC'])
Post::query()->orderBy('id', 'DESC')
// limit(取得数)
Post::query()->limit(2)
// groupBy(カラム名)
Post::query()->groupBy('user_id')
    
// カラムを選択
Post::query()->select('title','body')
```

### クエリの実行

クエリビルダを作成した後、あるいは単独で実行できるメソッド。

```php
// 複数のレコードを取得
$posts = Post::query()->where('id', '>', '2')->getAll();
// 最初のレコードを取得
$posts = Post::query()->where('id', '1')->getFirst();
// モデルクラスから単独で実行できる
$posts = Post::query()->getAll();

// 集計用のメソッド
// count, average, max, min, sum
Post::query()->count();
Post::query()->count('views'); // カラムを指定
```

### 複雑な WHERE 句の作成

**Wh** クラスを使って、Where句を作成できます。作成後にクエリビルダの**addWhClause**メソッドで、クエリに追加します。

```php
$query = Post::query();
// where 句の作成
// WHERE (views > 2) AND (hidden = "public") OR (title = "test3")
$where = new Wh();       
$where->addAnd('views', '>', '2');
$where->addAnd('hidden','public');
$where->addOr('title', 'test3');
// where句をクエリに追加
$query->addWhClause($where);

$posts = $query->getAll();
```

入れ子の構造を作成するには、Whの **static なメソッド**を使います。

```php
$where = new Wh();
// WHERE (hidden = "public") AND ((views > 2) OR (title = "test3"))
$where->addBlock(
    Wh::and(
        Wh::block('hidden', 'public'),
        Wh::or(
            Wh::block('views', '>', '2'),
            Wh::block('title', 'test3')
        )
    )
);    
$query2->addWhClause($where);

```

### モデルのリスト用クラス

クエリビルダで **getAll** 実行後の結果は、**ActiveList** を実装したクラスのオブジェクトとして返されます。AcitveList は **ArrayList**, **ObjectList** のインターフェースです。配列として扱えるうえ、リストを操作する便利なメソッドを提供します。

- ArrayList: 配列データが対象
- ObjectList: モデルオブジェクトのリストが対象

```php
// getArrayAllの戻り値は、配列のリストオブジェクト（ArrayList）になります
$posts = Post::query()->where('id', '>', '2')->getArrayAll();
```



#### 上記インターフェースが提供するメソッド

```php
merge(): ActiveList 同士の結合

orderBy(key, 'asc'): リストの並び替え
orderBy(callback)

pluck(key): リストの各要素の特定キーのみを取得する

filter(condition): リストを条件で絞り込む

shift():  先頭を取得してリストから取り除く

pop()：末尾を取得してリストから取り除く

first(): リストの先頭を取得

last(): リストの末尾を取得

slice(offset, length): 先頭から offset 番目から length 個取り出す

map(callback): 各要素に処理を実行する

ifAny(callback): 条件が true のものがひとつでもあるか

ifAll(callback): すべての要素で条件が true になるか

groupBy(key): key の値でリストをグループ分けする

getKeyMap(key): key の mapデータを作成する 
    
// 使用例
$titles = $post->filter(function($item) {
    return $item->id >= 3;
})->pluck('title');
```

### ページネーション

レコードを指定した数のモデルを取得、戻り値は Paginator クラスになります。

```php
// paginate(一度に取得するレコード数)
$posts = Post::paginate(10);
?>
    
<table>
    <thead>
        <tr>
        <th>ID</th>
        <th>title</th>
        <th>body</th>
        </tr>
    </thead>
    <tbody>
    	<?php foreach ($posts as $post): ?>
            <tr>
            <td><?= $post->id ?></td>
            <td><?= $post->title ?></td>
            <td><?= $post->body ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php 
   // リンクを表示する
   $posts->dispLinks(); 
?>

```

### リンクデータの取得

タグではなく、データのみ必要な場合は、 **getLinkData** メソッドを使います。

```php
$data = $posts->getLinkData(); 

// 以下、データのフォーマット
$data =[ 
    'routeUrl' => 'http://localhost/posts?key1=value1',
    'links' =>
    array (
        array (
            'label' => '最初',
            'href' => 'http://localhost/posts?key1=value1?pageNo=0',
        ),
        array (
            'label' => '前',
            'href' => 'http://localhost/posts?key1=value1?pageNo=1',
        ),
        array (
            'label' => 1,
            'href' => 'http://localhost/posts?key1=value1?pageNo=0',
        ),
        array (
            'label' => 2,
            'href' => 'http://localhost/posts?key1=value1?pageNo=1',
        ),
        array (
            'label' => 3,
            'href' => 'http://localhost/posts?key1=value1?pageNo=2',
            'selected' => true,
            'disabled' => true,
        ),
        array (
            'label' => '次',
            'href' => 'http://localhost/posts?key1=value1?pageNo=2',
            'disabled' => true,
        ),
        array (
            'label' => '最後',
            'href' => 'http://localhost/posts?key1=value1?pageNo=2',
            'disabled' => true,
        ),
    ),       
];

```

### リンクのデザインのカスタマイズ

デフォルトでは、bootstrapデザインのリンクが表示されます（要Bootstrapライブラリ）。

カスタマイズしたい場合は、BasicLinks クラスを拡張したクラスを作成し、Paginator::setTemplate() の引数に渡してください。

```php
$posts = Post::paginate(10);
$posts->setTemplate(new CustomLinks);
```

## モデルクラスを使わないクエリ実行方法

### DirectSql クラス

**DirectSql** を使えば、モデルクラスを介さずクエリを作成・実行したり、直接SQL文を実行できます。

```php
// 直接SQL文を実行
$sql = 'SELECT * FROM posts WHERE title = :title';
$query = DirectSql::query()->prepareSql($sql);
$query->bindParam(':title', 'my first post');
$results = $query->runSql();

// よりORM的な記述方法
// レコードの取得
$results = DirectSql::query()
    ->table('posts')
    ->select('title','body')
	->where('id','>=', 2)
	->orderBy('create_date')
	->limit(10)
    ->getAsModels(Post::class);
// 更新
DirectSql::query()
    ->table('posts')
    ->where('title', 'test')
    ->update([
        'title' => 'test 1',
        'body' => 'test post 1'
    ]);
// 削除
DirectSql::query()
    ->table('posts')
    ->where('id', 5)
    ->delete();
```

### リレーション先のモデルの取得

```php
// 関連モデルのデータを取得
$user = $post->user->getFirst();
$posts = $user->posts->getAll();

// eagerloading N+1問題の解決
// eagerLoad(['リレーション名'])
User::query()->eagerLoad(['tasks'])->getAll();
$tasks = $user->tasks;
```



## DB接続設定ファイル

*src/Config/Database.php* ファイル内にデータベース接続情報を登録します。 **.env** ファイルに記載した情報を使う場合は、 **env()** メソッドを使います。

```php
// connections 以下に、接続名をキーとして、接続情報を指定する
return [
    'default'=>'mysql', // デフォルト接続先
    'connections' => [
        'mysql'=>[
            'driver'=>'mysql',
            'host'=>'localhost',
            'user'=>'root',
            'password'=> env('DB_PASSWORD'),  // .env ファイルのデータを指定
            'dbname'=>'tasksdb',
        ],
        'pgsql'=>[
            'driver'=>'pgsql',
            'host'=>'localhost',
            'user'=>'postgres',
            'password'=>'password',
            'dbname'=>'MyDB',
            'schema'=>'public',
            'port'=>5433,
        ],
        'sqlite'=>[
            'driver'=>'sqlite',
            'dsn'=>'sqlite:' . FileHandler::SQLITE_PATH,
        ],
    ]
];
```

### データベースへ接続

モデルクラスを使う前に、RDBAdapterクラスのinitメソッドでDBへ接続する

```php
use Taro\DBModel\DB\DB;
// 引数として利用する接続名を渡す（省略すると、Database.phpに記載したdefault値が使われる）
$db = DB::start('mysql', true);

// 接続を閉じる
DB::stopGlobal();
```

### トランザクション制御

DBクラスを使って、トランザクションをコントロールします。

```php
// トランザクション開始
DB::beginTrans();
// ロールバック
DB::rollback();
// コミット
DB::commit();
```





## DDL関連

### データベースの管理

データベースの作成や削除を実行します。(Sqlite は対象外)

```php
use Taro\DBModel\Schema\Database;

// データベースを作成
Database::create('データベース名');
// データベースを削除
Database::dropIfExists('データベース名');
```

### テーブルの作成

**Schema::createTable** を使い、テーブルを作成します。引数のコールバックのタイプヒンティングにはデータベースに応じて、MySqlTable／PostgreSqlTable／SqliteTable を指定してください。

```php
use Taro\DBModel\Schema\Schema;

Schema::createTable('test2', function(MySqlTable $table){
    $table->addColumn('id','int')->unsigned()->primary();
    $table->addColumn('content','text')->nullable();
    $table->addColumn('status','string')->length(5)->default('good');
    $table->addColumn('user_id','int')->unsigned();

    $table->addUnique('content', 'status');
    $table->addForeign('user_id')->references('users', 'id')->onDelete('CASCADE');
});
// データベースによっては、一部のメソッドが利用できません。（例：postgresqlでは、unsigned()　利用不可）
```

### テーブルの更新

テーブルを更新するには、まず対象となるテーブルを**Schema::getTable**で取得し、**Schema::alterTable**を実行します。

```php
// test テーブルを取得
$table = Schema::getTable('test');
// 変更内容を記述
$table->addColumn('post_id','int');        
$table->changeColumn('status')->default(0);
$table->addForeign('post_id')->references('posts','id')->onDelete('cascade')->name('FK1');
$table->dropForeign('fk_test2_user_id_users_id');
$table->dropIndexByColumns('content','status');
$table->addIndex('status')->name('INDEX1');        
$table->dropColumn('content');
// 最後に変更クエリを実行
Schema::alterTable($table);
```

## テーブルの削除

テーブルを削除するには、**Schema::dropTableIfExists**を実行します。また、取得したテーブルを削除することもできます。

```php
// 取得したテーブルを削除
$table = Schema::getTable('test');
Schema::dropTable($table);

// test テーブルを削除
Schema::dropTableIfExists('test');
```



## ライセンス (License)

**DB Model**は[MIT license](https://opensource.org/licenses/MIT)のもとで公開されています。

**DB Model** is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).