<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TicketAnswer モデルクラス
 *
 * このモデルは 'ticket_answer' テーブルに対応し、チケットへの回答情報を表します。
 * 各回答は特定のチケット ('ticket_id') とユーザー ('user_id') に関連付けられており、
 * 回答の内容 ('content') を保持します。
 *
 * @property int $ticket_id この回答が関連付けられているチケットのID
 * @property int $user_id この回答を投稿したユーザーのID
 * @property string $content 回答の内容
 */
class TicketAnswer extends Model
{
    use HasFactory;

    protected $table = 'ticket_answer';

    protected $fillable = ['ticket_id', 'user_id', 'content'];

    /**
     * 回答に関連するチケットを取得するリレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * 回答を投稿したユーザーを取得するリレーション。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
