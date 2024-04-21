<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * TicketTag ピボットモデルクラス
 *
 * このモデルは 'ticket_tag' 中間テーブルに対応し、Ticket と MTicketTag モデル間の多対多関連を管理します。
 * Ticket モデルと MTicketTag モデルのリレーションで使用される。
 *
 * @property int $ticket_id チケットのID
 * @property int $tag_id タグのID
 */
class TicketTag extends Pivot
{
    protected $table = 'ticket_tag';

    public $timestamps = false;
}
