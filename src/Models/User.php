<?php
namespace Taro\DBModel\Models;

class User extends Model
{
    protected $id;
    
    protected $name;

    protected $email;
    
    protected $password;

    public function relatedPosts()
    {
        return $this->hasMany(Post::class , 'user_id');
    }

    public function favoritePosts()
    {
        return $this->manyToMany(Post::class , 'favorites');
    }

}