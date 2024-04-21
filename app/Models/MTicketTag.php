<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * m_ticket_tag テーブルに対応するモデル。
 *
 * タグの名前 ('label') と関連するカテゴリID ('category_id') を保持する。
 *
 * @property string $label タグの名前。
 * @property int $category_id タグが関連するカテゴリのID。
 *
 * @property \Illuminate\Database\Eloquent\Collection<int, Ticket> $tickets チケットコレクションを取得する
 */
class MTicketTag extends Model
{
    protected $table = 'm_ticket_tag';

    protected $fillable = ['label', 'category_id'];

    /**
     * このタグに関連するチケットを取得するための多対多リレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     *         このタグに関連するチケットを返す。
     */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_tag', 'tag_id', 'ticket_id');
    }
}
