<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CategoryEnum;
use App\Enums\TicketStatusEnum;
use Illuminate\Validation\Rule;

class TicketEditRequest extends FormRequest
{
    /**
     * リクエストに適用されるバリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => [
                'required',
                'integer',
                Rule::in(array_map(fn ($c) => $c->value, CategoryEnum::cases()))
            ],
            'content' => 'required|string|max:16383',
            'status_no' => [
                'required',
                'integer',
                Rule::in(array_map(fn ($s) => $s->value, TicketStatusEnum::cases()))
            ],
            'is_recruitment' => 'boolean',
            'tags' => 'array',
        ];
    }

    /**
     * バリデーションされたデータを使用して Ticket インスタンスを作成
     *
     * @return Ticket
     */
    public function makeTicket(): Ticket
    {
        $data = $this->validated();
        $ticket = new Ticket();
        $ticket->fill($data);

        // バリデーションされたデータにtagsが含まれている場合は、それも設定
        if (isset($data['tags'])) {
            $ticket->tags = $data['tags'];
        }

        return $ticket;
    }
}
