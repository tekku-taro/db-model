<?php
namespace Taro\DBModel\Models;

class Post extends Model
{
    protected $id;
    
    protected $title;

    protected $body;
    
    protected $user_id;
    
    protected $date;
    
    protected $views;
    
    protected $finished;
    
    protected $hidden;
    
    protected $category;
    
    protected $tags;
    
    protected $create_date;

    public function relatedComments()
    {
        return $this->hasMany(Comment::class , 'post_id');
    }    
}