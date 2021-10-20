<?php
namespace Taro\DBModel\Models;

class Comment extends Model
{
    protected $title;
    
    protected $body;

    protected $post_id;

    public function users()
    {
        return $this->belongsToThrough(User::class, Post::class);
    }    
}