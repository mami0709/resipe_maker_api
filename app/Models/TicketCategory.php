<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TicketCategory モデルクラス
 *
 * このモデルは 'm_ticket_category' テーブルに対応し、チケットのカテゴリ情報を表します。
 * カテゴリの名称 ('text')、並び順 ('sort_no')、およびアクティブ状態 ('is_active') を保持します。
 *
 * @property string $text カテゴリの名称
 * @property int $sort_no カテゴリの表示順序
 * @property bool $is_active カテゴリがアクティブかどうかを示すフラグ
 */
class TicketCategory extends Model
{
    use HasFactory;

    protected $table = 'm_ticket_category';

    protected $fillable = ['text', 'sort_no', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
