<?php

namespace App\Models;

use App\Models\Scopes\ExpenseScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'despesas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'descricao',
        'data',
        'id_usuario',
        'valor'
    ];

    /**
     * User relashionship.
     *
     * @return object
     */
    public function descriptions()
    {
        return $this->hasOne(User::class, 'id_usuario', 'id');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ExpenseScope);
    }
}
