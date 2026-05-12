<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Category extends Model implements AuditableContract
{
    use HasFactory, Auditable;
    protected $fillable = ['name', 'description'];
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
