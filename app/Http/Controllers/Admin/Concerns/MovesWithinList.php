<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Swap an item with its neighbour in an ordered list and renumber the whole list,
 * so gaps or equal sort orders never cause surprises.
 */
trait MovesWithinList
{
    /**
     * @param  Builder<Model>  $list  The list the item belongs to, already in display order.
     */
    protected function moveWithinList(Builder $list, Model $item, bool $earlier): void
    {
        DB::transaction(function () use ($list, $item, $earlier) {
            $items = $list->lockForUpdate()->get()->values();
            $index = $items->search(fn (Model $candidate) => $candidate->is($item));

            if ($index === false) {
                return;
            }

            $target = $earlier ? $index - 1 : $index + 1;

            if (! $items->has($target)) {
                return;
            }

            $order = $items->all();
            [$order[$index], $order[$target]] = [$order[$target], $order[$index]];

            foreach (array_values($order) as $position => $model) {
                $model->sort_order = $position + 1;
                $model->save();
            }
        });
    }
}
