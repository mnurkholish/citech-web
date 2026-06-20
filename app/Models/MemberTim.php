<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTim extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'member_tim';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_tim',
        'nama_peserta',
        'nim_peserta',
        'jurusan',
        'role',
    ];

    /**
     * Get the team that the member belongs to.
     */
    public function tim(): BelongsTo
    {
        return $this->belongsTo(Tim::class, 'id_tim', 'id_tim');
    }
}
