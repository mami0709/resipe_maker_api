<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use InvalidArgumentException;

class Ticket extends Model
{
    use HasFactory;

    // モデルの基本設定
    protected $table = 'ticket';
    protected $guarded = ['id'];
    protected $fillable = ['user_id', 'title', 'content', 'status_no', 'category_id', 'is_recruitment'];
    protected $with = ['tags'];

    protected $casts = [
        'is_recruitment' => 'boolean',
    ];

    /**
     * TicketモデルとUserモデルのリレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * TicketモデルとTicketCategoryモデル間のリレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    /**
     * TicketモデルとTicketAnswerモデル間のリレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(TicketAnswer::class);
    }

    /**
     * TicketモデルとMTicketTagモデル間の多対多リレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(MTicketTag::class, 'ticket_tag', 'ticket_id', 'tag_id');
    }

    /**
     * カテゴリのテキストを取得するアクセサ。
     *
     * @return string|null カテゴリのテキスト、または不明な場合は '不明'
     */
    public function getCategoryTextAttribute(): ?string
    {
        return $this->category->text ?? '不明';
    }

    /**
     * 関連するタグのリストを取得するメソッド。
     *
     * @return array タグのリスト
     */
    public function getTagsListAttribute()
    {
        return $this->tags()->pluck('label')->toArray();
    }
}
