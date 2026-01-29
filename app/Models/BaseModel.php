<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\BaseModel
 *
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends ApiModel
{
    use LogsActivity;

    // It will be override
    protected $dates = [];
    protected $ignoreLogAttributes = [];

    public static function options($items, $group = null, $columnName = null): string
    {
        $options = '<option value="">--</option>';

        foreach ($items as $item) {

            $name = is_null($columnName) ? $item->name : $item->{$columnName};

            $selected = (!is_null($group) && ($item->id == $group->id)) ? 'selected' : '';

            $options .= '<option ' . $selected . ' value="' . $item->id . '"> ' . ($name) . ' </option>';
        }

        return $options;
    }

    public static function clickAbleLink($route, $title, $other = null)
    {
        return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . $route . '" class="openRightModal">' . $title . '</a></h5>
                    <p class="mb-0">' . $other . '</p>
                    </div>
                  </div>';
    }

    // Added this for $dates
    public function getDates()
    {
        if (!$this->usesTimestamps()) {
            return $this->dates;
        }

        $defaults = [
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        return array_unique(array_merge($this->dates, $defaults));
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $company = company();

        $activity->company_id = $company ? $company->id : null;
        $activity->ip_address = request()->getClientIp() ?? null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly($this->ignoreLogAttributes);
    }
}
